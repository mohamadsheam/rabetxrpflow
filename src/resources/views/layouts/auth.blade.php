<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Authentication') - RabetXRPFlow</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="auth-card">
                    <div class="auth-logo">
                        <h1>RabetXRPFlow</h1>
                        <p>@yield('page-subtitle', 'Enter your credentials')</p>
                    </div>

                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>