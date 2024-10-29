<?php

namespace App\Imports;

use App\Models\Punch;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ImportEmployeeShift implements ToModel, WithStartRow
{

    private $fromDate;
    private $toDate;
    private $departmentId;

    public function __construct($arrayData)
    {
        $this->fromDate = Carbon::parse($arrayData['from_date']);
        $this->toDate = Carbon::parse($arrayData['to_date']);
        $this->departmentId = $arrayData['department_id'];
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $clonedDate = clone($this->fromDate);
        $clonedDate = $clonedDate->next($row[1])->toDateString();
        DB::beginTransaction();

        $user = User::whereEmpCode($row[0])->where('department_id', $this->departmentId)->first();
        if($user)
        {
            $shift = Shift::whereName($row[3])->first();
            $user->update([ 'shift_id'=> $shift->id ]);
            $user->weekoff()->create([
                'shift_in_time'=> $shift->from_time,
                'weekoff_1'=> $row[1],
                'weekoff_2'=> $row[2] ?? null,
                'start_of_week'=> Carbon::parse($this->fromDate)->toDateString(),
                'end_of_week'=> Carbon::parse($this->toDate)->toDateString(),
            ]);
            $noOfWeeks = $this->fromDate->diffInWeeks($this->toDate);
            for( $i=1; $i<=$noOfWeeks; $i++ )
            {
                Punch::create([
                    'emp_code' => $user->emp_code,
                    'device_id' => 0,
                    'check_in' => Carbon::createFromFormat('Y-m-d H:i:s', $clonedDate.' 10:00:00')->toDateTimeString(),
                    'check_out' => Carbon::createFromFormat('Y-m-d H:i:s', $clonedDate.' 19:00:00')->toDateTimeString(),
                    'duration' => '0',
                    'punch_date' => $clonedDate,
                    'type' => Punch::PUNCH_TYPE_SAT_SUN,
                    'is_paid' => '1',
                ]);
            }
        }
        DB::commit();
    }
}
