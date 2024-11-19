<?php

namespace App\Http\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use ProtoneMedia\LaravelCrossEloquentSearch\Search;

class EmpList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['$refresh'];

    // TABLE FUNCTIONALITY
    public $records_per_page = 10;
    public $search = '';
    public $column = 'app_users.created_at';
    public $order = 'DESC';
    //

    public function render()
    {
        $authUser = Auth::user();
        $employees = Search::add(
                        User::withTrashed()->whereIsEmployee('1')
                            ->with('designation', 'clas', 'shift', 'deletedBy')
                            ->with(['subDepartment', 'department', 'ward'])
                            ->leftJoin('wards', 'app_users.ward_id', '=', 'wards.id')
                            ->leftJoin('departments', 'app_users.department_id', '=', 'departments.id')
                            ->leftJoin('contractors', 'app_users.contractor', '=', 'contractors.id')
                            ->select('app_users.*', 'wards.name as ward_name', 'departments.name as department_name', 'contractors.name as contractor_name')
                            ->where('app_users.tenant_id', $authUser->tenant_id)
                            ->when( !$authUser->hasRole(['Admin', 'Super Admin']), fn($q)=> $q->where('app_users.department_id', $authUser->department_id) )
                            ->orderBy($this->column, $this->order),
                                [ 'id', 'emp_code', 'name', 'mobile', 'department.name', 'ward.name' ]
                            )
                            ->paginate((int)$this->records_per_page)
                            ->beginWithWildcard()
                            ->search($this->search);

        return view('livewire.emp-list', compact('employees'));
    }

    public function restoreEmployee($empId)
    {
        User::withTrashed()->find($empId)->restore();
    }

    public function sorting($column, $order)
    {
        if($this->column == $column)
        {
            $this->order = $order == 'ASC' ? 'DESC' : 'ASC';
        }
        else
        {
            $this->column = $column;
            $this->order = $order;
        }
    }
}
