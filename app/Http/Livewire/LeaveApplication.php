<?php

namespace App\Http\Livewire;

use App\Models\LeaveRequest as ModelsLeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use ProtoneMedia\LaravelCrossEloquentSearch\Search;


class LeaveApplication extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['$refresh'];

    // TABLE FUNCTIONALITY
    public $records_per_page = 10;
    public $search = '';
    public $column = 'user.created_at';
    public $order = 'DESC';

    public function render()
    {
        $authUser = Auth::user();
        $leaveRequests = Search::add(
                        ModelsLeaveRequest::with('user.clas', 'user.department', 'user.ward', 'leaveType', 'document')
                                    ->when(!$authUser->hasRole(['Admin', 'Super Admin']),
                                        fn($q)=> $q->withWhereHas('user', fn($qr)=> $qr->where('department_id', $authUser->department_id)->where('tenant_id', $authUser->tenant_id) )
                                    )
                                    ->when($authUser->hasRole(['Admin', 'Super Admin']),
                                        fn($q)=> $q->withWhereHas('user', fn($qr)=> $qr->where('tenant_id', $authUser->tenant_id) )
                                    )
                                    ->latest(),
                                [ 'id', 'from_date', 'to_date', 'no_of_days', 'remark', 'user.name', 'user.emp_code' ]
                            )
                            ->paginate((int)$this->records_per_page)
                            ->beginWithWildcard()
                            ->search($this->search);

        return view('livewire.leave-application')->with(['leaveRequests'=> $leaveRequests]);
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
