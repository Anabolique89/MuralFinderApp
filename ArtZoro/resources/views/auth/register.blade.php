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


        <img src="{{ asset('img/graphics/bubbles.png') }}" class="graphics" alt="bubbles">
        <div class="onboarding-page-login">
            <div class="logo-container"><a href="homepage.php"><img src="{{ asset('img/LOGOWhite.png') }}"
                        alt="logo white"></a>
            </div>
            <p>Create An Account with us</p>
            <div class="form-wrapper">

                <form action="{{ route('register') }}" method="post" class="signup-form">

                    @csrf
                    <div class="input-wrapper">
                        <input type="text" name="username" placeholder="Username" class="input-text">
                        @error('username')

                            <span class="invalid-feedback"
                                style="display: block; width: 100%; margin-top: .25rem; font-size: 80%; color: #dc3545;">
                                <strong>
                                    @error('username')@enderror
                                </strong>
                            </span>
                        @enderror
                    </div>

                    <div class="input-wrapper">
                        <input type="email" name="email" placeholder="E-mail" class="input-text">
                        @error('email')

                            <span class="invalid-feedback"
                                style="display: block; width: 100%; margin-top: .25rem; font-size: 80%; color: #dc3545;">
                                <strong>
                                    @error('email')@enderror
                                </strong>
                            </span>
                        @enderror
                    </div>

                    <div class="input-wrapper">
                        <input type="password" name="password" placeholder="Password" class="input-text">
                        @error('password')
                            <span class="invalid-feedback"
                                style="display: block; width: 100%; margin-top: .25rem; font-size: 80%; color: #dc3545;">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror

                    </div>
                    <div class="input-wrapper">
                        <input type="password" name="password_confirmation" placeholder="Repeat Password"
                            class="input-text">
                        @error('password_confirmation')
                            <span class="invalid-feedback"
                                style="display: block; width: 100%; margin-top: .25rem; font-size: 80%; color: #dc3545;">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="input-wrapper">
                        <select name="role" id="role" class="select" required>
                            <option value="{{ \App\Enums\UserRole::ART_LOVER }}">Art Lover</option>
                            <option value="{{ \App\Enums\UserRole::ARTIST }}">Artist</option>
                        </select>
                    </div>

                    <br>
                    <button type="submit" name="submit" class="submit-button btn">CREATE ACCOUNT</button>
                </form>
                <p class="bottom-p-text">Already have an account yet?<a class="link" href="{{ route('login') }}">
                        Login
                        here!</a>

                </p>
            </div>

        </div>
    </main>
</body>

</html>
