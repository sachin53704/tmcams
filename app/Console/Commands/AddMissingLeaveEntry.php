<?php

namespace App\Console\Commands;

use App\Models\LeaveRequest;
use Illuminate\Console\Command;

class AddMissingLeaveEntry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'punches:leave-entry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $leaveRequests = LeaveRequest::with('user.latestWeekoff')
        //                     ->orderByDesc('id')->chunk(100, function($q){



        //     $dateRanges = CarbonPeriod::create( Carbon::parse($leave_request->from_date), Carbon::parse($leave_request->to_date) )->toArray();
        //     foreach($dateRanges as $dateRange)
        //     {
        //         $allotedTime = '';
        //         if($leave_request->user->latestWeekoff)
        //             $allotedTime = Carbon::parse($leave_request->user->latestWeekoff->end_of_week)->gt($dateRange->toDateString()) ? $leave_request->user->latestWeekoff->shift_in_time : $leave_request->user->in_time;
        //         else
        //             $allotedTime = $leave_request->user->in_time ?? $defaultShift['from_time'];
        //         $allotedTime = $allotedTime ?? $defaultShift['from_time'];

        //         Punch::updateOrCreate(
        //             [ 'punch_date'=> $dateRange->toDateString(), 'emp_code'=> $leave_request->user->emp_code ],
        //             [
        //                 'device_id'=> $leave_request->user->device_id ?? 0,
        //                 'check_in'=> Carbon::createFromFormat('Y-m-d H:i:s', $dateRange->toDateString().' '.$allotedTime)->toDateTimeString(),
        //                 'check_out'=> Carbon::createFromFormat('Y-m-d H:i:s', $dateRange->toDateString().' '.Carbon::parse($allotedTime)->addHours(9)->toTimeString())->toDateTimeString(),
        //                 'duration'=> Carbon::parse($allotedTime)->diffInSeconds(Carbon::parse($allotedTime)->addHours(9)),
        //                 'punch_by'=> Punch::PUNCH_BY_ADJUSTMENT,
        //                 'type'=> Punch::PUNCH_TYPE_LEAVE,
        //                 'leave_type_id'=> $leave_request->leave_type_id,
        //                 'is_latemark'=> '0',
        //                 'is_latemark_updated'=> '1',
        //                 'is_duration_updated'=> '1',
        //                 'is_paid'=> $leave_request->leaveType->is_paid == 'no' ? Punch::PUNCH_IS_UNPAID : Punch::PUNCH_IS_PAID,
        //             ]);
        //     }





        // })
    }
}
