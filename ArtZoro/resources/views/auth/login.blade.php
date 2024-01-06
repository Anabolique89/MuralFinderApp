<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArtZoro-Login</title>
    <link rel="stylesheet" href="{{ asset('css/style2.css') }}">
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
        rel="stylesheet">
</head>

<body>


    <main>
        <img src="{{asset('img/graphics/bubbles.png')}}" class="graphics" alt="bubbles">
        <div class="onboarding-page-login">
            <div class="logo-container"><a href="homepage.php"><img src="{{asset('img/LOGOWhite.png')}}" alt="logo white"></a>
            </div>
            <p>Enter your account</p>
            <div class="form-wrapper">

                <form method="POST" action="{{ route('login') }}" class="signup-form">
                    <div class="input-wrapper">
                        <input type="text" name="email" placeholder="Username" class="input-text"
                            value="{{ old('email') }}" required autocomplete="email" autofocus>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="input-wrapper">
                        <input type="password" name="password" placeholder="Password" class="input-text">
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <br>
                    <button type="submit" name="submit" class="submit-button btn">LOGIN</button>
                    <p class="bottom-p-text">Don't have an account yet? Sign up <a class="link"
                            href={{route('register')}}> here!</a></p>
                    <div class="row mb-3">
                        <div class="col-md-6 offset-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                    {{ old('remember') ? 'checked' : '' }}>

                                <label class="form-check-label" for="remember">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-8 offset-md-4">

                            @if (Route::has('password.request'))
                                <a class="btn btn-link" href="{{ route('password.request') }}">
                                    {{ __('Forgot Your Password?') }}
                                </a>
                            @endif
                        </div>
                    </div>

                </form>
            </div>
        </div>
        </div>
        </div>
        </div>
