<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--favicon-->
    <link rel="icon" href="{{ asset('assets/images/logo_intisaridiesel_fc_64x64.png') }}" type="image/png" />
    <!-- loader-->
    {{-- <link href="{{ asset('assets/css/pace.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('assets/js/pace.min.js') }}"></script> --}}
    <!-- Bootstrap CSS -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    {{-- <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet"> --}}
    <link href="{{ asset('assets/css/fonts.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">
    <title>{{ env('APP_TITLE') }}</title>
</head>

<body class="bg-theme bg-theme16">
    <!--wrapper-->
    <div class="wrapper">
        <div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
            <div class="container-fluid">
                <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
                    <div class="col mx-auto">

                        <div class="card">
                            <div class="card-body">
                                <div class="text-center" style="background-color: #fff;padding-top:10px;padding-bottom:10px;">
                                    <img src="{{ asset('assets/images/logo_UID.png') }}" width="300" alt="" />
                                    {{-- <img src="{{ asset('assets/images/logo_ID_trans.png') }}" width="300" alt="" /> --}}
                                </div>
                                <div class="border p-4 rounded">
                                    <div class="text-center"><h3 class="">Log in</h3></div>
                                    <div class="form-body">
                                        <form class="row g-3" action="{{ url('sign-in') }}" method="POST" enctype="application/x-www-form-urlencoded">
                                            @csrf
                                            <div class="col-12">
                                                <label for="inputEmailAddress" class="form-label">Email Address</label>
                                                <input type="text" maxlength="255" class="form-control @error('inputEmailAddress') is-invalid @enderror"
                                                    id="inputEmailAddress" name="inputEmailAddress" placeholder="Email Address">
                                                @error('inputEmailAddress')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-12">
                                                <label for="inputChoosePassword" class="form-label">Enter Password</label>
                                                <div class="input-group" id="show_hide_password">
                                                    <input type="password" class="form-control border-end-0 @error('inputChoosePassword') is-invalid @enderror"
                                                        id="inputChoosePassword" name="inputChoosePassword" placeholder="Enter Password">
                                                    <a href="javascript:;"
                                                        class="input-group-text bg-transparent" style="border: 1px solid #393939;"><i id="eyeIcon" name="eyeIcon" class="bx bx-hide"
                                                            onclick="myFunction('inputChoosePassword','eyeIcon');"></i></a>
                                                    @error('inputChoosePassword')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="rememberChecked" name="rememberChecked" checked>
                                                    <label class="form-check-label" for="rememberChecked">Remember Me</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-grid">
                                                    <button type="submit" class="btn btn-light" style="background-color: #ed1b24;"><i class="bx bxs-lock-open"></i>Sign in</button>
                                                </div>
                                                @if (session('status-error'))
                                                    <div class="alert alert-danger">
                                                        {{ session('status-error') }}
                                                    </div>
                                                @endif
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end row-->
            </div>
        </div>
    </div>
    <!--end wrapper-->
    {{-- <!--start switcher-->
    <div class="switcher-wrapper">
        <div class="switcher-btn"> <i class='bx bx-cog bx-spin'></i>
        </div>
        <div class="switcher-body">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 text-uppercase">Theme Customizer</h5>
                <button type="button" class="btn-close ms-auto close-switcher" aria-label="Close"></button>
            </div>
            <hr />
            <p class="mb-0">Gaussian Texture</p>
            <hr>
            <ul class="switcher">
                <li id="theme1"></li>
                <li id="theme2"></li>
                <li id="theme3"></li>
                <li id="theme4"></li>
                <li id="theme5"></li>
                <li id="theme6"></li>
            </ul>
            <hr>
            <p class="mb-0">Gradient Background</p>
            <hr>
            <ul class="switcher">
                <li id="theme7"></li>
                <li id="theme8"></li>
                <li id="theme9"></li>
                <li id="theme10"></li>
                <li id="theme11"></li>
                <li id="theme12"></li>
                <li id="theme13"></li>
                <li id="theme14"></li>
                <li id="theme15"></li>
                <li id="theme16"></li>
            </ul>
        </div>
    </div>
    <!--end switcher--> --}}


    <!--plugins-->
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>

    <script>
        function myFunction(sid, currentO) {
            var x = document.getElementById(sid);
            var element = document.getElementById(currentO);
            if (x.type === "password") {
                x.type = "text";
                element.classList.remove("bx-hide");
                element.classList.add("bx-show");
            } else {
                x.type = "password";
                element.classList.remove("bx-show");
                element.classList.add("bx-hide");
            }
        }

        $(".switcher-btn").on("click", function() {
            $(".switcher-wrapper").toggleClass("switcher-toggled")
        }), $(".close-switcher").on("click", function() {
            $(".switcher-wrapper").removeClass("switcher-toggled")
        }),
        $('#theme1').click(theme1);
        $('#theme2').click(theme2);
        $('#theme3').click(theme3);
        $('#theme4').click(theme4);
        $('#theme5').click(theme5);
        $('#theme6').click(theme6);
        $('#theme7').click(theme7);
        $('#theme8').click(theme8);
        $('#theme9').click(theme9);
        $('#theme10').click(theme10);
        $('#theme11').click(theme11);
        $('#theme12').click(theme12);
        $('#theme13').click(theme13);
        $('#theme14').click(theme14);
        $('#theme15').click(theme15);
        $('#theme16').click(theme16);

        function theme1() {
            $('body').attr('class', 'bg-theme bg-theme1');
        }

        function theme2() {
            $('body').attr('class', 'bg-theme bg-theme2');
        }

        function theme3() {
            $('body').attr('class', 'bg-theme bg-theme3');
        }

        function theme4() {
            $('body').attr('class', 'bg-theme bg-theme4');
        }

        function theme5() {
            $('body').attr('class', 'bg-theme bg-theme5');
        }

        function theme6() {
            $('body').attr('class', 'bg-theme bg-theme6');
        }

        function theme7() {
            $('body').attr('class', 'bg-theme bg-theme7');
        }

        function theme8() {
            $('body').attr('class', 'bg-theme bg-theme8');
        }

        function theme9() {
            $('body').attr('class', 'bg-theme bg-theme9');
        }

        function theme10() {
            $('body').attr('class', 'bg-theme bg-theme10');
        }

        function theme11() {
            $('body').attr('class', 'bg-theme bg-theme11');
        }

        function theme12() {
            $('body').attr('class', 'bg-theme bg-theme12');
        }

        function theme13() {
            $('body').attr('class', 'bg-theme bg-theme13');
        }

        function theme14() {
            $('body').attr('class', 'bg-theme bg-theme14');
        }

        function theme15() {
            $('body').attr('class', 'bg-theme bg-theme15');
        }

        function theme16() {
            $('body').attr('class', 'bg-theme bg-theme16');
        }
    </script>
</body>

</html>
