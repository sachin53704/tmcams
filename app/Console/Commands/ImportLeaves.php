<?php

namespace App\Console\Commands;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Punch;
use App\Models\User;
use App\Repositories\LeaveRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use function App\Helpers\caseMatchTable;

class ImportLeaves extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leaves:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import leaves from previous system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $leaveTypeArray = [ '1'=> 6, '2'=> 7, '3'=> 5, '4'=> 4 ];
        $leaveRepository = new LeaveRepository();


        DB::table('emp_leave_master')
            ->orderBy('eid')
            ->chunk(100, function($leaves) use ($leaveTypeArray, $leaveRepository){
                foreach($leaves as $leave)
                {
                    $empInfo = DB::table('app_users')->where('emp_code', $leave->emp_id)->first();
                    if(!$empInfo)
                        continue;

                    $leaveRequest = LeaveRequest::create([
                        'user_id'=> $empInfo->id,
                        'leave_type_id'=> $leaveTypeArray[$leave->leave_type],
                        'from_date'=> $leave->from_date,
                        'to_date'=> $leave->to_date,
                        'no_of_days'=> (int) abs($leave->no_of_day),
                        'remark'=> $leave->remark ?? '',
                        'request_for_type'=> '1',
                        'is_approved'=> '1',
                    ]);

                    $leaveRequest->document()
                        ->create([ 'path'=> 'leave_document/'.$leave->document ]);

                    $leaveRepository->changeRequest(['status'=> 1], $leaveRequest);
                }
            });


        $this->info('Importing Child Medical Leaves');

        $defaultShift = collect( config('default_data.shift_time') );
        DB::table('emp_leave_child')
            ->orderBy('id')
            ->where('leave_type', '2')
            ->chunk(100, function($leaveChilds) use ($defaultShift){
                foreach($leaveChilds as $leaveChild)
                {
                    $parentLeaveInfo = DB::table('emp_leave_master')->where('eid', $leaveChild->leave_emp_id)->first();
                    $user = User::with('latestWeekoff')->where('tenant_id', 1)->where('emp_code', $leaveChild->emp_id)->first();

                    if(!$parentLeaveInfo || !$user)
                        continue;

                    $date = Carbon::parse($leaveChild->date)->toDateString();
                    $allotedTime = '';
                    if($user->latestWeekoff)
                        $allotedTime = Carbon::parse($user->latestWeekoff->end_of_week)->gt($date) ? $user->latestWeekoff->shift_in_time : $user->in_time;
                    else
                        $allotedTime = $user->in_time ?? $defaultShift['from_time'];
                    $allotedTime = $allotedTime ?? $defaultShift['from_time'];

                    Punch::create([
                        'emp_code'=> $user->emp_code,
                        'device_id'=> '0',
                        'check_in'=> Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.$allotedTime)->toDateTimeString() ,
                        'check_out'=> Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.$allotedTime)->addSeconds(16200)->toDateTimeString() ,
                        'duration'=> Carbon::parse($allotedTime)->diffInSeconds( Carbon::parse($allotedTime)->addHours(9) ),
                        'punch_date'=> $date,
                        'is_latemark'=> '0',
                        'is_latemark_updated'=> '1',
                        'punch_by'=> '2',
                        'type'=> '1',
                        'leave_type_id'=> '7',
                        'is_paid'=> LeaveType::IS_MEDICAL_LEAVE_PAID,
                    ]);

                }
            });



        $this->info('Importing Half Day');

        DB::table('emp_half_day')
            ->orderBy('h_id')
            ->chunk(100, function($halfDays) use ($defaultShift) {
                foreach($halfDays as $halfDay)
                {
                    $user = User::with('latestWeekoff')->where('tenant_id', 1)->where('emp_code', $halfDay->emp_id)->first();
                    if(!$user)
                        continue;

                    $date = Carbon::parse($halfDay->date)->toDateString();
                    $allotedTime = '';
                    if($user->latestWeekoff)
                        $allotedTime = Carbon::parse($user->latestWeekoff->end_of_week)->gt($date) ? $user->latestWeekoff->shift_in_time : $user->in_time;
                    else
                        $allotedTime = $user->in_time ?? $defaultShift['from_time'];
                    $allotedTime = $allotedTime ?? $defaultShift['from_time'];

                    $leaveRequest = LeaveRequest::create([
                        'user_id'=> $user->id,
                        'from_date'=> $date,
                        'remark'=> $halfDay->remark ?? '',
                        'request_for_type'=> '2',
                        'is_approved'=> '1',
                    ]);

                    $leaveRequest->document()
                        ->create([ 'path'=> 'leave_document/'.$halfDay->document ]);

                    Punch::updateOrCreate(
                        [ 'punch_date'=> Carbon::parse($halfDay->date), 'emp_code'=> $halfDay->emp_id ],
                        [
                            'device_id'=> $user->device_id ?? 0,
                            'check_in'=> Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.$allotedTime)->toDateTimeString() ,
                            'check_out'=> Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.$allotedTime)->addSeconds(16200)->toDateTimeString() ,
                            'duration'=> Carbon::parse($allotedTime)->diffInSeconds( Carbon::parse($allotedTime)->addHours(9) ) / 2,
                            'punch_by'=> Punch::PUNCH_BY_ADJUSTMENT,
                            'type'=> Punch::PUNCH_TYPE_HALFDAY_LEAVE,
                            'leave_type_id'=> '0',
                            'is_latemark'=> '0',
                            'is_latemark_updated'=> '1',
                            'is_duration_updated'=> '1',
                            'is_paid'=> Punch::PUNCH_IS_PAID,
                        ]);
                }
            });

        $this->info('All leaves are imported');
    }
}
