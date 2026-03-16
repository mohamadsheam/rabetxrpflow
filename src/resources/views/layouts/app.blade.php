<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Panel</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    @livewireStyles
</head>

<body>

<div class="d-flex">

    @include('components.sidebar')

    <div class="flex-grow-1">

        @include('components.navbar')

        <div class="container-fluid p-4">
            {{ $slot ?? '' }}
            @yield('content')
        </div>

    </div>

</div>

@livewireScripts
</body>
</html>
