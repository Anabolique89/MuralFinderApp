<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Urban Art Worldwide</title>
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
        <img src="{{ asset('img/graphics/map-paint.png') }}" class="graphics" alt="graphic1">

        <div class="onboarding-page2">
            <h1 class="roboto-uppercase-heading">Urban Art Worldwide</h1>
            <p class="welcome-heading-small">And new places on the map.</p>
            <p class="welcome-text">Find your favourite walls and share your work while
                exploring and travelling the world.</p>
            <a href="{{ url('/onboard3') }}" class="btn">Next</a>
        </div>
    </main>
</body>

</html>
