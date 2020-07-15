<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hands of Cards</title>
    @yield('meta')

    @stack('before-styles')
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @stack('after-styles')

</head>
<body>
<div id="app">
    <main class="container py-4">
        @yield('content')
    </main>
</div><!--app-->

@stack('before-scripts')
<script src="{{ mix('js/app.js') }}"></script>
@stack('after-scripts')
</body>
</html>
