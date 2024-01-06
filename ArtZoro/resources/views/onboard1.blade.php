<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArtZoro Presentation Website</title>
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
        rel="stylesheet">
</head>

<body>
    <main class="main">
        <img src="./img/graphics/onboarding1.png" class="graphics" alt="graphic1">

        <div class="onboarding-page">
            <h1 class="roboto-uppercase-heading">Welcome to Artzoro</h1>
            <p class="welcome-heading-small">Explore your full <br> creativity</p>
            <p class="welcome-text">Feel confident in having full coverage <br> when travelling and seeking murals. </p>
            <a href="{{url('/onboard2')}}" class="btn">Next</a>
        </div>
    </main>
</body>

</html>
