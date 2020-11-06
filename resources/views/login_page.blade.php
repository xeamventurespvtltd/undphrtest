<style type="text/css">
    @import url('https://fonts.googleapis.com/css?family=Numans');

    html,body {
        background-image: url("{{config('constants.static.loginBg')}}"); 
        background-size: cover;
        background-repeat: no-repeat;
        height: 100%;
        font-family: 'Numans', sans-serif;
    }

    .container {
        height: 100%;
        align-content: center;
    }

    .card {
        height: 370px;
        margin-top: auto;
        margin-bottom: auto;
        width: 400px;
        background-color: rgba(0,0,0,0.5) !important;
    }

    .social_icon span {
        font-size: 60px;
        margin-left: 10px;
        color: #FFC312;
    }

    .social_icon span:hover {
        color: white;
        cursor: pointer;
    }

    .card-header h3 {
        color: white;
    }

    .social_icon {
        position: absolute;
        right: 20px;
        top: -45px;
    }

    .input-group-prepend span {
        width: 50px;
        background-color: #FFC312;
        color: black;
        border:0 !important;
    }

    input:focus {
        outline: 0 0 0 0  !important;
        box-shadow: 0 0 0 0 !important;
    }

    .remember {
        color: white;
    }

    .remember input {
        width: 20px;
        height: 20px;
        margin-left: 15px;
        margin-right: 5px;
    }

    .login_btn {
        color: black;
        background-color: #FFC312;
        width: 100px;
    }

    .login_btn:hover {
        color: black;
        background-color: white;
    }

    .links {
        color: white;
    }

    .links a {
        margin-left: 4px;
    }

    .blackstrp {
        background-color: black;
        position: fixed;
        bottom: 0px;
        right: 0;
        left: 0;
    }

    .blackstrp h4 {
        color: #169bba !important;
        text-transform: capitalize;
        text-align: center;
        letter-spacing: 5px;
        font-size: 18px;
        margin-bottom: 0px;
    }

    .blackstrp h3 {
        color: white;
        text-align: center;
        font-size: 20px;
        letter-spacing: 3px;
    }

    img.xeamlogo-img {
        width: 120px;
        position: absolute;
        left: 45%;
        top: 47px;
    }

    .xeam-version {
        position: absolute;
        top: 111px;
    }
</style>
<link href="{{asset('public/login_page/bootstrap.min.css')}}" rel="stylesheet" id="bootstrap-css">
<script src="{{asset('public/login_page/jquery.min.js')}}"></script>
<script src="{{asset('public/login_page/bootstrap.min.js')}}"></script>
<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.sitekey') }}"></script>


<!------ Include the above in your HEAD tag ---------->
<!DOCTYPE html>
<html>
<head>
    <title>Login Page</title>
    <link rel="shortcut icon" type="image/png" href="{{asset('public/admin_assets/static_assets/xeam-favicon.png')}}">
   <!--Made with love by Mutiullah Samim -->
   <!--Bootsrap 4 CDN-->
   <!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous"> -->
   <!--Fontawesome CDN-->
   <link rel="stylesheet" href="{{asset('public/login_page/all.css')}}">
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-center h-100">
            <img src="{{config('constants.static.xeamLogo')}}" class="xeamlogo-img">
            <span class="xeam-version">UNDP HR PORTAL</span>
            <div class="card">
                <div class="card-header">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @elseif(session()->has('error_attempt'))
                        <div class="alert alert-info alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            {{ session()->get('error_attempt') }}
                        </div>
                    @endif
                    <h3>Sign In</h3>
                </div>

                <div class="card-body">
                    <form id="login_form" method="POST" action="{{ url('employees/login') }}">
                        {{ csrf_field() }}
                        <div class="input-group form-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <input type="text" name="employee_code" id="employeeCode" class="form-control" placeholder="User Id">
                        </div>
                        <div class="input-group form-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                            </div>
                            <input type="password" name="password" id="password" class="form-control" placeholder="password">
							<input type="hidden" name="recaptcha" id="recaptcha">
                        </div>
						
						

                        <div class="row align-items-center remember">
                            <input type="checkbox" name="remember_me">Remember Me
                        </div>
                        <div class="form-group">
						
                            <input type="submit" value="Login" class="btn float-right login_btn">
                        </div>
                    </form>
					
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-center">
                        <a href='{{url("forgot-password")}}'>Forgot your password?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <div class="blackstrp">
            <h4>for more information please visit us at:</h4>
            <h3>www.xeamventures.com</h3>
        </div>
    </footer>
</body>
</html>
<script>
    $(".alert-dismissible").fadeOut(5000);
</script>
<script>
         grecaptcha.ready(function() {
             grecaptcha.execute('{{ config('services.recaptcha.sitekey') }}', {action: 'homepage'}).then(function(token) {
                if (token) {
					console.log(token);
                  document.getElementById('recaptcha').value = token;
                }
             });
         });
</script>