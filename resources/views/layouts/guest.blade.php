<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
    @include('layouts.head')
        
    <body class="">
        @yield('container')

        @include('layouts.script')
    </body>

    
</html>
