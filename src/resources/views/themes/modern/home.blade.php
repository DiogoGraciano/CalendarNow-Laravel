@extends('themes.modern.layouts.public')

@section('content')
<div class="min-h-screen flex flex-col">
    {{-- Header --}}
    <header class="sticky top-0 z-50 bg-card/80 backdrop-blur-lg shadow-sm">
        <div class="container mx-auto px-4 py-4 flex items-center justify-between">
            @if($tenant->logo)
                <a href="{{ url('/') }}">
                    <img src="{{ asset($tenant->logo) }}" alt="{{ $tenant->name }}" class="h-12 object-contain" />
                </a>
            @else
                <a href="{{ url('/') }}" class="text-xl font-bold text-foreground tracking-tight">{{ $tenant->name }}</a>
            @endif
            <a href="{{ route('public.booking') }}" class="inline-flex items-center justify-center rounded-xl bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/25 transition-all hover:shadow-xl hover:shadow-primary/30 hover:scale-105">
                {{ __('Agendar') }}
            </a>
        </div>
    </header>

    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-primary via-primary to-primary/70 py-24 lg:py-32">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(255,255,255,0.15),transparent_60%)]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,rgba(0,0,0,0.1),transparent_60%)]"></div>
        <div class="absolute top-0 left-0 w-full h-full opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')"></div>
        <div class="container relative mx-auto px-4 text-center">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/15 backdrop-blur-sm px-4 py-1.5 text-sm font-medium text-primary-foreground/90 mb-6">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                {{ __('Agenda aberta') }}
            </div>
            <h1 class="text-4xl font-extrabold text-primary-foreground lg:text-6xl tracking-tight">
                {{ $tenant->hero_title ?? $tenant->name }}
            </h1>
            @if($tenant->hero_subtitle)
                <p class="mt-6 text-lg text-primary-foreground/80 max-w-2xl mx-auto leading-relaxed">
                    {{ $tenant->hero_subtitle }}
                </p>
            @endif
            <a href="{{ route('public.booking') }}" class="mt-10 inline-flex items-center justify-center rounded-xl bg-background px-10 py-4 text-base font-bold text-foreground shadow-2xl transition-all hover:shadow-3xl hover:scale-105">
                {{ __('Agendar agora') }}
                <svg class="w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </section>

    <main class="flex-1">
        {{-- Services Section --}}
        <section class="container mx-auto px-4 py-20">
            <div class="text-center mb-14">
                <span class="inline-block text-xs font-semibold uppercase tracking-widest text-primary mb-3">{{ __('O que oferecemos') }}</span>
                <h2 class="text-3xl font-bold text-foreground lg:text-4xl">{{ __('Nossos serviços') }}</h2>
                <p class="mt-3 text-muted-foreground max-w-md mx-auto">{{ __('Confira os serviços disponíveis para agendamento') }}</p>
            </div>

            @if($services->isEmpty())
                <p class="text-center text-muted-foreground">{{ __('Nenhum serviço disponível no momento.') }}</p>
            @else
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($services as $service)
                        <article class="group rounded-2xl border border-border bg-card overflow-hidden shadow-sm transition-all duration-300 hover:shadow-xl hover:-translate-y-2">
                            @if($service->getFirstMediaUrl())
                                <div class="aspect-video overflow-hidden">
                                    <img src="{{ $service->getFirstMediaUrl('default', 'preview') ?: $service->getFirstMediaUrl() }}" alt="{{ $service->name }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
                                </div>
                            @endif
                            <div class="p-6">
                                <h3 class="text-lg font-bold text-foreground">{{ $service->name }}</h3>
                                @if($service->description)
                                    <p class="mt-2 text-sm text-muted-foreground line-clamp-2">{{ $service->description }}</p>
                                @endif
                                <div class="mt-5 flex items-center justify-between">
                                    <span class="inline-flex items-center rounded-full bg-primary/10 px-4 py-1.5 text-sm font-bold text-primary">
                                        R$ {{ number_format((float) $service->price, 2, ',', '.') }}
                                    </span>
                                    @if($service->duration)
                                        <span class="inline-flex items-center gap-1.5 text-sm text-muted-foreground">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ $service->duration_minutes }} min
                                        </span>
                                    @endif
                                </div>
                                <a href="{{ route('public.booking') }}" class="mt-5 block w-full rounded-xl bg-primary px-4 py-3 text-center text-sm font-semibold text-primary-foreground shadow-md shadow-primary/20 transition-all hover:shadow-lg hover:shadow-primary/30 hover:scale-[1.02]">
                                    {{ __('Agendar') }}
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($services->hasPages())
                    <div class="mt-10 flex justify-center">
                        {{ $services->links() }}
                    </div>
                @endif
            @endif
        </section>

        {{-- Team Section --}}
        @if(($tenant->show_employees_section ?? true) && $employees->isNotEmpty())
        <section class="bg-muted/40 py-20">
            <div class="container mx-auto px-4">
                <div class="text-center mb-14">
                    <span class="inline-block text-xs font-semibold uppercase tracking-widest text-primary mb-3">{{ __('Profissionais') }}</span>
                    <h2 class="text-3xl font-bold text-foreground lg:text-4xl">{{ __('Nossa equipe') }}</h2>
                    <p class="mt-3 text-muted-foreground max-w-md mx-auto">{{ __('Conheça os profissionais que cuidam de você') }}</p>
                </div>
                <div class="grid gap-6 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 max-w-4xl mx-auto">
                    @foreach($employees as $employee)
                        <div class="flex flex-col items-center rounded-2xl bg-card/80 backdrop-blur-sm border border-border p-6 shadow-sm hover:shadow-md transition-all duration-300">
                            @if($employee['photo_url'])
                                <img src="{{ $employee['photo_url'] }}" alt="{{ $employee['name'] }}" class="h-20 w-20 rounded-full object-cover ring-4 ring-primary/15 shadow-lg" />
                            @else
                                <div class="h-20 w-20 rounded-full bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center text-2xl font-bold text-primary shadow-inner">
                                    {{ strtoupper(substr($employee['name'], 0, 1)) }}
                                </div>
                            @endif
                            <span class="mt-4 text-sm font-semibold text-foreground text-center">{{ $employee['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        {{-- Contact Info --}}
        @if($tenant->address || $tenant->phone || $tenant->email)
        <section class="container mx-auto px-4 py-20">
            <div class="text-center mb-14">
                <span class="inline-block text-xs font-semibold uppercase tracking-widest text-primary mb-3">{{ __('Fale conosco') }}</span>
                <h2 class="text-3xl font-bold text-foreground lg:text-4xl">{{ __('Contato') }}</h2>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 max-w-3xl mx-auto">
                @if($tenant->address)
                <div class="rounded-2xl border border-border bg-card p-6 text-center shadow-sm hover:shadow-md transition-shadow">
                    <div class="mx-auto w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center mb-4">
                        <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <p class="text-sm font-medium text-foreground">{{ __('Endereço') }}</p>
                    <p class="mt-1 text-sm text-muted-foreground">{{ $tenant->address }}@if($tenant->city), {{ $tenant->city }}@endif @if($tenant->state) - {{ $tenant->state }}@endif</p>
                </div>
                @endif
                @if($tenant->phone)
                <div class="rounded-2xl border border-border bg-card p-6 text-center shadow-sm hover:shadow-md transition-shadow">
                    <div class="mx-auto w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center mb-4">
                        <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <p class="text-sm font-medium text-foreground">{{ __('Telefone') }}</p>
                    <p class="mt-1 text-sm text-muted-foreground">{{ $tenant->phone }}</p>
                </div>
                @endif
                @if($tenant->email)
                <div class="rounded-2xl border border-border bg-card p-6 text-center shadow-sm hover:shadow-md transition-shadow">
                    <div class="mx-auto w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center mb-4">
                        <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <p class="text-sm font-medium text-foreground">{{ __('E-mail') }}</p>
                    <p class="mt-1 text-sm text-muted-foreground">{{ $tenant->email }}</p>
                </div>
                @endif
            </div>
        </section>
        @endif
    </main>

    {{-- Footer --}}
    <footer class="border-t border-border py-8 mt-auto">
        <div class="container mx-auto px-4 text-center text-sm text-muted-foreground">
            &copy; {{ date('Y') }} {{ $tenant->name }}. {{ __('Todos os direitos reservados.') }}
        </div>
    </footer>
</div>
@endsection
