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
    <div class="onboarding-page-login">
        <div class="logo-container"><a href="homepage.php"><img src="{{ asset('img/LOGOWhite.png') }}"
                    alt="logo white"></a>
        </div>
        <p>{{ __('Reset Password') }}</p>

        <div class="form-wrapper">
            <form method="POST" action="{{ route('password.update') }}" class="signup-form">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">


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
                    <input id="password" type="password" class="input-text @error('password') is-invalid @enderror"
                        name="password" required autocomplete="new-password">

                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>


                <div class="input-wrapper">
                    <input id="password-confirm" type="password" class="input-text" name="password_confirmation"
                        required autocomplete="new-password">
                </div>

                <div class="input-wrapper offset-md-4">
                    <button type="submit" class="btn btn-primary">
                        {{ __('Reset Password') }}
                    </button>
                </div>
        </div>
        </form>
    </div>
</body>

</html>
