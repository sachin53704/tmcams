<?php

namespace App\Exports;

use App\Models\Shift;
use App\Models\User;
use App\Models\WeekDay;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class ExportEmployeeShift implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $users;
    protected $selects;
    protected $row_count;
    protected $column_count;
    protected $weekDays;
    protected $shifts;

    public function __construct()
    {
        $authUser = Auth::user();
        $users = User::select('id', 'emp_code', 'name', 'email')
                        ->where('tenant_id', $authUser->tenant_id)
                        ->whereIsEmployee('1')
                        ->whereActiveStatus('1')->whereNot('id', $authUser->id);

        if($authUser->hasRole(['Admin', 'Super Admin']))
            $users = $users->latest()->get();
        else
            $users = $users->where('department_id', $authUser->department_id)->get();

        $this->users = $users;

        $weekDays = WeekDay::pluck('name')->toArray();
        $shifts = Shift::pluck('name')->take(10)->toArray();
        // dd($shifts);
        $selects=[
            [ 'columns_name'=> 'B', 'options'=> $weekDays ],
            [ 'columns_name'=> 'C', 'options'=> $weekDays ],
            [ 'columns_name'=> 'D', 'options'=> $shifts ],
        ];
        $this->selects= $selects;
        $this->row_count= 30;
        $this->column_count= 5;
    }


    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect([]);
    }

    public function headings():array{
        return[
            'Emp Code',
            'Week Off 1',
            'Week Off 2',
            'Shift',
        ];
    }

    public function map($users): array
    {
        return [
            '',
            '',
            '',
            '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $row_count = $this->row_count;
                $column_count = $this->column_count;
                foreach ($this->selects as $select){
                    $drop_column = $select['columns_name'];
                    $options = $select['options'];
                    // set dropdown list for first data row
                    $validation = $event->sheet->getCell("{$drop_column}2")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST );
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION );
                    $validation->setAllowBlank(false);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowDropDown(true);
                    $validation->setErrorTitle('Input error');
                    $validation->setError('Value is not in list.');
                    $validation->setPromptTitle('Pick from list');
                    $validation->setPrompt('Please pick a value from the drop-down list.');
                    $validation->setFormula1(sprintf('"%s"',implode(',',$options)));

                    // clone validation to remaining rows
                    for ($i = 3; $i <= $row_count; $i++) {
                        $event->sheet->getCell("{$drop_column}{$i}")->setDataValidation(clone $validation);
                    }
                    // set columns to autosize
                    for ($i = 1; $i <= $column_count; $i++) {
                        $column = Coordinate::stringFromColumnIndex($i);
                        $event->sheet->getColumnDimension($column)->setAutoSize(true);
                    }
                }

            },
        ];
    }
}
