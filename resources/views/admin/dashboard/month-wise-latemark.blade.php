<x-admin.admin-layout>
    <x-slot name="title">{{ auth()->user()->tenant_name }} - Month Wise Latemark</x-slot>

    <div class="page-body">
        <div class="container-fluid">
            <div class="page-header py-3">


                <div class="row">
                    <div class="col-sm-12">
                        <h3>Month Wise Latemark</h3>

                        <div class="card">
                            <form class="theme-form" method="GET" action="{{ route('dashboard.month-wise-latemark') }}">
                                @csrf
                                <div class="card-body pt-0">

                                    <div class="mb-3 row">
                                        <div class="col-md-3 mt-3">
                                            <label class="col-form-label" for="year">Year <span class="text-danger">*</span> </label>
                                            <select name="year" class="form-control" id="year">
                                                <option value="2022" {{ date('Y') == '2022' ? 'selected' : '' }}>2022</option>
                                                <option value="2023" {{ date('Y') == '2023' ? 'selected' : '' }}>2023</option>
                                                <option value="2024" {{ date('Y') == '2024' ? 'selected' : '' }}>2024</option>
                                            </select>
                                            <span class="text-danger error-text year_err"></span>
                                        </div>

                                        <div class="col-md-3 mt-3">
                                            <label class="col-form-label" for="month">Select Month<span class="text-danger">*</span></label>
                                            <select class="col-sm-12 form-control @error('month') is-invalid  @enderror" value="{{ old('month') }}" required name="month">
                                                <option value="">--Select Month--</option>
                                                <option value="1" {{ request()->month == 1 ? 'selected' : '' }} >January</option>
                                                <option value="2" {{ request()->month == 2 ? 'selected' : '' }} >February</option>
                                                <option value="3" {{ request()->month == 3 ? 'selected' : '' }} >March</option>
                                                <option value="4" {{ request()->month == 4 ? 'selected' : '' }} >April</option>
                                                <option value="5" {{ request()->month == 5 ? 'selected' : '' }} >May</option>
                                                <option value="6" {{ request()->month == 6 ? 'selected' : '' }} >June</option>
                                                <option value="7" {{ request()->month == 7 ? 'selected' : '' }} >July</option>
                                                <option value="8" {{ request()->month == 8 ? 'selected' : '' }} >August</option>
                                                <option value="9" {{ request()->month == 9 ? 'selected' : '' }} >September</option>
                                                <option value="10" {{ request()->month == 10 ? 'selected' : '' }} >October</option>
                                                <option value="11" {{ request()->month == 11 ? 'selected' : '' }} >November</option>
                                                <option value="12" {{ request()->month == 12 ? 'selected' : '' }} >December</option>
                                            </select>
                                            @error('month')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>


                                        <div class="col-md-3 mt-3">
                                            <label class="col-form-label" for="from_date">From Date <span class="text-danger">*</span> </label>
                                            <input class="form-control" name="from_date" id="from_date" type="date" value="{{ request()->from_date }}" placeholder="From Date">
                                            <span class="text-danger error-text from_date_err"></span>
                                        </div>

                                        <div class="col-md-3 mt-3">
                                            <label class="col-form-label" for="to_date">To Date <span class="text-danger">*</span> </label>
                                            <input class="form-control" name="to_date" id="to_date" type="date" value="{{ request()->to_date }}" placeholder="To Date">
                                            <span class="text-danger error-text to_date_err"></span>
                                        </div>

                                        <div class="col-md-3 mt-3">
                                            <label class="col-form-label" for="class">Ward  <span class="text-danger">*</span></label>
                                            <select class="js-example-basic-single col-sm-12  @error('ward') is-invalid  @enderror" name="ward" required>
                                                <option value="">--Select Ward--</option>
                                                @foreach ($wards as $w)
                                                    <option value="{{ $w->id }}" {{ request()->ward == $w->id ? 'selected' : '' }} >{{ $w->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('ward')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        @if ( auth()->user()->hasRole(['Admin', 'Super Admin']) )
                                            <div class="col-md-3 mt-3">
                                                <label class="col-form-label" for="department">Department <span class="text-danger">*</span> </label>
                                                <select class="js-example-basic-single col-sm-12  @error('department') is-invalid  @enderror" name="department">
                                                    <option value="">--Select Department--</option>
                                                    @foreach ($departments as $department)
                                                        <option value="{{ $department->id }}" {{ request()->department == $department->id ? 'selected' : '' }} >{{ $department->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('department')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        @endif

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
                                            <th>Department</th>
                                            <th>Total Late Marks</th>
                                            @foreach ($dateRanges as $dateRange)
                                                <td>{{ $dateRange->format('d') }}</td>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data as $emp)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $emp->emp_code }}</td>
                                                <td>{{ $emp->name }}</td>
                                                <td>{{ $emp->department?->name }}</td>
                                                <td>{{ $emp->punches->count() }}</td>
                                                @foreach ($dateRanges as $dateRange)
                                                    @php
                                                        $hasPunch = $emp->punches->where('punch_date', '>=', $dateRange->toDateString())->where('punch_date', '<', $dateRange->addDay()->toDateString())->first();
                                                    @endphp
                                                    @if($hasPunch)
                                                        <td>{{ Carbon\Carbon::parse($hasPunch->check_in)->format('h:i A') }}</td>
                                                    @else
                                                        <td>-</td>
                                                    @endif
                                                @endforeach
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


{{-- Get month wise date --}}
{{-- <script>
    $(document).ready(function(){

        $("select[name='month']").change(function(e){
            e.preventDefault();
            var month = $("select[name='month']").val();

            if(month != '')
            {
                $.ajax({
                    url: "{{ route('reports.dates') }}",
                    type: 'GET',
                    data: {
                        'month': month,
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
</script> --}}

<!-- Get Ward wise departments -->
{{-- <script>
    $("select[name='ward']").change( function(e) {
        e.preventDefault();

        var model_id = $(this).val();
        var url = "{{ route('wards.departments', ':model_id') }}";

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
                    $("select[name='department']").html(data.departmentHtml);
                } else {
                    swal("Error!", data.error, "error");
                }
            },
            error: function(error, jqXHR, textStatus, errorThrown) {
                swal("Error!", "Some thing went wrong", "error");
            },
        });
    });
</script> --}}
