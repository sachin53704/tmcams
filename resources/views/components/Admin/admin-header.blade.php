
<div class="page-main-header">
    <div class="main-header-right row m-0">
        <div class="main-header-left">
            <div class="logo-wrapper"><a href="{{ route('/') }}"><img class="img-fluid" src="{{ asset('assets/images/logo/logo.png') }}" alt="" style="width: 50px"></a> <strong style="font-size: 18px">{{ strtoupper(auth()->user()->tenant_name) }}</strong> </div>
            <div class="dark-logo-wrapper"><a href="{{ route('/') }}"><img class="img-fluid" src="{{ asset('assets/images/logo/dark-logo.png') }}" alt="" style="width: 50px"></a> <strong style="font-size: 18px">{{ strtoupper(auth()->user()->tenant_name) }}</strong> </div>
            <div class="toggle-sidebar"><i class="status_toggle middle" data-feather="align-center" id="sidebar-toggle"></i></div>
        </div>
        <div class="nav-right col pull-right right-menu p-0">
            <ul class="nav-menus">

                <li class="p-0">
                    <div id="clockdate">
                        <div class="clockdate-wrapper">
                            <div id="clock"></div>
                            {{-- <div id="date"></div> --}}
                        </div>
                    </div>
                </li>

                {{-- <li><a class="text-dark" href="#!" onclick="javascript:toggleFullScreen()"><i data-feather="maximize"></i></a></li>

                <li>
                    <div class="mode"><i class="fa-regular fa-moon"></i></div>
                </li> --}}

                <li class="onhover-dropdown">
                    <div class="notification-box">
                        <img class="img-30 rounded-circle" src="{{ auth()->user()->avatar? asset(auth()->user()->avatar): asset('assets/images/dashboard/1.png') }}" alt="">
                        <strong>{{ ucfirst(auth()->user()->name) }}</strong>
                    </div>
                    <ul class="notification-dropdown onhover-show-div">
                        {{-- <li class="noti-primary">
                            <div class="media"><span class="notification-bg bg-light-primary"><i data-feather="activity">
                                    </i></span>
                                <div class="media-body d-flex align-self-center">
                                    <p>Dashboard</p>
                                </div>
                            </div>
                        </li> --}}
                        <li class="noti-danger">
                            <div class="media"><span class="notification-bg bg-light-danger"><i data-feather="log-out">
                                    </i></span>
                                <div class="media-body d-flex align-self-center">
                                    <p>
                                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            {{ __('Logout') }}
                                        </a>
                                    </p>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>

                                </div>
                            </div>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>
        <div class="d-lg-none mobile-toggle pull-right w-auto"><i data-feather="more-horizontal"></i></div>
    </div>
</div>

@push('scripts')
    <script >
        $(document).ready(function(){
            // if ( window.location.pathname == '/dashboard' )
                startTime()
        });
        function startTime() {
            var today = new Date();
            var hr = today.getHours();
            var min = today.getMinutes();
            var sec = today.getSeconds();
            ap = (hr < 12) ? "<span>AM</span>" : "<span>PM</span>";
            hr = (hr == 0) ? 12 : hr;
            hr = (hr > 12) ? hr - 12 : hr;
            //Add a zero in front of numbers<10
            hr = checkTime(hr);
            min = checkTime(min);
            sec = checkTime(sec);
            document.getElementById("clock").innerHTML = hr + ":" + min + ":" + sec + " " + ap;

            // var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            // var days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            // var curWeekDay = days[today.getDay()];
            // var curDay = today.getDate();
            // var curMonth = months[today.getMonth()];
            // var curYear = today.getFullYear();
            // var date = curWeekDay + ", " + curDay + " " + curMonth + " " + curYear;
            // document.getElementById("date").innerHTML = date;

            var time = setTimeout(function() {
                startTime()
            }, 500);
        }

        function checkTime(i) {
            if (i < 10) {
                i = "0" + i;
            }
            return i;
        }
    </script>
@endpush
