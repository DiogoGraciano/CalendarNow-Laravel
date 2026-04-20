<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {!! seo($SEOData ?? null) !!}
    @if(tenant()?->favicon)
        <link rel="icon" href="{{ asset(tenant()->favicon) }}">
    @endif
    @vite(['resources/css/public.css', 'resources/js/public.ts'])
    @php
        $t = tenant();
        $primaryColor = $t?->primary_color;
        $secondaryColor = $t?->secondary_color;
    @endphp
    @if($primaryColor || $secondaryColor)
    <style>
        :root {
            @if($primaryColor)
            --primary: {{ $primaryColor }};
            --ring: {{ $primaryColor }};
            @endif
            @if($secondaryColor)
            --foreground: {{ $secondaryColor }};
            @endif
        }
    </style>
    @endif
</head>
<body class="min-h-screen font-sans antialiased bg-background text-foreground">
    @yield('content')
</body>
</html>
