<x-admin.admin-layout>
    <x-slot name="title">{{ auth()->user()->tenant_name }} - Device Wise Report</x-slot>

    <div class="page-body">
        <div class="container-fluid">
            <div class="page-header">


                <div class="row">
                    <div class="col-sm-12">
                        <h3>Device Wise Report</h3>

                        @if(Session::has('success'))
                            <div class="alert alert-success text-center">
                                {{Session::get('success')}}
                            </div>
                        @endif

                        <div class="card">
                            <form class="theme-form" method="GET" action="{{ route('reports.device-wise-report') }}">
                                <div class="card-body pt-0">

                                    <div class="mb-3 row">
                                        

                                        <div class="col-md-3 mt-3">
                                            <label class="col-form-label" for="from_date">Select Start Date <span class="text-danger">*</span> </label>
                                            <input class="form-control" name="from_date" type="date" onclick="this.showPicker()" value="{{ isset(request()->from_date) ? request()->from_date : date('Y-m-d') }}" required>
                                            @error('from_date')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="col-md-3 mt-3">
                                            <label class="col-form-label" for="to_date">Select End Date <span class="text-danger">*</span></label>
                                            <input class="form-control" name="to_date" type="date" onclick="this.showPicker()" value="{{ isset(request()->to_date) ? request()->to_date : date('Y-m-d') }}" required>
                                            @error('to_date')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>


                                        <div class="col-md-3 mt-3">
                                            <label class="col-form-label" for="device">Devices  </label>
                                            <select class="js-example-basic-single col-sm-12  @error('device') is-invalid  @enderror" name="device">
                                                <option value="">--Select Device--</option>
                                                @foreach ($devices as $devices)
                                                    <option value="{{ $devices->DeviceId }}" {{ request()->device == $devices->DeviceId ? 'selected' : '' }} >{{ $devices->DeviceLocation.'('.$devices->SerialNumber.')' }}</option>
                                                @endforeach
                                            </select>
                                            @error('devices')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="col-md-3 mt-3">
                                            <label class="col-form-label" for="class">Emp Code <span class="text-info" style="font-size: 10px">for individual report</span></label>
                                            <input class="form-control" name="emp_code" id="emp_code" type="text" value="{{ request()->emp_code }}" placeholder="Employee Code" >
                                            @error('emp_code')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                    </div>

                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary" >Submit</button>
                                    <button type="reset" class="btn btn-warning">Reset</button>
                                </div>
                            </form>
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
                                            <th>Designation</th>
                                            <th>Department</th>
                                            <th>Device / Location</th>
                                            <th>DateTime</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($data as $value)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $value->user?->emp_code }}</td>
                                                <td>{{ $value->user?->name }}</td>
                                                <td>{{ $value->user?->designation?->name }}</td>
                                                <td>{{ $value->user?->department?->name }}</td>
                                                <td>{{ $value?->device?->DeviceLocation }}</td>
                                                <td>{{ Carbon\Carbon::parse($value->LogDate)->format('Y-m-d h:i A') }}</td>
                                            </tr>
                                        @empty
                                            
                                        @endforelse
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



<!-- Get Sub departments -->
<script>
    $("select[name='department']").change( function(e) {
        e.preventDefault();

        var model_id = $(this).val();
        var url = "{{ route('departments.sub_departments', ':model_id') }}";

        $.ajax({
            url: url.replace(':model_id', model_id),
            type: 'GET',
            data: {
                '_token': "{{ csrf_token() }}"
            },
            success: function(data, textStatus, jqXHR)
            {
                if (!data.error)
                {
                    $("select[name='sub_department']").html(data.subDepartmentHtml);
                } else {
                    swal("Error!", data.error, "error");
                }
            },
            error: function(error, jqXHR, textStatus, errorThrown) {
                swal("Error!", "Some thing went wrong", "error");
            },
        });
    });
</script>


<!-- Show Details -->
<script>
    $("#datatable-tabletools").on("click", ".emp-more-info", function(e) {
        e.preventDefault();
        var model_id = $(this).attr("data-id");
        var url = "{{ route('punches.show', ':model_id') }}";

        $.ajax({
            url: url.replace(':model_id', model_id),
            type: 'GET',
            data: {
                '_token': "{{ csrf_token() }}"
            },
            success: function(data, textStatus, jqXHR)
            {
                console.log(data);
                if (data.result == 1)
                {
                    $("#more-info-modal").modal('show');
                    $("#empMoreInfo").html(data.html);
                }
                else
                {
                    swal("Error!", "Some thing went wrong", "error");
                }
            },
            error: function(error, jqXHR, textStatus, errorThrown) {
                swal("Error!", "Some thing went wrong", "error");
            },
        });
    });
</script>


{{-- Get month wise date --}}
<script>
    $(document).ready(function(){

        $("select[name='month']").change(function(e){
            e.preventDefault();
            var month = $("select[name='month']").val();
            var year = $("select[name='year']").val();

            if(month != '')
            {
                $.ajax({
                    url: "{{ route('reports.dates') }}",
                    type: 'GET',
                    data: {
                        'month': month,
                        'year': year,
                        '_token': "{{ csrf_token() }}"
                    },
                    success: function(data, textStatus, jqXHR)
                    {
                        if (!data.error)
                        {
                            $("input[name='from_date']").val(data.fromDate);
                            $("input[name='to_date']").val(data.toDate);
                        } else {
                            swal("Error!", data.error, "error");
                        }
                    },
                    error: function(error, jqXHR, textStatus, errorThrown) {
                        swal("Error!", "Some thing went wrong", "error");
                    },
                });
            }
            else
            {
            alert("please select month");
            }
        });

    });
</script>


