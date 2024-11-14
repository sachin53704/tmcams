<div style="">
    <div wire:loading.flex style="position: absolute;
        width: 100%;
        height: 100%;
        justify-content: center;
        align-items: center;
        background: rgba(245,245,251,0.6);
        margin-top: -30px;
        margin-left: -30px;
        z-index: 4;
        pointer-events: none;
        font-size: 20px;
        font-weight: 600">Loading...
    </div>

    <div class="row p-1">
        <div class="col-sm-3 col-md-3 me-auto">
            <select name="records_per_page" id="" class="form-control" wire:model="records_per_page" style="max-width: 60px">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="col-sm-9 col-md-3 ms-auto">
            <input type="text" name="search" wire:model.debounce.500="search" class="form-control" placeholder="search..">
        </div>
    </div>

    <div class="row" style="overflow-x: scroll">
        <div class="col-12">
            <table class="table table-hover" id="list_table">
                <thead>
                    <tr>
                        {{-- <th style="min-width: 100px" wire:click="sorting('app_users.id', '{{$order}}')" class="sortable {{ $column == 'app_users.id' ? 'active' : '' }} {{ $order }}" scope="col"> <span class="custom_th">Sr No. </span> <span class="arrow"></span> </th> --}}
                        <th style="min-width: 100px" scope="col"> <span class="custom_th">Sr No. </span> <span class="arrow"></span> </th>
                        <th style="min-width: 110px" wire:click="sorting('app_users.emp_code', '{{$order}}')" class="sortable {{ $column == 'app_users.emp_code' ? 'active' : '' }} {{ $order }}" scope="col"> <span class="custom_th">Emp Id </span> <span class="arrow"></span> </th>
                        <th wire:click="sorting('app_users.name', '{{$order}}')" class="sortable {{ $column == 'app_users.name' ? 'active' : '' }} {{ $order }}" scope="col"> <span class="custom_th">Name </span> <span class="arrow"></span> </th>
                        <th style="min-width: 160px" wire:click="sorting('departments.name', '{{$order}}')" class="sortable {{ $column == 'departments.name' ? 'active' : '' }} {{ $order }}" scope="col"> <span class="custom_th">Department </span> <span class="arrow"></span> </th>
                        <th style="min-width: 160px" scope="col"> <span class="custom_th">Employee Type </span> <span class="arrow"></span> </th>
                        <th style="min-width: 160px" scope="col"> <span class="custom_th">Contractor Name </span> <span class="arrow"></span> </th>
                        {{-- <th style="min-width: 110px" wire:click="sorting('mobile', '{{$order}}')" class="sortable {{ $column == 'mobile' ? 'active' : '' }} {{ $order }}" scope="col"> <span class="custom_th">Mobile </span> <span class="arrow"></span> </th> --}}
                        {{-- <th style="min-width: 140px" wire:click="sorting('wards.name', '{{$order}}')" class="sortable {{ $column == 'wards.name' ? 'active' : '' }} {{ $order }}" scope="col"> <span class="custom_th">Office </span> <span class="arrow"></span> </th> --}}
                        <th style="min-width: 120px" scope="col">Details</th>
                        <th style="min-width: 90px" scope="col">Status</th>
                        <th style="min-width: 120px" scope="col">Removed/Working</th>
                        {{-- <th style="min-width: 190px" wire:click="sorting('app_users.created_at', '{{$order}}')" class="sortable {{ $column == 'app_users.created_at' ? 'active' : '' }} {{ $order }}" scope="col"> <span class="custom_th">Registered On </span> <span class="arrow"></span>  </th> --}}
                        <th style="min-width: 130px" scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = $employees->perPage() * ($employees->currentPage() -1 );
                    @endphp
                    @foreach ($employees as $emp)
                        <tr>
                            <td>{{ ++$i }}</td>
                            <td>{{ $emp->emp_code }}</td>
                            <td>{{ $emp->name }}</td>
                            <td>{{ $emp->department_name }}</td>
                            <td>
                                @if ($emp->employee_type == 0)
                                    Contractual
                                @else
                                    Permanent
                                @endif
                            </td>
                            <td>
                                {{ $emp->contractor_name ?? 'NA' }}
                            </td>
                            {{-- <td>{{ $emp->mobile }}</td> --}}
                            {{-- <td>{{ $emp->ward_name }}</td> --}}
                            <td>
                                <button class="emp-more-info btn btn-primary px-2 py-1" title="More info" data-id="{{ $emp->id }}"><i class="fa fa-circle-info"></i></button>
                            </td>
                            <td>
                                <div class="media-body text-end icon-state">
                                    <label class="switch">
                                        <input type="checkbox" class="status" data-id="{{ $emp->id }}" {{ $emp->active_status == '1' ? 'checked' : '' }}><span class="switch-state"></span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                @if ($emp->deleted_at == null)
                                    <strong>Working</strong>
                                @else
                                    <span>Removed by :</span> <br>
                                    {{ $emp->deletedBy?->emp_code }} - {{ Str::limit($emp->deletedBy?->name, 20) }} - {{ $emp->deleted_at }}
                                @endif
                            </td>
                            {{-- <td>
                                {{ \Carbon\Carbon::parse($emp->created_at)->format('d M, y h:i:s') }}
                            </td> --}}
                            <td>
                                @if ($emp->deleted_at == null)
                                    <button class="edit-element btn btn-success px-2 py-1" title="Edit Employee" data-id="{{ $emp->id }}"><i class="fa fa-pencil"></i></button>
                                    <button class="btn btn-warning change-password px-2 py-1" title="Change Password" data-id="{{ $emp->id }}"><i class="fa fa-lock"></i></button>
                                    <button class="btn btn-danger remove-element px-2 py-1" title="Delete Employee" data-id="{{ $emp->id }}"><i class="fa fa-trash"></i></button>
                                @else
                                    @if( Auth::user()->hasRole(['Super Admin']))
                                        <button class="btn btn-info restore-element px-2 py-1" title="Restore Employee" wire:key="{{ $emp->id }}" wire:click="restoreEmployee({{$emp->id}})" ><i class="fa fa-recycle"></i></button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="d-flex justify-content-between">
            <div>
                Total Employee: {{ $employees->total() }}
            </div>
            <div>
                {{ $employees->links() }}
            </div>
        </div>
    </div>

</div>
