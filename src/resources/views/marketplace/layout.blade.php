<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {!! seo($SEOData ?? null) !!}
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/marketplace.css', 'resources/js/marketplace.ts'])
    @stack('jsonld')
</head>
<body class="min-h-screen antialiased bg-background text-foreground" style="font-family: 'Inter', ui-sans-serif, system-ui, sans-serif">
    {{-- Header --}}
    <header class="sticky top-0 z-50 bg-card/80 backdrop-blur-lg border-b border-border/50">
        <div class="container mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('marketplace') }}" class="flex items-center">
                <img src="/logo.webp" alt="CalendarNow" class="h-10 w-auto object-contain" />
            </a>
        </div>
    </header>

    @yield('content')

    {{-- Footer --}}
    <footer class="border-t border-border py-10 mt-auto">
        <div class="container mx-auto px-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3 text-sm text-muted-foreground">
                    <img src="/logo.webp" alt="CalendarNow" class="h-6 w-auto object-contain opacity-60" />
                    &copy; {{ date('Y') }} CalendarNow. {{ __('marketplace.allRightsReserved') }}
                </div>
                <div class="text-sm text-muted-foreground">
                    {{ __('marketplace.onlineBookingPlatform') }}
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
