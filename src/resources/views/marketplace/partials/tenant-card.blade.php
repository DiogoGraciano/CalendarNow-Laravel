@php
    $tenantDomain = $tenant->domains->first()?->domain;
    $scheme = request()->getScheme();
    $tenantUrl = $tenantDomain ? $scheme . '://' . $tenantDomain : '#';
    $bookingUrl = $tenantDomain ? $scheme . '://' . $tenantDomain . '/agendar' : '#';
    $delay = ($loop->index ?? 0) * 0.06;
@endphp

<article class="group rounded-2xl border border-border bg-card overflow-hidden shadow-sm transition-all duration-300 hover:shadow-xl hover:-translate-y-2 card-animate"
         style="animation-delay: {{ $delay }}s">
    {{-- Header with logo/avatar --}}
    <a href="{{ $tenantUrl }}" class="block">
        <div class="relative h-36 bg-gradient-to-br from-primary/10 via-primary/5 to-accent/10 overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(255,255,255,0.3),transparent_60%)]"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                @if($tenant->logo)
                    <img src="{{ $tenant->logo }}" alt="{{ $tenant->name }}" class="h-20 w-20 rounded-2xl object-cover shadow-lg ring-4 ring-white/50 transition-transform duration-300 group-hover:scale-110" />
                @else
                    <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-primary to-primary/70 flex items-center justify-center text-3xl font-bold text-primary-foreground shadow-lg ring-4 ring-white/50 transition-transform duration-300 group-hover:scale-110">
                        {{ strtoupper(mb_substr($tenant->name, 0, 1)) }}
                    </div>
                @endif
            </div>
            @if($tenant->segment)
                <div class="absolute top-3 right-3">
                    <span class="inline-flex items-center gap-1 rounded-full bg-white/90 backdrop-blur-sm px-3 py-1 text-xs font-semibold text-foreground shadow-sm">
                        <x-lucide-tag class="h-3 w-3" />
                        {{ $tenant->segment->label() }}
                    </span>
                </div>
            @endif
        </div>
    </a>

    {{-- Content --}}
    <div class="p-5">
        <a href="{{ $tenantUrl }}" class="block">
            <h3 class="text-lg font-bold text-foreground truncate group-hover:text-primary transition-colors">
                {{ $tenant->name }}
            </h3>
        </a>

        {{-- Location --}}
        @if($tenant->city || $tenant->state)
            <div class="mt-2 flex items-center gap-1.5 text-sm text-muted-foreground">
                <x-lucide-map-pin class="h-4 w-4 shrink-0" />
                <span class="truncate">{{ implode(', ', array_filter([$tenant->city, $tenant->state])) }}</span>
            </div>
        @endif

        {{-- Stats --}}
        <div class="mt-4 flex items-center gap-4">
            <div class="flex items-center gap-1.5 text-sm text-muted-foreground">
                <x-lucide-briefcase class="h-4 w-4 text-primary/60" />
                <span>{{ trans_choice('marketplace.service', $tenant->services_count ?? 0, ['count' => $tenant->services_count ?? 0]) }}</span>
            </div>
        </div>

        {{-- CTA --}}
        <a href="{{ $bookingUrl }}" class="mt-5 flex items-center justify-center gap-2 w-full rounded-xl bg-accent px-4 py-3 text-sm font-semibold text-accent-foreground shadow-md shadow-accent/20 transition-all hover:shadow-lg hover:shadow-accent/30 hover:scale-[1.02]">
            <x-lucide-calendar-plus class="h-4 w-4" />
            {{ __('marketplace.book') }}
        </a>
    </div>
</article>
