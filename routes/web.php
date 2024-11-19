<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {

    return redirect()->route('login');
});


// Guest Users
Route::middleware(['guest','PreventBackHistory'])->group(function()
{
    Route::get('/', [App\Http\Controllers\Admin\AuthController::class, 'showLogin'])->name('/');
    Route::get('login', [App\Http\Controllers\Admin\AuthController::class, 'showLogin'] )->name('login');
    Route::post('login', [App\Http\Controllers\Admin\AuthController::class, 'login'])->name('signin');

});



// Authenticated users
Route::middleware(['auth','PreventBackHistory'])->group(function()
{

    // Auth Routes
    Route::get('edit-profile', [App\Http\Controllers\Admin\DashboardController::class, 'editProfile'] )->name('edit-profile');
    Route::get('home', fn () => redirect()->route('dashboard'))->name('home');
    Route::post('logout', [App\Http\Controllers\Admin\AuthController::class, 'Logout'])->name('logout');
    Route::get('show-change-password', [App\Http\Controllers\Admin\AuthController::class, 'showChangePassword'] )->name('show-change-password');
    Route::post('change-password', [App\Http\Controllers\Admin\AuthController::class, 'changePassword'] )->name('change-password');



    // Dashboard routes
    Route::get('dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');



    // Manual Sync Attendance
    Route::get('manual-sync', [App\Http\Controllers\Admin\MaualSyncController ::class, 'index'] )->name('manual-sync.index');
    Route::post('manual-sync', [App\Http\Controllers\Admin\MaualSyncController::class, 'addManualSync'] )->name('manual-sync.store');
    Route::post('check-sync-status', [App\Http\Controllers\Admin\MaualSyncController::class, 'checkSyncStatus'] )->name('check-sync-status');



    // Masters routes
    Route::resource('departments', App\Http\Controllers\Admin\Masters\DepartmentController::class );
    Route::get('departments/{department}/sub_departments', [App\Http\Controllers\Admin\Masters\DepartmentController::class, 'getSubDepartments'] )->name('departments.sub_departments');
    Route::resource('sub-departments', App\Http\Controllers\Admin\Masters\SubDepartmentController::class );
    Route::resource('wards', App\Http\Controllers\Admin\Masters\WardController::class );
    Route::get('wards/{ward}/departments', [App\Http\Controllers\Admin\Masters\DepartmentController::class, 'getWardDepartments'] )->name('wards.departments');
    Route::resource('clas', App\Http\Controllers\Admin\Masters\ClasController::class );
    Route::resource('designations', App\Http\Controllers\Admin\Masters\DesignationController::class );
    Route::resource('holidays', App\Http\Controllers\Admin\Masters\HolidayController::class );
    Route::resource('leave_types', App\Http\Controllers\Admin\Masters\LeaveTypeController::class );
    Route::resource('leaves', App\Http\Controllers\Admin\Masters\LeaveController::class );
    Route::resource('shifts', App\Http\Controllers\Admin\Masters\ShiftController::class );
    Route::resource('devices', App\Http\Controllers\Admin\Masters\DeviceController::class );
    Route::resource('contractors', App\Http\Controllers\Admin\Masters\ContractorController::class );


    // Users Roles n Permissions
    Route::resource('users', App\Http\Controllers\Admin\UserController::class );
    Route::get('users/{user}/toggle', [App\Http\Controllers\Admin\UserController::class, 'toggle' ])->name('users.toggle');
    Route::put('users/{user}/change-password', [App\Http\Controllers\Admin\UserController::class, 'changePassword' ])->name('users.change-password');
    Route::get('users/{user}/get-role', [App\Http\Controllers\Admin\UserController::class, 'getRole' ])->name('users.get-role');
    Route::put('users/{user}/assign-role', [App\Http\Controllers\Admin\UserController::class, 'assignRole' ])->name('users.assign-role');
    Route::resource('roles', App\Http\Controllers\Admin\RoleController::class );


    // Employees Routes
    Route::resource('employees', App\Http\Controllers\Admin\EmployeeController::class );
    Route::get('employees/{employee:emp_code}/info', [App\Http\Controllers\Admin\EmployeeController::class, 'fetchInfo'] )->name('employees.info');


    // Punches Routes
    Route::resource('punches', App\Http\Controllers\Admin\PunchController::class );
    Route::get('punches/{punch}/toggle', [App\Http\Controllers\Admin\PunchController::class, 'toggle' ])->name('punches.toggle');


    // Leaves Routes
    Route::resource('leave-requests', App\Http\Controllers\Admin\LeaveRequestController::class );
    Route::put('leave-requests/{leave_request}/change-request', [App\Http\Controllers\Admin\LeaveRequestController::class, 'changeRequest'] )->name('leave-requests.change-request');
    Route::get('active-medical-leave-requests', [App\Http\Controllers\Admin\LeaveRequestController::class, 'activeMedicalLeaveRequest'] )->name('leave-requests.active-medical-leave');
    Route::get('completed-medical-leave-requests', [App\Http\Controllers\Admin\LeaveRequestController::class, 'completedMedicalLeaveRequest'] )->name('leave-requests.completed-medical-leave');
    Route::get('leave-applications', [App\Http\Controllers\Admin\LeaveRequestController::class, 'pendingLeaveRequest'] )->name('leave-requests.application');


    // Employee Shifts
    Route::resource('rosters', App\Http\Controllers\Admin\RosterController::class );
    Route::get('sample_roster', [App\Http\Controllers\Admin\RosterController::class, 'downloadSample'] )->name('rosters.sample');
    Route::post('import_shift_roster', [App\Http\Controllers\Admin\RosterController::class, 'importShiftRoster'] )->name('rosters.import');


    // Reports
    Route::resource('reports', App\Http\Controllers\Admin\ReportController::class );
    Route::get('muster-report', [App\Http\Controllers\Admin\ReportController::class, 'musterReport'] )->name('reports.muster');
    Route::get('reports/get/month-wise-date', [App\Http\Controllers\Admin\ReportController::class, 'monthWiseDate'] )->name('reports.dates');
    Route::get('device_log_report', [App\Http\Controllers\Admin\ReportController::class, 'deviceLogReport'])->name('dashboard.device-log-report');
    Route::get('daily_attendance_report', [App\Http\Controllers\Admin\ReportController::class, 'dailyAttendanceReport'])->name('dashboard.daily-attendance-report');
    Route::get('department_wise_report', [App\Http\Controllers\Admin\ReportController::class, 'departmentWiseReport'])->name('dashboard.department-wise-report');
    Route::get('todays_present_report', [App\Http\Controllers\Admin\ReportController::class, 'todaysPresentReport'])->name('dashboard.todays-present-report');
    Route::get('todays_absent_report', [App\Http\Controllers\Admin\ReportController::class, 'todaysAbsentReport'])->name('dashboard.todays-absent-report');
    Route::get('shift_wise_employee/{shift_id}', [App\Http\Controllers\Admin\ReportController::class, 'shiftWiseEmployees'])->name('dashboard.shift-wise-employee');
    Route::get('todays_leave_bifurcation', [App\Http\Controllers\Admin\ReportController::class, 'todaysLeaveBifurcation'])->name('dashboard.todays-leave-bifurcation');
    Route::get('month_wise_latemark', [App\Http\Controllers\Admin\ReportController::class, 'monthWiseLatemark'])->name('dashboard.month-wise-latemark');
    Route::get('month_wise_absent', [App\Http\Controllers\Admin\ReportController::class, 'monthWiseAbsent'])->name('dashboard.month-wise-absent');
    Route::get('employee_wise_report', [App\Http\Controllers\Admin\ReportController::class, 'employeeWiseReport'])->name('dashboard.employee-wise-report');
    Route::get('device-wise-report', [App\Http\Controllers\Admin\ReportController::class, 'deviceWiseReport'])->name('reports.device-wise-report');
    Route::get('employee-report', [App\Http\Controllers\Admin\ReportController::class, 'employeeReport'])->name('reports.employee-report');


});




Route::prefix('employee')->name('employee.')->group(function(){

    // Guest employees
    Route::middleware(['employee.guest','PreventBackHistory'])->group(function()
    {
        Route::get('/', fn()=> redirect()->route('login', ['device_type'=> 'mobile']) )->name('login');
        Route::get('/register', [App\Http\Controllers\Employee\AuthController::class, 'showRegister'])->name('register');
        Route::post('/emp-info', [App\Http\Controllers\Employee\AuthController::class, 'searchEmployeeCode'])->name('emp-info');
        Route::post('/register', [App\Http\Controllers\Employee\AuthController::class, 'register'])->name('signup');
    });

    // Authenticated employees
    Route::middleware(['employee.auth','PreventBackHistory'])->group(function()
    {
        Route::get('/home', [App\Http\Controllers\Employee\HomeController::class, 'index'])->name('home');
        Route::post('/employee-logout', [App\Http\Controllers\Employee\HomeController::class, 'logout'])->name('logout');
        Route::get('/delete-account', [App\Http\Controllers\Employee\HomeController::class, 'deleteAccount'])->name('delete-account');

        Route::get('show-change-password', [App\Http\Controllers\Employee\HomeController::class, 'showChangePassword'] )->name('show-change-password');
        Route::post('change-password', [App\Http\Controllers\Employee\HomeController::class, 'changePassword'] )->name('change-password');
    });

});

Route::get('/privacy-policy', [App\Http\Controllers\Employee\HomeController::class, 'privacyPolicy'])->name('privacy-policy');






Route::get('/import-department', function(){
    Artisan::call('department:import');
    return dd(Artisan::output());
});
Route::get('/import-punches', function(){
    Artisan::call('punches:import');
    return dd(Artisan::output());
});
Route::get('/import-employees', function(){
    Artisan::call('employees:import');
    return dd(Artisan::output());
});

Route::get('/php', function(Request $request){
    // if( !auth()->check() )
    //     return 'Unauthorized request';

    Artisan::call($request->artisan);
    return dd(Artisan::output());
});


Route::get('/test-code', function(){
    // DB::table('punches')
    //     ->where('punch_by', '2')
    //     ->orderBy('id')
    //     ->groupBy('punch_date', 'emp_code')
    //     ->delete();

    return 'done';
});

Route::get('/remove-duplicate', function(){
    DB::table('punches as p1')
        ->where('punch_by', '2')
        ->join(DB::raw('
            (SELECT punch_date, emp_code, MIN(id) AS min_id
            FROM punches
            GROUP BY punch_date, emp_code
            HAVING COUNT(*) > 1
            ) as p2'), function ($join) {
                $join->on('p1.punch_date', '=', 'p2.punch_date')
                    ->on('p1.emp_code', '=', 'p2.emp_code')
                    ->where('p1.id', '>', DB::raw('p2.min_id'));
        })
        ->delete();

    return 'done';
});
