<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {!! seo($SEOData ?? null) !!}
    @if(tenant()?->favicon)
        <link rel="icon" href="{{ asset(tenant()->favicon) }}">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    @themeAssets
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
<body class="min-h-screen antialiased bg-background text-foreground" style="font-family: 'Inter', ui-sans-serif, system-ui, sans-serif">
    @yield('content')
</body>
</html>
