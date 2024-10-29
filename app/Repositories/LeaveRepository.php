<?php

namespace App\Repositories;

use App\Models\LeaveRequest;
use App\Models\LeaveRequestDocument;
use App\Models\LeaveType;
use App\Models\Punch;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LeaveRepository
{

    public function storeLeaveRequest($input, $user)
    {
        DB::beginTransaction();
            $input['path'] = isset($input['file']) ? 'leave_document/'.$input['file']->store('','leave_document') : '';

            $input['user_id'] = $user->id;
            $input['from_date'] = $input['from_date'] ?? $input['date'];
            $type_const = strtoupper( 'LEAVE_FOR_TYPE_'.$input['page_type'] );
            $input['request_for_type'] = constant("App\Models\LeaveRequest::$type_const");
            $input['is_approved'] = '1';

            $leaveRequest = LeaveRequest::create( Arr::only( $input, LeaveRequest::getFillables() ) );
            $leaveRequest->document()->create( Arr::only( $input, LeaveRequestDocument::getFillables() ) );

            $this->changeRequest(['status'=> '1', 'reason'=> 'Leave auto approved by system'], $leaveRequest);
        DB::commit();

        return $leaveRequest;
    }


    public function editLeaveRequest($leave_request)
    {
        $leave_request->load('document', 'user.ward', 'user.clas', 'user.department');
        $leaveTypes = LeaveType::latest()->get();

        if ($leaveTypes)
        {
            $leaveTypeHtml = '<span>
                <option value="">--Select Leave Type--</option>';
                foreach($leaveTypes as $lt):
                    $is_select = $lt->id == $leave_request->leave_type_id ? "selected" : "";
                    $leaveTypeHtml .= '<option value="'.$lt->id.'" '.$is_select.'>'.$lt->name.'</option>';
                endforeach;
            $leaveTypeHtml .= '</span>';

            // Make file html
            $fileHtml = '';
            if( $leave_request->document->path == '' )
            {
                $fileHtml.= '<label class="col-form-label card rounded" for="file">No document uploaded </label>';
            }
            else
            {
                $fileNameParts = explode('.', $leave_request->document->path);
                $ext = end($fileNameParts);

                if($ext == 'pdf')
                    $fileHtml.= ' <a class="btn btn-primary" target="_blank" href="'.asset($leave_request->document->path).'">Open File</a>';
                else
                    $fileHtml.= '<a href="'.asset($leave_request->document->path).'" target="_blank"> <img class="img-fluid" src="'.asset($leave_request->document->path).'" style="border-radius: 8px; max-height: 200px; max-width: 150px" /> </a>';
            }

            $response = [
                'result' => 1,
                'leave_request' => $leave_request,
                'leaveTypeHtml' => $leaveTypeHtml,
                'fileHtml' => $fileHtml,
            ];
        }
        else
        {
            $response = ['result' => 0];
        }
        return $response;
    }


    public function updateLeaveRequest($input, $leave_request)
    {
        if( gettype($leave_request) === 'string' || gettype($leave_request) ===  'integer' )
            $leave_request = LeaveRequest::findOrFail($leave_request);

        $leave_request->load('document');

        if(isset($input['file']))
        {
            if(File::exists( Storage::get( 'leave_document'.$leave_request->document->path ) ))
                File::delete(Storage::get( 'leave_document'.$leave_request->document->path ));

            $input['path'] = isset($input['file']) ? 'leave_document/'.$input['file']->store('','leave_document') : '';
            $leave_request->document()->update( Arr::only( $input, LeaveRequestDocument::getFillables() ) );
        }

        DB::beginTransaction();
        $input['from_date'] = $input['from_date'] ?? $input['date'];
        $leave_request->update( Arr::only( $input, LeaveRequest::getFillables() ) );
        DB::commit();

        return true;
    }


    // Approve/Reject Leave Request
    public function changeRequest($input, $leave_request)
    {
        // If request is rejected then update the status and return response
        // if($input['status'] == 2)
        // {
        //     $leave_request->is_approved = $input['status'];
        //     $leave_request->approver_remark = $input['reason'];
        //     $leave_request->save();
        //     return true;
        // }

        // #### If request is approved then proceed further conditions ####
        // DB::beginTransaction();
        $leave_request->is_approved = $input['status'];
        $leave_request->save();

        if( $leave_request->to_date == null && $leave_request->request_for_type == 2 )                      // Check if leave is half day
            $this->createLeaveRequestPunch($leave_request, 'half_day');

        // else if( $leave_request->to_date == null && $leave_request->request_for_type != 2 )                 // Check if leave is Unpredictable/Medical leave
        //     $this->createLeaveRequestPunch($leave_request, 'medical');

        else                                                                                                // Else leave is full day predictable
            $this->createLeaveRequestPunch($leave_request, 'full_day');
        // DB::commit();

        return true;
    }


    protected function createLeaveRequestPunch($leave_request, $type)
    {
        if( gettype($leave_request) === 'string' || gettype($leave_request) === 'integer' )
            $leave_request = LeaveRequest::findOrFail($leave_request);

        $leave_request->load('leaveType.leave', 'user.latestWeekoff');
        $defaultShift = collect( config('default_data.shift_time') );

        $allotedTime = '';
        if($leave_request->user->latestWeekoff)
            $allotedTime = Carbon::parse($leave_request->user->latestWeekoff->end_of_week)->gt($leave_request->from_date) ? $leave_request->user->latestWeekoff->shift_in_time : $leave_request->user->in_time;
        else
            $allotedTime = $leave_request->user->in_time ?? $defaultShift['from_time'];
        $allotedTime = $allotedTime ?? $defaultShift['from_time'];

        if($type == 'half_day')                      //If leave request is halfday
        {
            Punch::updateOrCreate(
                [ 'punch_date'=> Carbon::parse($leave_request->from_date), 'emp_code'=> $leave_request->user->emp_code],
                [
                    'device_id'=> $leave_request->user->device_id ?? 0,
                    'check_in'=> Carbon::createFromFormat('Y-m-d H:i:s', $leave_request->from_date.' '.$allotedTime)->toDateTimeString() ,
                    'check_out'=> Carbon::createFromFormat('Y-m-d H:i:s', $leave_request->from_date.' '.$allotedTime)->addSeconds(16200)->toDateTimeString() ,
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
        elseif($type == 'full_day')                  // If leave request is fullday
        {
            $dateRanges = CarbonPeriod::create( Carbon::parse($leave_request->from_date), Carbon::parse($leave_request->to_date) )->toArray();
            foreach($dateRanges as $dateRange)
            {
                $allotedTime = '';
                if($leave_request->user->latestWeekoff)
                    $allotedTime = Carbon::parse($leave_request->user->latestWeekoff->end_of_week)->gt($dateRange->toDateString()) ? $leave_request->user->latestWeekoff->shift_in_time : $leave_request->user->in_time;
                else
                    $allotedTime = $leave_request->user->in_time ?? $defaultShift['from_time'];
                $allotedTime = $allotedTime ?? $defaultShift['from_time'];

                $punchCondition = $leave_request->leave_type_id == 2 ?
                                    [ 'punch_date'=> $dateRange->toDateString(), 'emp_code'=> $leave_request->user->emp_code ]
                                    : [ 'punch_date'=> $dateRange->toDateString(), 'emp_code'=> $leave_request->user->emp_code ];

                Punch::updateOrCreate(
                    $punchCondition,
                    [
                        'device_id'=> $leave_request->user->device_id ?? 0,
                        'check_in'=> Carbon::createFromFormat('Y-m-d H:i:s', $dateRange->toDateString().' '.$allotedTime)->toDateTimeString(),
                        'check_out'=> Carbon::createFromFormat('Y-m-d H:i:s', $dateRange->toDateString().' '.Carbon::parse($allotedTime)->addHours(9)->toTimeString())->toDateTimeString(),
                        'duration'=> Carbon::parse($allotedTime)->diffInSeconds(Carbon::parse($allotedTime)->addHours(9)),
                        'punch_by'=> Punch::PUNCH_BY_ADJUSTMENT,
                        'type'=> Punch::PUNCH_TYPE_LEAVE,
                        'leave_type_id'=> $leave_request->leave_type_id,
                        'is_latemark'=> '0',
                        'is_latemark_updated'=> '1',
                        'is_duration_updated'=> '1',
                        'is_paid'=> $leave_request->leaveType->is_paid == 'no' ? Punch::PUNCH_IS_UNPAID : Punch::PUNCH_IS_PAID,
                    ]);
            }
        }
        elseif($type == 'medical')                   // If leave request is medical
        {
            // Do nothing just update the approval status and cronjob will mark the punches daily
        }

        return true;
    }


}
