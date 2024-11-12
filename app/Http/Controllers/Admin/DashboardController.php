<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Holiday;
use App\Models\Punch;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Ward;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $authUser = auth()->user();
        $is_admin = $authUser->hasRole(['Admin', 'Super Admin']) ? true : false;
        $ward = $request->ward;
        $department = $request->ward;

        $totalEmployees = User::when(!$is_admin, fn($qr) => $qr->where('department_id', $authUser->department_id))
            ->where('tenant_id', $authUser->tenant_id)
            ->when($ward, fn($qr) => $qr->where('ward_id', $ward))
            ->whereIsEmployee('1')
            ->count();

        if ($is_admin)
            $totalDepartments = Department::whereDepartmentId(null)->where('tenant_id', $authUser->tenant_id)->when($ward, fn($qr) => $qr->where('ward_id', $ward))->count();
        else
            $totalDepartments = 1;

        $totalHolidays = Holiday::where('year', date('Y'))->where('tenant_id', $authUser->tenant_id)->count();
        $totalWards = Ward::withCount('users')->where('tenant_id', auth()->user()->tenant_id)->get();

        $todaysDate = Carbon::today()->toDateString();
        $backDate = Carbon::today()->subDay()->toDateString();

        $punchData = Punch::whereIn('punch_date', [$todaysDate, $backDate])
            ->select('id', 'emp_code', 'check_in', 'check_out', 'duration', 'punch_date', 'is_latemark', 'is_latemark_updated', 'punch_by', 'type', 'leave_type_id')
            ->withWhereHas(
                'user',
                fn($q) =>
                $q->when(!$is_admin, fn($qr) => $qr->where('department_id', $authUser->department_id))
                    ->when($is_admin && $ward, fn($qr) => $qr->where('ward_id', $ward))
                    ->where('tenant_id', $authUser->tenant_id)
            )
            ->latest()->get();

        $todayPunchData = $punchData->where('punch_date', '>=', Carbon::parse($todaysDate)->toDateString());

        $departments = '';
        if ($is_admin) {
            $departments = Department::query()
                ->withCount([
                    'users' => fn($q) => $q
                        ->when($department, fn($qr) => $qr->where('department_id', $department))
                ])
                ->when(Auth::user()->hasRole(['HOD/checker']), function ($q) {
                    $q->where('id', Auth::user()->department_id);
                })
                ->whereDepartmentId(null)
                ->get();

            $totalDepartments = $departments->count();
        } else {
            $totalDepartments = 1;
        }

        return view('admin.dashboard.index')->with([
            'is_admin' => $is_admin,
            'totalEmployees' => $totalEmployees,
            'totalDepartments' => $totalDepartments,
            'totalHolidays' => $totalHolidays,
            'totalWards' => $totalWards,
            'todaysDate' => $todaysDate,
            'backDate' => $backDate,
            'punchData' => $punchData,
            'todayPunchData' => $todayPunchData,
            'departments' => $departments
            // 'shiftWiseData'=> $shiftWiseData,
        ]);
    }
}
