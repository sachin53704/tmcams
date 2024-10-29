<x-admin.admin-layout>
    <x-slot name="title">TMC - Dashboard</x-slot>

    <div class="page-body">
        <!-- Container-fluid starts-->
        <div class="container-fluid dashboard-default-sec">

            <div class="row">
                <div class="col-12 px-0">
                    <div class="row">
                        <div class="col-sm-6 col-xl-3 col-lg-6">
                            <div class="card o-hidden border-0">
                                <div class="bg-blue b-r-4 card-body">
                                    <div class="media static-top-widget">
                                        <div class="media-body"><span class="m-0">Total Employees</span>
                                            <h4 class="mb-0 counter"> {{ $totalEmployees }} </h4><i class="icon-bg" data-feather="user"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3 col-lg-6">
                            <div class="card o-hidden border-0">
                                <div class="bg-success b-r-4 card-body">
                                    <div class="media static-top-widget">
                                        <div class="media-body"><span class="m-0">Total Department</span>
                                            <h4 class="mb-0 counter"> {{ $totalDepartments }} </h4><i class="icon-bg" data-feather="book"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3 col-lg-6">
                            <div class="card o-hidden border-0">
                                <div class="bg-warning b-r-4 card-body">
                                    <div class="media static-top-widget">
                                        <div class="media-body"><span class="m-0">Total Holidays</span>
                                            <h4 class="mb-0 counter"> {{ $totalHolidays }} </h4><i class="icon-bg" data-feather="home"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3 col-lg-6">
                            <div class="card o-hidden border-0">
                                <div class="bg-danger b-r-4 card-body">
                                    <div class="media static-top-widget">
                                        <div class="media-body"><span class="m-0">Total Office</span>
                                            <h4 class="mb-0 counter"> {{ $totalWards->count() }} </h4><i class="icon-bg" data-feather="briefcase"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-12  px-0">
                    <div class="row">
                        <div class="col-9">
                            <div class="row">
                                {{-- Todays present --}}
                                <div class="col-md-6 col-lg-6 col-xl-6 box-col-6">
                                    <div class="card custom-card rounded">
                                        <h6 class="card-header rounded bg-primary py-2 px-3 text-center">Today's Present</h6>
                                        <div class="card-body px-3">
                                            <div class="row">
                                                <div class="col-6">
                                                    @php
                                                        $todaysPresentCount = $todayPunchData->where('check_in', '!=', '0000-00-00 00:00:00')->count();
                                                        $todaysPresentPercent = $totalEmployees ? round(($todaysPresentCount/$totalEmployees)*100) : '0';
                                                        $todaysAbsentCount = $totalEmployees-$todaysPresentCount;
                                                    @endphp
                                                    <label for="">{{ $todaysPresentPercent }}%</label>
                                                    <div class="progress">
                                                        <div class="progress-bar-animated bg-primary progress-bar-striped" role="progressbar" style="width: {{$todaysPresentPercent}}%" aria-valuenow="{{$todaysPresentPercent}}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <strong style="font-size:22px">{{$todaysPresentCount}} </strong>({{ $todaysPresentPercent }}%) <br>
                                                    <strong>Present Count</strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer row">
                                            @php
                                                $till10AMCount = $todayPunchData->countBy( fn($item) => Carbon\Carbon::parse($item->check_in)->gte($todaysDate.' 10:00:00') );
                                                $after10AMCount = array_key_exists('1', $till10AMCount->toArray()) ? $till10AMCount['1'] : 0;
                                                $till10AMCount = array_key_exists('0', $till10AMCount->toArray()) ? $till10AMCount['0'] : 0;
                                            @endphp
                                            <div class="col-6 col-sm-6">
                                                <h6>Till 10AM</h6>
                                                <h3><span class="counter" style="font-size:22px">{{ $till10AMCount }}</span><span style="font-size:14px">({{ $totalEmployees ? round(($till10AMCount/$totalEmployees)*100) : '0' }}%)</span></h3>
                                            </div>
                                            <div class="col-6 col-sm-6">
                                                <h6>After 10AM</h6>
                                                <h3><span class="counter" style="font-size:22px">{{ $after10AMCount }}</span><span style="font-size:14px">({{ $totalEmployees ? round(($after10AMCount/$totalEmployees)*100) : '0' }}%)</span></h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Todays absent --}}
                                <div class="col-md-6 col-lg-6 col-xl-6 box-col-6">
                                    <div class="card custom-card rounded">
                                        <h6 class="card-header rounded bg-primary py-2 px-3 text-center">Today's Absent</h6>
                                        <div class="card-body px-3">
                                            <div class="row">
                                                <div class="col-6">
                                                    <label for="">{{ $totalEmployees ? round(($todaysAbsentCount/$totalEmployees)*100) : '0' }}%</label>
                                                    <div class="progress">
                                                        <div class="progress-bar-animated bg-primary progress-bar-striped" role="progressbar" style="width: {{ $totalEmployees ? round(($todaysAbsentCount/$totalEmployees)*100) : '0' }}%" aria-valuenow="{{ $totalEmployees ? round(($todaysAbsentCount/$totalEmployees)*100) : '0' }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <strong style="font-size:22px">{{ max($todaysAbsentCount, 0) }} </strong>({{ $totalEmployees ? round(($todaysAbsentCount/$totalEmployees)*100) : '0' }}%) <br>
                                                    <strong>Absent Count</strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer row">
                                            @php
                                                $permittedLeaveCount = $todayPunchData->where('check_in', '0000-00-00 00:00:00')->where('punch_by', '2')->count();
                                            @endphp
                                            <div class="col-6 col-sm-6">
                                                <h6>Permitted Leave</h6>
                                                <h3><span class="counter" style="font-size:22px">{{ $permittedLeaveCount }}</span><span style="font-size:14px">({{ $totalEmployees ? round( ($permittedLeaveCount/$totalEmployees)*100) : '0' }}%)</span></h3>
                                            </div>
                                            <div class="col-6 col-sm-6">
                                                <h6>Non Permitted Leave</h6>
                                                <h3><span class="counter" style="font-size:22px">{{ max($todaysAbsentCount-$permittedLeaveCount, 0) }}</span><span style="font-size:14px">({{ $totalEmployees ? round((($todaysAbsentCount-$permittedLeaveCount)/$totalEmployees)*100) : '0' }}%)</span></h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Leave bifurcation --}}
                                <div class="col-md-12 col-lg-12 col-xl-12 box-col-12">
                                    <div class="card custom-card rounded">
                                        <h6 class="card-header rounded bg-primary py-2 px-3 text-center">Leave Bifurcation</h6>
                                        <div class="card-footer row">
                                            <div class="col-2 col-sm-2">
                                                <h6>CL</h6>
                                                <h3 class="counter">{{ $todayPunchData->where('leave_type_id', '6')->count() }}</h3>
                                            </div>
                                            <div class="col-2 col-sm-2">
                                                <h6>EL</h6>
                                                <h3><span class="counter">{{ $todayPunchData->where('leave_type_id', '5')->count() }}</span></h3>
                                            </div>
                                            <div class="col-2 col-sm-2">
                                                <h6>ML</h6>
                                                <h3><span class="counter">{{ $todayPunchData->where('leave_type_id', '7')->count() }}</span></h3>
                                            </div>
                                            <div class="col-3 col-sm-3">
                                                <h6>Other Leave</h6>
                                                <h3><span class="counter">{{ $todayPunchData->where('leave_type_id', '4')->count() }}</span></h3>
                                            </div>
                                            <div class="col-3 col-sm-3">
                                                <h6>Half Day</h6>
                                                <h3><span class="counter">{{ $todayPunchData->where('leave_type_id', '0')->count() }}</span></h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Repeatedly latemark / absent --}}
                            <div class="row">
                                @php
                                    $repeatedlyLateMark = $punchData->groupBy('emp_code')->countBy( fn($item) => $item->where('is_latemark', '>', '0')->count() > 1 );
                                    $repeatedlyLateMark = array_key_exists('1', $repeatedlyLateMark->toArray()) ? $repeatedlyLateMark['1'] : 0;
                                @endphp

                                <div class="col-md-6 col-lg-6 col-xl-6 box-col-6">
                                    <div class="card custom-card rounded">
                                        <h6 class="card-header rounded bg-primary py-2 px-3 text-center">Repeatedly Late Mark</h6>
                                        <div class="card-body px-3">
                                            <div class="row">
                                                <div class="col-6">
                                                    <label for="">{{ $totalEmployees ? round(($repeatedlyLateMark/$totalEmployees)*100) : '0' }}%</label>
                                                    <div class="progress">
                                                        <div class="progress-bar-animated bg-primary progress-bar-striped" role="progressbar" style="width: {{ $totalEmployees ? round(($repeatedlyLateMark/$totalEmployees)*100) : '0' }}%" aria-valuenow="{{ $totalEmployees ? round(($repeatedlyLateMark/$totalEmployees)*100) : '0' }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <strong style="font-size:22px">{{ $repeatedlyLateMark }} </strong>({{ $totalEmployees ? round(($repeatedlyLateMark/$totalEmployees)*100) : '0' }}%) <br>
                                                    <strong>Repeatedly Late Mark</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @php
                                    $regularEmployeeCount = $punchData->groupBy('emp_code')->count();
                                    $repeatedlyAbsent = $totalEmployees-$regularEmployeeCount;
                                @endphp

                                <div class="col-md-6 col-lg-6 col-xl-6 box-col-6">
                                    <div class="card custom-card rounded">
                                        <h6 class="card-header rounded bg-primary py-2 px-3  text-center">Repeatedly Absent</h6>
                                        <div class="card-body px-3">
                                            <div class="row">
                                                <div class="col-6">
                                                    <label for="">{{ $totalEmployees ? round(($repeatedlyAbsent/$totalEmployees)*100) : '0' }}%</label>
                                                    <div class="progress">
                                                        <div class="progress-bar-animated bg-primary progress-bar-striped" role="progressbar" style="width: {{ $totalEmployees ? round(($repeatedlyAbsent/$totalEmployees)*100) : '0' }}%" aria-valuenow="{{ $totalEmployees ? round(($repeatedlyAbsent/$totalEmployees)*100) : '0' }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <strong style="font-size:22px">{{ max($repeatedlyAbsent, 0) }} </strong>({{ $totalEmployees ? round(($repeatedlyAbsent/$totalEmployees)*100) : '0' }}%) <br>
                                                    <strong>Repeatedly Absent</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>


                        {{-- Side recent 5 attendance list --}}
                        <div class="col-3">

                            <h6 class="mb-4">Today's Latest 6 Records</h6>
                            @php
                                $latestFives = $todayPunchData->take(6);
                            @endphp
                            @foreach ($latestFives as $latest)
                                <div class="col-12 card rounded latest-update-sec mb-2">
                                    <div class="media py-2">
                                        <div class="col-12">
                                            <div class="media-body">
                                                <span>{{ Str::limit(ucwords($latest->user->name), 25) }}</span> <br>
                                                <span>#{{ $latest->emp_code }}</span> &nbsp;&nbsp; <span class="text-danger"> {{  Carbon\Carbon::parse($latest->check_in)->format('d-m-Y h:i A') }} </span>
                                                <p style="font-size: 10px">{{ Str::limit(ucfirst($latest->user->department->name), 25) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="col-12">
                                <a href="" class="btn btn-primary w-100 py-1">View more </a>
                            </div>

                        </div>
                    </div>
                </div>




                {{-- Shift wise details --}}
                <div class="col-12 px-0 mt-4">
                    <div class="row">
                        <div class="card rounded">
                            <div class="card-header px-2 py-3">
                                <h6>Shift Wise Details</h6>
                            </div>
                            <div class="row">
                                @foreach ($shiftWiseData as $shift)
                                @php
                                    // $currentShiftData = $todayPunchData->where('check_in', '>=', $todaysDate.' '.$shift->from_time)->where('check_in', '<=', $todaysDate.' '.$shift->to_time)
                                    $currentShiftData = $todayPunchData->where( fn($item) => $item->user->shift_id == $shift->id )
                                @endphp

                                    <div class="col-md-6 col-lg-6 col-xl-6 box-col-6">
                                        <div class="card custom-card rounded">
                                            <h6 class="card-header rounded bg-primary py-2 px-3 text-center"> {{ ucwords($shift->name) }} Employees</h6>
                                            <div class="card-body p-3">
                                                <div class="row">
                                                    <div class="col-4 br-right text-center">
                                                        <h6 class="mb-0">Total</h6>
                                                        <strong style="font-size:22px">{{ $shift->employees_count }} </strong> <br>
                                                    </div>
                                                    <div class="col-4 br-right  text-center">
                                                        <h6 class="mb-0">Present</h6>
                                                        <strong style="font-size:22px; display:inline-block;">{{ $currentShiftData->count() }} </span><span style="font-size:14px; display:inline-block;">({{ $shift->employees_count ? round(($currentShiftData->count()/$shift->employees_count)*100) : '0' }}%)</strong> <br>
                                                    </div>
                                                    <div class="col-4  text-center">
                                                        <h6 class="mb-0">Absent</h6>
                                                        <strong style="font-size:22px; display:inline-block;">{{ abs( $shift->employees_count-$currentShiftData->count() ) }} </span><span style="font-size:14px; display:inline-block;">({{ $shift->employees_count ? round(((($shift->employees_count-$currentShiftData->count() ))/$shift->employees_count)*100) : '0' }}%)</strong> <br>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer row">
                                                <div class="col-2 col-sm-2">
                                                    <h6 class="font-12">CL</h6>
                                                    <h3 class="font-16"><span class="counter">{{ $currentShiftData->where('leave_type_id', '6')->count() }}</span></h3>
                                                </div>
                                                <div class="col-2 col-sm-2">
                                                    <h6 class="font-12">EL</h6>
                                                    <h3 class="font-16"><span class="counter">{{ $currentShiftData->where('leave_type_id', '5')->count() }}</span></h3>
                                                </div>
                                                <div class="col-2 col-sm-2">
                                                    <h6 class="font-12">ML</h6>
                                                    <h3 class="font-16"><span class="counter">{{ $currentShiftData->where('leave_type_id', '7')->count() }}</span></h3>
                                                </div>
                                                <div class="col-3 col-sm-3">
                                                    <h6 class="font-12">OL</h6>
                                                    <h3 class="font-16"><span class="counter">{{ $currentShiftData->where('leave_type_id', '4')->count() }}</span></h3>
                                                </div>
                                                <div class="col-3 col-sm-3">
                                                    <h6 class="font-12">HDL</h6>
                                                    <h3 class="font-16"><span class="counter">{{ $currentShiftData->where('leave_type_id', '0')->count() }}</span></h3>
                                                </div>
                                            </div>
                                            <div class="card-footer row">
                                                @php
                                                    $beforeTime = $todayPunchData->where('check_in', '>=', Carbon\Carbon::parse($todaysDate.' '.$shift->from_time)->subHour()->toDateTimeString())->where('check_in', '<=', Carbon\Carbon::parse($todaysDate.' '.$shift->from_time)->toDateTimeString() )->count();
                                                    $afterTime = $todayPunchData->where('check_in', '<=', Carbon\Carbon::parse($todaysDate.' '.$shift->from_time)->addHour()->toDateTimeString())->where('check_in', '>=', Carbon\Carbon::parse($todaysDate.' '.$shift->from_time)->toDateTimeString() )->count();
                                                @endphp

                                                <div class="col-6 col-sm-6">
                                                    <h6 class="color-black">Before Time</h6>
                                                    <h3><span class="counter" style="font-size:20px">{{ $beforeTime }}</span><span style="font-size:12px">( {{ $currentShiftData->count() ? round(($beforeTime/$currentShiftData->count())*100) : '0' }}%)</span></h3>
                                                </div>
                                                <div class="col-6 col-sm-6">
                                                    <h6  class="color-black">After Time</h6>
                                                    <h3><span class="counter" style="font-size:20px">{{ ($afterTime) }}</span><span style="font-size:12px">({{ $currentShiftData->count() ? round(($afterTime/$currentShiftData->count())*100) : '0' }}%)</span></h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>



                {{-- Office/Ward wise details --}}
                <div class="col-12 px-0">
                    @if ( $is_admin && !request()->ward )
                        <div class="row">
                            <div class="card rounded">
                                <div class="card-header px-2 py-3">
                                    <h6>Office Wise Details</h6>
                                </div>
                                <div class="row">
                                    @foreach ($totalWards as $totalWard)
                                        @php
                                            $currentWardData = $todayPunchData->where( fn($item) => $item->user->ward_id == $totalWard->id );
                                        @endphp
                                        <div class="col-md-4 col-lg-4 col-xl-4 box-col-4">
                                            <div class="card custom-card rounded">
                                                <h6 class="card-header rounded bg-primary py-2 px-3  text-center"> {{ Str::limit(ucwords($totalWard->name), 25) }} Office</h6>
                                                <div class="card-body px-3">
                                                    <div class="row">
                                                        <div class="col-4 br-right text-center">
                                                            <h6 class="mb-0">Total</h6>
                                                            <strong style="font-size:22px">{{ $totalWard->users_count }} </strong> <br>
                                                        </div>
                                                        <div class="col-4 br-right text-center">
                                                            <h6 class="mb-0">Present</h6>
                                                            <strong style="font-size:22px; display:inline-block;">{{ $currentWardData->count() }} </span><span style="font-size:14px; display:inline-block;">({{ $totalWard->users_count ? round(($currentWardData->count()/$totalWard->users_count)*100) : '0' }}%)</strong> <br>
                                                        </div>
                                                        <div class="col-4 text-center">
                                                            <h6 class="mb-0">Absent</h6>
                                                            <strong style="font-size:22px; display:inline-block;">{{ abs( $totalWard->users_count-$currentWardData->count() ) }} </span><span style="font-size:14px; display:inline-block;">({{ $totalWard->users_count ? round(((($totalWard->users_count-$currentWardData->count() ))/$totalWard->users_count)*100) : '0' }}%)</strong> <br>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-footer row">
                                                    <div class="col-12 col-sm-12">
                                                        <a href="{{ route('dashboard', ['ward'=> $totalWard->id]) }}" class="btn btn-primary color-green-blue font-12">CLICK HERE FOR MORE DETAILS</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>



            </div>

        </div>
        <!-- Container-fluid Ends-->
    </div>

</x-admin.admin-layout>
