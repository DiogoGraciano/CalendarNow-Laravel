@extends('layouts.public')

@section('content')
<div class="min-h-screen flex flex-col">
    {{-- Header --}}
    <header class="sticky top-0 z-50 border-b border-border bg-card/95 backdrop-blur supports-[backdrop-filter]:bg-card/60">
        <div class="container mx-auto px-4 py-4 flex items-center justify-between">
            @if($tenant->logo)
                <a href="{{ url('/') }}">
                    <img src="{{ asset($tenant->logo) }}" alt="{{ $tenant->name }}" class="h-10 object-contain" />
                </a>
            @else
                <a href="{{ url('/') }}" class="text-xl font-bold text-foreground">{{ $tenant->name }}</a>
            @endif
            <a href="{{ route('public.booking') }}" class="inline-flex items-center justify-center rounded-lg bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-all hover:shadow-md hover:brightness-110">
                {{ __('Agendar') }}
            </a>
        </div>
    </header>

    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-primary py-20 lg:py-28">
        <div class="absolute inset-0 bg-gradient-to-br from-primary via-primary to-primary/80"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(255,255,255,0.12),transparent_70%)]"></div>
        <div class="container relative mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold text-primary-foreground lg:text-5xl">
                {{ $tenant->hero_title ?? $tenant->name }}
            </h1>
            @if($tenant->hero_subtitle)
                <p class="mt-4 text-lg text-primary-foreground/80 max-w-2xl mx-auto">
                    {{ $tenant->hero_subtitle }}
                </p>
            @endif
            <a href="{{ route('public.booking') }}" class="mt-8 inline-flex items-center justify-center rounded-lg bg-background px-8 py-3.5 text-base font-semibold text-foreground shadow-lg transition-all hover:shadow-xl hover:scale-105">
                {{ __('Agendar agora') }}
            </a>
        </div>
    </section>

    <main class="flex-1">
        {{-- Services Section --}}
        <section class="container mx-auto px-4 py-16">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-foreground">{{ __('Nossos serviços') }}</h2>
                <p class="mt-2 text-muted-foreground">{{ __('Confira os serviços disponíveis para agendamento') }}</p>
            </div>

            @if($services->isEmpty())
                <p class="text-center text-muted-foreground">{{ __('Nenhum serviço disponível no momento.') }}</p>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($services as $service)
                        <article class="group rounded-xl border border-border bg-card overflow-hidden shadow-sm transition-all hover:shadow-lg hover:-translate-y-1">
                            @if($service->getFirstMediaUrl())
                                <div class="aspect-video overflow-hidden">
                                    <img src="{{ $service->getFirstMediaUrl('default', 'preview') ?: $service->getFirstMediaUrl() }}" alt="{{ $service->name }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" />
                                </div>
                            @endif
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-foreground">{{ $service->name }}</h3>
                                @if($service->description)
                                    <p class="mt-2 text-sm text-muted-foreground line-clamp-2">{{ $service->description }}</p>
                                @endif
                                <div class="mt-4 flex items-center justify-between">
                                    <span class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-sm font-semibold text-primary">
                                        R$ {{ number_format((float) $service->price, 2, ',', '.') }}
                                    </span>
                                    @if($service->duration)
                                        <span class="inline-flex items-center gap-1 text-sm text-muted-foreground">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ $service->duration_minutes }} min
                                        </span>
                                    @endif
                                </div>
                                <a href="{{ route('public.booking') }}" class="mt-4 block w-full rounded-lg bg-primary px-4 py-2.5 text-center text-sm font-semibold text-primary-foreground transition-all hover:brightness-110">
                                    {{ __('Agendar') }}
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($services->hasPages())
                    <div class="mt-8 flex justify-center">
                        {{ $services->links() }}
                    </div>
                @endif
            @endif
        </section>

        {{-- Team Section --}}
        @if(($tenant->show_employees_section ?? true) && $employees->isNotEmpty())
        <section class="bg-muted/30 py-16">
            <div class="container mx-auto px-4">
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-bold text-foreground">{{ __('Nossa equipe') }}</h2>
                    <p class="mt-2 text-muted-foreground">{{ __('Conheça os profissionais que cuidam de você') }}</p>
                </div>
                <div class="flex gap-6 overflow-x-auto pb-4 snap-x snap-mandatory lg:justify-center lg:flex-wrap">
                    @foreach($employees as $employee)
                        <div class="snap-center shrink-0 w-48">
                            <div class="flex flex-col items-center rounded-xl bg-card border border-border p-6 shadow-sm">
                                @if($employee['photo_url'])
                                    <img src="{{ $employee['photo_url'] }}" alt="{{ $employee['name'] }}" class="h-24 w-24 rounded-full object-cover ring-4 ring-primary/20" />
                                @else
                                    <div class="h-24 w-24 rounded-full bg-muted flex items-center justify-center text-2xl font-bold text-muted-foreground">
                                        {{ strtoupper(substr($employee['name'], 0, 1)) }}
                                    </div>
                                @endif
                                <span class="mt-4 text-sm font-medium text-foreground text-center">{{ $employee['name'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        {{-- Contact Info --}}
        @if($tenant->address || $tenant->phone || $tenant->email)
        <section class="container mx-auto px-4 py-16">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-foreground">{{ __('Contato') }}</h2>
            </div>
            <div class="flex flex-wrap justify-center gap-8">
                @if($tenant->address)
                <div class="flex items-start gap-3 text-muted-foreground">
                    <svg class="h-5 w-5 mt-0.5 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>{{ $tenant->address }}@if($tenant->city), {{ $tenant->city }}@endif @if($tenant->state) - {{ $tenant->state }}@endif</span>
                </div>
                @endif
                @if($tenant->phone)
                <div class="flex items-center gap-3 text-muted-foreground">
                    <svg class="h-5 w-5 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span>{{ $tenant->phone }}</span>
                </div>
                @endif
                @if($tenant->email)
                <div class="flex items-center gap-3 text-muted-foreground">
                    <svg class="h-5 w-5 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>{{ $tenant->email }}</span>
                </div>
                @endif
            </div>
        </section>
        @endif
    </main>

    {{-- Footer --}}
    <footer class="border-t border-border bg-card py-8 mt-auto">
        <div class="container mx-auto px-4 text-center text-sm text-muted-foreground">
            &copy; {{ date('Y') }} {{ $tenant->name }}. {{ __('Todos os direitos reservados.') }}
        </div>
    </footer>
</div>
@endsection
