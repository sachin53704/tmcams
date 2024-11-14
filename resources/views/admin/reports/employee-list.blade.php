<x-admin.admin-layout>
    <x-slot name="title">{{ auth()->user()->tenant_name }} - Employee Report</x-slot>

    <div class="page-body">
        <div class="container-fluid">
            <div class="page-header py-3">


                <div class="row">
                    <div class="col-sm-12">
                        <h3>Employee Report</h3>

                        @if(Session::has('success'))
                            <div class="alert alert-success text-center">
                                {{Session::get('success')}}
                            </div>
                        @endif

                        <div class="card">
                            
                        </div>

                    </div>
                    <div class="col-sm-6">
                    </div>
                </div>
            </div>
        </div>
        <!-- Container-fluid starts-->
        <div class="container-fluid support-ticket">
            <div class="row">

                <div class="col-sm-12">
                    <div class="card">

                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="">
                                        {{-- <button id="addToTable" class="btn btn-primary">Manual Attendance <i class="fa fa-plus"></i></button> --}}
                                        <button id="btnCancel" class="btn btn-danger" style="display:none;">Cancel</button>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="display table-bordered" id="datatable-tabletools">
                                    <thead>
                                        <tr>
                                            <th>Sr No</th>
                                            <th>Emp Code</th>
                                            <th>Emp Name</th>
                                            <th>Emp Mobile</th>
                                            <th>Emp Gender</th>
                                            <th>Emp Type</th>
                                            <th>Contractor Name</th>
                                            <th>Is Rotational</th>
                                            <th>Shift</th>
                                            <th>Office</th>
                                            <th>Department</th>
                                            <th>Machine</th>
                                            <th>Class</th>
                                            <th>Designation</th>
                                            <th>Is OT Allow</th>
                                            <th>Employee Is Divyang</th>
                                            <th>Is Fix Half Day On Saturday</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                            @foreach ($empList as $list)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $list->emp_code }}</td>
                                                    <td>{{ $list->name }}</td>
                                                    <td>{{ (!empty($list->mobile)) ? $list->mobile : 'NA' }}</td>
                                                    <td>{{ ($list->gender == 'm') ? "Male" : "Female" }}</td>
                                                    <td>{{ ($list->employee_type == '0') ? "Contractual" : "Permanent" }}</td>
                                                    <td>{{ (!empty($list->contractorName)) ? $list->contractorName : 'NA' }}</td>
                                                    <td>{{ ($list->is_rotational == '0') ? "Not" : "Yes" }}</td>
                                                    <td>{{ (!empty($list->shiftName)) ? $list->shiftName : 'NA' }}</td>
                                                    <td>{{ (!empty($list->wardName)) ? $list->wardName : 'NA' }}</td>
                                                    <td>{{ $list->department->name }}</td>
                                                    <td>{{ (!empty($list->devicesName)) ? $list->devicesName : 'NA' }}</td>
                                                    <td>{{ (!empty($list->clasName)) ? $list->clasName : 'NA' }}</td>
                                                    <td>{{ $list->designation?->name }}</td>
                                                    <td>{{ ($list->is_ot == 'y') ? "Yes" : "No" }}</td>
                                                    <td>{{ ($list->is_divyang == 'y') ? "Yes" : "No" }}</td>
                                                    <td>{{ ($list->is_rotational == 'y') ? "Yes" : "No" }}</td>
                                                </tr>
                                            @endforeach
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- Container-fluid Ends-->
    </div>



</x-admin.admin-layout>








