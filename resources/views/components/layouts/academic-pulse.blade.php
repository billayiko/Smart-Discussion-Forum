<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ filled($title ?? null) ? $title.' - Academic Pulse Forum' : 'Academic Pulse Forum' }}</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    @include('partials._pulse-styles')
</head>
<body>
    {{ $slot }}
    @include('partials._pulse-scripts')
    @include('partials._quiz-watch')
    @include('partials._warning-popup')
</body>
</html>
