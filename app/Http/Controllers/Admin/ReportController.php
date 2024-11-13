<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clas;
use App\Models\Department;
use App\Models\Contractor;
use App\Models\Designation;
use App\Models\DeviceLogsProcessed;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Punch;
use App\Models\Setting;
use App\Models\User;
use App\Models\Ward;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Elibyy\TCPDF\Facades\TCPDF;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Carbon\CarbonPeriod;
use Crypt;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $authUser = Auth::user();
        $departments = Department::whereDepartmentId(null)
                        ->where('tenant_id', auth()->user()->tenant_id)
                        ->when( !$authUser->hasRole(['Admin', 'Super Admin']), fn($qr)=> $qr->where('id', $authUser->department_id) )
                        ->orderBy('name')->get();
        $contractors = Contractor::latest()->get();

        $wards = Ward::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get();
        $class = Clas::orderBy('name')->get();
        $designations = Designation::orderBy('name')->get();
        $empList = [];
        $leaveTypes = LeaveType::get();
        $totalDays = '';
        $holidays = 0;
        $dateRanges = [];
        $otherLeavesArray = ['no'=> 'NIGHTOFF', 'co'=> 'COMPENS', 'ph'=> 'PUBLIC', 'so'=> 'SATOFF', 'do'=> 'DAYOFF'];

        $settings = Setting::getValues( $authUser->tenant_id )->pluck('value', 'key');
        $fromDate = Carbon::parse($request->from_date)->toDateString();
        $toDate = Carbon::parse($request->to_date)->toDateString();

        if( $request->month )
        {
            $departmentId = $authUser->hasRole(['Admin', 'Super Admin']) ? $request->department : $authUser->department_id;
            $empList = User::whereNot('id', $authUser->id)
                        ->with(['department', 'shift', 'empShifts'=> fn($q)=> $q->whereBetween('from_date', [$fromDate, $toDate])])
                        ->where('department_id', $departmentId)
                        ->where('is_employee', 1)
                        ->where('tenant_id', $authUser->tenant_id)
                        ->orderBy('emp_code')
                        ->with('punches', fn($q)=> $q->whereBetween('punch_date', [$fromDate, $toDate] ) )
                        ->when($request->ward, fn($qr)=> $qr->where('ward_id', $request->ward))
                        ->when($request->class, fn($qr)=> $qr->where('clas_id', $request->class))
                        ->when($request->employee_type, fn($qr)=> $qr->where('employee_type', $request->employee_type))
                        ->when($request->contractor, fn($qr)=> $qr->where('contractor', $request->contractor))
                        ->when($request->designation, fn($qr)=> $qr->where('designation_id', $request->designation))
                        ->get();



            $holidays = Holiday::whereBetween('date', [$fromDate, $toDate])->where('tenant_id', $authUser->tenant_id)->get();
            $totalDays = Carbon::parse($fromDate)->diffInDays($toDate)+1;
            $dateRanges = CarbonPeriod::create( Carbon::parse($fromDate), Carbon::parse($toDate) )->toArray();
        }
        return view('admin.reports.month-wise-report')->with(['dateRanges'=> $dateRanges, 'otherLeavesArray'=> $otherLeavesArray, 'empList'=> $empList, 'departments'=> $departments, 'holidays'=> $holidays, 'settings'=> $settings, 'leaveTypes'=> $leaveTypes, 'wards'=> $wards, 'class'=> $class, 'fromDate'=> $fromDate, 'toDate'=> $toDate, 'totalDays'=> $totalDays, 'designations'=> $designations, 'contractors' => $contractors]);
    }


    public function musterReport(Request $request)
    {
        $authUser = Auth::user();
        $departments = Department::whereDepartmentId(null)
                            ->where('tenant_id', auth()->user()->tenant_id)
                            ->when( !$authUser->hasRole(['Admin', 'Super Admin']), fn($qr)=> $qr->where('id', $authUser->department_id) )
                            ->orderBy('name')->get();
        $contractors = Contractor::latest()->get();

        $wards = Ward::where('tenant_id', auth()->user()->tenant_id)->latest()->get();
        $class = Clas::latest()->get();
        $designations = Designation::orderBy('name')->get();
        $empList = [];
        $leaveTypes = LeaveType::get();
        $totalDays = '';
        $holidays = 0;
        $errorMessage = '';

        $settings = Setting::getValues( $authUser->tenant_id )->pluck('value', 'key');
        $defaultShift = collect( config('default_data.shift_time') );
        $fromDate = Carbon::parse($request->from_date)->toDateString();
        $toDate = Carbon::parse($request->to_date)->toDateString();

        if( $request->month )
        {
            $empList = User::whereNot('id', $authUser->id)
                    ->select('id', 'ward_id', 'department_id', 'emp_code', 'in_time', 'employee_type', 'name', 'contractor', 'shift_id', 'clas_id', 'designation_id', 'is_rotational', 'is_ot', 'is_divyang','is_half_day_on_saturday')
                    ->with('punches', fn($q) => $q
                        ->whereBetween('punch_date', [$fromDate, $toDate] )
                        ->select('id', 'emp_code', 'check_in', 'check_out', 'punch_date', 'is_latemark', 'type', 'leave_type_id', 'is_paid', 'duration', 'punch_by')
                    )
                    ->with([ 'ward', 'department', 'designation', 'clas', 'shift',
                        'empShifts'=> fn($q)=> $q->whereBetween('from_date', [$fromDate, $toDate])
                    ])
                    ->where('ward_id', $request->ward)
                    ->where('is_employee', 1)
                    ->where('tenant_id', $authUser->tenant_id)
                    ->orderBy('emp_code')
                    ->when(!$request->emp_code && $request->department, fn($qr)=> $qr->where('department_id', $request->department))
                    ->when(!$request->emp_code && $request->class, fn($qr)=> $qr->where('clas_id', $request->class ))
                    ->when(!$request->emp_code && $request->designation, fn($qr)=> $qr->where('designation_id', $request->designation))
                    ->when($request->employee_type, fn($qr)=> $qr->where('employee_type', $request->employee_type))
                    ->when($request->contractor, fn($qr)=> $qr->where('contractor', $request->contractor))
                    ->when($request->emp_code, fn($qr)=> $qr->where('emp_code', $request->emp_code))
                    ->get();

            $holidays = Holiday::whereBetween('date', [$fromDate, $toDate])->where('tenant_id', $authUser->tenant_id)->get();
            $totalDays = Carbon::parse($fromDate)->diffInDays($toDate)+1;
            $dateRanges = CarbonPeriod::create( Carbon::parse($fromDate), Carbon::parse($toDate) )->toArray();

            try
            {
                $data['empList'] = $empList;
                $data['holidays'] = $holidays;
                $data['settings'] = $settings;
                $data['leaveTypes'] = $leaveTypes;
                $data['fromDate'] = $fromDate;
                $data['toDate'] = $toDate;
                $data['totalDays'] = $totalDays;
                $data['dateRanges'] = $dateRanges;
                $data['defaultShift'] = $defaultShift;
                $data['leavesArray'] = ['0'=> 'HALFDAY', '1'=> 'TECH', '2'=> 'OUT', '3'=> 'COMP', '4'=> 'OL', '5'=> 'EL', '6'=> 'CL', '7'=> 'MEDI'];
                $data['otherLeavesArray'] = ['no'=> 'NIGHTOFF', 'co'=> 'COMPENS', 'ph'=> 'PUBLIC', 'so'=> 'SATOFF', 'do'=> 'DAYOFF', 'wo'=> 'WEEKOFF', 'tr'=> 'TECHBREK'];

                $filename = str_replace('/', '_', 'MUSTER_'.$fromDate.'_'.$toDate);
                $filename = str_replace('-', '_', $filename.'.pdf');

                $pdf = SnappyPdf::loadView('admin.pdf.muster', $data)
                                ->setPaper('a4')
                                ->setOrientation('landscape')
                                ->setOption('margin-bottom', 0)
                                ->setOption('margin-top', 3)
                                ->setOption('margin-left', 0)
                                ->setOption('margin-right', 0);

                return $pdf->inline($filename);
            }
            catch(\Exception $e)
            {
                $errorMessage = $e->getMessage();
                Log::info("error: ".$e);
            }
        }

        return view('admin.reports.muster-report')->with([ 'errorMessage'=> $errorMessage, 'empList'=> $empList, 'holidays'=> $holidays, 'settings'=> $settings, 'leaveTypes'=> $leaveTypes, 'departments'=> $departments, 'designations'=> $designations, 'wards'=> $wards, 'class'=> $class, 'fromDate'=> $fromDate, 'toDate'=> $toDate, 'totalDays'=> $totalDays, 'contractors' => $contractors]);
    }








    public function todaysInTime(Request $request)
    {
        // do something about it
    }

    public function deviceLogReport(Request $request)
    {
        $authUser = Auth::user();
        $isAdmin = $authUser->hasRole(['Admin', 'Super Admin']);
        $departments = Department::whereDepartmentId(null)
                                ->where('tenant_id', auth()->user()->tenant_id)
                                ->when( !$authUser->hasRole(['Admin', 'Super Admin']), fn($qr)=> $qr->where('id', $authUser->department_id) )
                                ->orderBy('name')->get();

        $wards = Ward::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get();
        $selectedDepartmentId = $isAdmin ? $request->department : $authUser->department_id;
        $selectedDate = $request->date ? Carbon::parse($request->date)->toDateString() : Carbon::today()->subDays(2)->format('Y-m-d');


        $datas = DeviceLogsProcessed::withWhereHas(
                        'user', fn($qr)=> $qr->select('id', 'device_id', 'ward_id', 'clas_id', 'department_id', 'emp_code', 'name' )
                                    ->with('device:DeviceId,DeviceLocation', 'ward', 'clas', 'department')
                                    ->when( $selectedDepartmentId, fn($q)=> $q->where('department_id', $selectedDepartmentId) )
                                    ->when( $request->ward, fn($q)=> $q->where('ward_id', $request->ward) )
                        )
                        ->whereDate('LogDate', $selectedDate )
                        ->orderByDesc('DeviceLogId')
                        // ->take(600)
                        ->get();

        return view('admin.dashboard.device-log-report')->with(['datas'=> $datas, 'isAdmin'=> $isAdmin, 'departments'=> $departments, 'wards'=> $wards]);
    }


    public function dailyAttendanceReport(Request $request)
    {
        $authUser = Auth::user();
        $isAdmin = $authUser->hasRole(['Admin', 'Super Admin']);
        $departments = Department::whereDepartmentId(null)
                                ->where('tenant_id', auth()->user()->tenant_id)
                                ->when( !$authUser->hasRole(['Admin', 'Super Admin']), fn($qr)=> $qr->where('id', $authUser->department_id) )
                                ->orderBy('name')->get();

        $wards = Ward::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get();
        $selectedDepartmentId = $isAdmin ? $request->department : $authUser->department_id;

        $data = Punch::withWhereHas(
                        'user', fn($qr)=> $qr->select('id', 'device_id', 'ward_id', 'department_id', 'emp_code', 'name' )
                                    ->with('device:DeviceId,DeviceLocation', 'ward', 'clas', 'department')
                                    ->when( $selectedDepartmentId, fn($q)=> $q->where('department_id', $selectedDepartmentId) )
                                    ->when( $request->ward, fn($q)=> $q->where('ward_id', $request->ward) )
                        )
                        ->whereDate('punch_date', Carbon::parse($request->date)->toDateString() ?? Carbon::today()->toDateString() )
                        ->orderByDesc('id')->get();

        return view('admin.dashboard.daily-attendance-report')->with(['data'=> $data, 'isAdmin'=> $isAdmin, 'departments'=> $departments, 'wards'=> $wards]);
    }


    public function departmentWiseReport(Request $request)
    {
        $data = Department::withCount('users')
                ->where('tenant_id', auth()->user()->tenant_id)
                ->withCount(['users as present_count'=> fn($q)=> $q->withWhereHas('punches', fn($qr)=> $qr->where('punch_date', Carbon::today()->toDateString()) ) ])->get();

        return view('admin.dashboard.department-wise-attendance-report')->with(['data'=> $data]);
    }

    // Same as device log report
    public function todaysPresentReport(Request $request)
    {
        $authUser = Auth::user();
        $isAdmin = $authUser->hasRole(['Admin', 'Super Admin']);
        $departments = Department::whereDepartmentId(null)
                                ->where('tenant_id', auth()->user()->tenant_id)
                                ->when( !$authUser->hasRole(['Admin', 'Super Admin']), fn($qr)=> $qr->where('id', $authUser->department_id) )
                                ->orderBy('name')->get();
        $contractors = Contractor::latest()->get();
        $wards = Ward::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get();
        $selectedDepartmentId = $isAdmin ? $request->department : $authUser->department_id;

        $data = Punch::withWhereHas(
                        'user', fn($qr)=> $qr->select('id', 'device_id', 'ward_id', 'department_id', 'emp_code', 'name', 'employee_type', 'contractor' )
                                ->with('device:DeviceId,DeviceLocation', 'ward', 'clas')
                                ->when( $selectedDepartmentId, fn($q)=> $q->where('department_id', $selectedDepartmentId) )
                                ->when( $request->ward, fn($q)=> $q->where('ward_id', $request->ward) )
                                ->when( $request->employee_type, fn($q)=> $q->where('employee_type', $request->employee_type) )
                                ->when($request->contractor, fn($qr)=> $qr->where('contractor', $request->contractor))
                        )
                        ->when($request->before, fn($q)=> $q->whereTime('check_in', '<=', $request->before)->whereTime('check_in', '>=', '01:00:00'))
                        ->when($request->after, fn($q)=> $q->whereTime('check_in', '>=', $request->after)->whereTime('check_in', '<=', '24:00:00'))
                        ->whereDate('punch_date', Carbon::parse($request->date)->toDateString() ?? Carbon::today()->toDateString() )
                        ->orderByDesc('id')->get();

        return view('admin.dashboard.todays-present-report')->with(['data'=> $data, 'wards'=> $wards, 'isAdmin'=> $isAdmin, 'departments'=> $departments, 'contractors' => $contractors]);
    }

    public function todaysAbsentReport(Request $request)
    {
        $authUser = Auth::user();
        $isAdmin = $authUser->hasRole(['Admin', 'Super Admin']);
        $departments = Department::whereDepartmentId(null)
                                ->where('tenant_id', auth()->user()->tenant_id)
                                ->when( !$authUser->hasRole(['Admin', 'Super Admin']), fn($qr)=> $qr->where('id', $authUser->department_id) )
                                ->orderBy('name')->get();

        $selectedDepartmentId = $isAdmin ? $request->department : $authUser->department_id;
        $wards = Ward::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get();
        $contractors = Contractor::latest()->get();

        $date = $request->date ? Carbon::parse($request->date)->toDateString() : Carbon::today()->toDateString();

        $data = User::whereDoesntHave( 'punches', fn($q)=> $q->whereDate('punch_date', $date ) )
                        ->select('id', 'device_id', 'ward_id', 'department_id', 'emp_code', 'name', 'employee_type', 'contractor' )
                        ->whereIsEmployee('1')
                        ->where('tenant_id', $authUser->tenant_id)
                        ->with('ward', 'shift', 'clas')
                        ->when( $selectedDepartmentId, fn($q)=> $q->where('department_id', $selectedDepartmentId) )
                        ->when( $request->ward, fn($q)=> $q->where('ward_id', $request->ward) )
                        ->when( $request->employee_type, fn($q)=> $q->where('employee_type', $request->employee_type) )
                        ->when($request->contractor, fn($qr)=> $qr->where('contractor', $request->contractor))
                        ->get();

        return view('admin.dashboard.todays-absent-report')->with(['data'=> $data, 'wards'=> $wards, 'isAdmin'=> $isAdmin, 'departments'=> $departments, 'contractors' => $contractors]);
    }

    public function shiftWiseEmployees(Request $request, $shiftId)
    {
        $shiftId = Crypt::decrypt($shiftId) ?? '1';
        $authUser = Auth::user();
        $isAdmin = $authUser->hasRole(['Admin', 'Super Admin']);
        $departments = $isAdmin ? Department::whereDepartmentId(null)->where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get() : [] ;
        $wards = Ward::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get();
        $selectedDepartmentId = $isAdmin ? $request->department : $authUser->department_id;

        $data = User::select('id', 'device_id', 'ward_id', 'department_id', 'emp_code', 'name' )
                        ->whereIsEmployee('1')
                        ->with('shift', 'clas')
                        ->where('shift_id', $shiftId)
                        ->where('tenant_id', $authUser->tenant_id)
                        ->with('device:DeviceId,DeviceLocation', 'ward', 'department')
                        ->when( $selectedDepartmentId, fn($q)=> $q->where('department_id', $selectedDepartmentId) )
                        ->when( $request->ward, fn($q)=> $q->where('ward_id', $request->ward) )
                        ->get();

        return view('admin.dashboard.shift-wise-employee')->with(['data'=> $data, 'isAdmin'=> $isAdmin, 'departments'=> $departments, 'wards'=> $wards]);
    }


    public function todaysLeaveBifurcation(Request $request)
    {
        $leaveTypes = config('default_data.leave_types');
        $leave_type_id = (int) $request->leave_type_id;
        $authUser = Auth::user();
        $isAdmin = $authUser->hasRole(['Admin', 'Super Admin']);
        $departments = $isAdmin ? Department::whereDepartmentId(null)->where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get() : [] ;
        $wards = Ward::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get();
        $selectedDepartmentId = $isAdmin ? $request->department : $authUser->department_id;
        $date = $request->date ?? Carbon::today()->toDateString();

        $data = Punch::whereDate('punch_date', $date)
                        ->when($request->has('leave_type_id'), fn($qr)=>$qr->where('leave_type_id', $leave_type_id) )
                        ->when(!$request->has('leave_type_id'), fn($qr)=>$qr->whereIn('leave_type_id', array_keys($leaveTypes)) )
                        ->withWhereHas('user', fn ($q) =>
                                $q->with('department', 'ward')
                                ->when($request->ward, fn($qr)=> $qr->where('ward_id', $request->ward))
                                ->when($selectedDepartmentId, fn($qr)=> $qr->where('department_id', $selectedDepartmentId))
                        )->get();

        return view('admin.dashboard.leave-bifurcation-report')->with(['data'=> $data, 'isAdmin'=> $isAdmin, 'leaveTypes'=> $leaveTypes, 'departments'=> $departments, 'wards'=> $wards]);
    }


    public function monthWiseLatemark(Request $request)
    {
        $authUser = Auth::user();
        $departments = Department::whereDepartmentId(null)->where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get();
        $wards = Ward::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get();

        $departmentId = $authUser->hasRole(['Admin', 'Super Admin']) ? $request->department : $authUser->department_id;
        $ward = $authUser->hasRole(['Admin', 'Super Admin']) ? $request->department : $authUser->ward_id;

        $fromDate = $request->from_date ? Carbon::parse($request->from_date)->toDateString() : Carbon::today()->startOfMonth()->toDateString();
        $toDate = $request->to_date ? Carbon::parse($request->to_date)->toDateString() : Carbon::today()->endOfMonth()->toDateString();

        $dateRanges = CarbonPeriod::create( Carbon::parse($fromDate), Carbon::parse($toDate) )->toArray();

        $data = User::withWhereHas('punches', fn($q)=> $q
                                    ->where('is_latemark', '1')
                                    ->whereDate('punch_date', '>=', $fromDate)
                                    ->whereDate('punch_date', '<=', $toDate)
                        )
                        ->with('ward', 'department')
                        ->where('tenant_id', $authUser->tenant_id)
                        ->when($departmentId, fn($q)=> $q->where('department_id', $departmentId) )
                        ->when($ward, fn($q)=> $q->where('ward_id', $ward))
                        ->get();

        return view('admin.dashboard.month-wise-latemark')->with(['data'=> $data, 'departments'=> $departments, 'wards'=> $wards, 'dateRanges'=> $dateRanges]);
    }


    public function monthWiseAbsent(Request $request)
    {
        $authUser = Auth::user();
        $departments = Department::whereDepartmentId(null)->where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get();
        $wards = Ward::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get();

        $departmentId = $authUser->hasRole(['Admin', 'Super Admin']) ? $request->department : $authUser->department_id;
        $ward = $authUser->hasRole(['Admin', 'Super Admin']) ? $request->department : $authUser->ward_id;

        $fromDate = $request->from_date ? Carbon::parse($request->from_date)->toDateString() : Carbon::today()->startOfMonth()->toDateString();
        $toDate = $request->to_date ? Carbon::parse($request->to_date)->toDateString() : Carbon::today()->endOfMonth()->toDateString();

        $dateRanges = CarbonPeriod::create( Carbon::parse($fromDate), Carbon::parse($toDate) )->toArray();
        $weekDays = Carbon::parse($fromDate)->diffInDaysFiltered( fn (Carbon $date)=> $date->isWeekend(), $toDate);

        $data = User::withCount(['punches'=> fn($q)=> $q
                            ->whereDate('punch_date', '>=', $fromDate)
                            ->whereDate('punch_date', '<=', $toDate)
                        ])
                        ->with('department')
                        ->where('is_employee', 1)
                        ->where('tenant_id', $authUser->tenant_id)
                        ->whereNot('id', $authUser->id)
                        ->when($departmentId, fn($q)=> $q->where('department_id', $departmentId) )
                        ->when($ward, fn($q)=> $q->where('ward_id', $ward))
                        ->get();
        // dd($data->take(1));
        // $data = $data->filter
        return view('admin.dashboard.month-wise-latemark')->with(['data'=> $data, 'departments'=> $departments, 'wards'=> $wards, 'dateRanges'=> $dateRanges]);
    }


    public function employeeWiseReport(Request $request)
    {
        // $authUser = Auth::user();
        $fromDate = $request->from_date ??  Carbon::today()->endOfMonth()->toDateString();
        $toDate = $request->to_date ?? Carbon::today()->startOfMOnth()->toDateString();

        $data = [];
        if($request->emp_code)
        {
            $data = Punch::with('user', 'device')
                        ->when($request->from_date, fn($qr)=> $qr->whereDate('punch_date', '>=', $fromDate))
                        ->when($request->to_date, fn($qr)=> $qr->whereDate('punch_date', '<=', $toDate))
                        ->where('emp_code', $request->emp_code)
                        ->get();
        }
        // dd($data);
        return view('admin.dashboard.employee-wise-report')->with(['data'=> $data]);
    }








    public function monthWiseDate(Request $request)
    {
        $year = $request->year ?? date('Y');
        $month = $request->month ?? 1;

        $settings = Setting::getValues( auth()->user()->tenant_id )->pluck('value', 'key');
        $fromDate = Carbon::parse($year.'-'.($month).'-'.$settings['PAYROLL_DATE']);
        $toDate = clone($fromDate);
        $fromDate = (string) $fromDate->subMonth()->toDateString();
        $toDate = (string) $toDate->subDay()->toDateString();

        return response()->json([
            'success'=> true,
            'fromDate'=> $fromDate,
            'toDate'=> $toDate,
        ]);
    }
    
    
    public function deviceWiseReport(Request $request)
    {
        set_time_limit(0);
        $authUser = Auth::user();
        $isAdmin = $authUser->hasRole(['Admin', 'Super Admin']);


        $fromDate = (isset($request->from_date) && $request->from_date != "") ? date('Y-m-d', strtotime($request->from_date)) : date('Y-m-d');
        $toDate = (isset($request->to_date) && $request->to_date != "") ? date('Y-m-d', strtotime($request->to_date)) : date('Y-m-d');

        $data = [];

        if (isset($request->from_date)) {
            $data = DeviceLogsProcessed::with(['device', 'user.designation', 'user.department'])
                ->when(isset($request->emp_code) && $request->emp_code != "", function ($q) use ($request) {
                    $q->where('UserId', $request->emp_code);
                })->when(isset($request->device) && $request->device != "", function ($q) use ($request) {
                    $q->where('DeviceId', $request->device);
                })->whereDate('LogDate', '>=', $fromDate)
                ->whereDate('LogDate', '<=', $toDate)
                ->get();
        }


        $devices = DB::table('Devices')->select('DeviceId', 'DeviceLocation', 'SerialNumber')->get();


        return view('admin.reports.device-wise-report')->with(['data' => $data, 'isAdmin' => $isAdmin, 'devices' => $devices]);
    }
}
