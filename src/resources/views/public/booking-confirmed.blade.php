@extends('layouts.public')

@section('content')
<div class="min-h-screen flex flex-col">
    <header class="sticky top-0 z-50 border-b border-border bg-card/95 backdrop-blur-sm">
        <div class="container mx-auto px-4 py-4 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                @if($tenant->logo)
                    <img src="{{ asset($tenant->logo) }}" alt="{{ $tenant->name }}" class="h-8 object-contain" />
                @else
                    <span class="text-lg font-semibold text-foreground">{{ $tenant->name }}</span>
                @endif
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-16 flex-1 max-w-lg flex items-center justify-center">
        <div class="w-full rounded-xl border border-border bg-card p-8 text-center">
            <div class="mx-auto mb-6 w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-foreground mb-3">{{ __('Agendamento realizado!') }}</h1>
            <p class="text-muted-foreground mb-8 leading-relaxed">
                {{ __('Seu agendamento foi enviado com sucesso. Em breve entraremos em contato para confirmar.') }}
            </p>

            <a href="{{ url('/') }}" class="inline-flex items-center justify-center rounded-lg bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground hover:opacity-90 transition-opacity">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                {{ __('Voltar ao início') }}
            </a>
        </div>
    </main>

    <footer class="border-t border-border bg-muted/30 py-6 mt-auto">
        <div class="container mx-auto px-4 text-center text-sm text-muted-foreground">
            &copy; {{ date('Y') }} {{ $tenant->name }}
        </div>
    </footer>
</div>
@endsection
