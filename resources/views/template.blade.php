<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="sininfo" />
    <meta name="author" content="Allam Diaz" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sastreria - @yield('title')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

    <!-- Custom CSS -->
    <link href="{{ asset('css/template.css') }}" rel="stylesheet" />

    <!-- Font Awesome -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <!-- CSS adicional de las páginas -->
    @stack('css')
</head>

@auth

    <body class="sb-nav-fixed">
        <x-navegation />
        <div id="layoutSidenav">
            <x-navegation-menu />
            <div id="layoutSidenav_content">
                <main>
                    @yield('content')
                </main>
                <x-footer />
            </div>
        </div>

        <!-- Scripts base -->
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous">
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
        <script src="{{ asset('js/scrips.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
        <script src="{{ asset('js/datatables-simple-demo.js') }}"></script>
        @stack('js')
    </body>
@endauth

@guest
    @include('pages.401')
@endguest

</html>
