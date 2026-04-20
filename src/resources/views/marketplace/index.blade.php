@extends('marketplace.layout')

@push('jsonld')
    @include('marketplace.partials.json-ld', ['tenants' => $tenants])
@endpush

@section('content')
<div class="min-h-screen flex flex-col" x-data="marketplaceSearch">

    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-primary via-primary to-primary/70 py-20 lg:py-28">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(255,255,255,0.15),transparent_60%)]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,rgba(0,0,0,0.1),transparent_60%)]"></div>
        <div class="absolute top-0 left-0 w-full h-full opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')"></div>

        <div class="container relative mx-auto px-4 text-center">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/15 backdrop-blur-sm px-4 py-1.5 text-sm font-medium text-primary-foreground/90 mb-6">
                <span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span>
                {{ __('marketplace.onlineBooking') }}
            </div>

            <h1 class="text-4xl font-extrabold text-primary-foreground lg:text-6xl tracking-tight">
                {{ __('marketplace.heroTitle') }}
                <span class="text-accent">{{ __('marketplace.heroHighlight') }}</span>
            </h1>
            <p class="mt-6 text-lg text-primary-foreground/80 max-w-2xl mx-auto leading-relaxed">
                {{ __('marketplace.heroSubtitle') }}
            </p>

            {{-- Search Bar --}}
            <div class="mt-10 max-w-2xl mx-auto relative">
                <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none">
                    <x-lucide-search class="h-5 w-5 text-muted-foreground" />
                </div>
                <input
                    type="text"
                    name="search"
                    x-model="search"
                    placeholder="{{ __('marketplace.searchPlaceholder') }}"
                    class="search-input pl-14"
                    hx-get="{{ route('marketplace.search') }}"
                    hx-trigger="input changed delay:300ms"
                    hx-target="#marketplace-results"
                    hx-include="[name='segment'], [name='country'], [name='city']"
                />
                {{-- Loading indicator --}}
                <div class="absolute inset-y-0 right-5 flex items-center htmx-indicator">
                    <x-lucide-loader-2 class="animate-spin h-5 w-5 text-primary" />
                </div>
            </div>
        </div>
    </section>

    {{-- Filters --}}
    <section class="container mx-auto px-4 py-8">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            {{-- Segment Chips --}}
            <div class="flex gap-2 overflow-x-auto pb-2 sm:pb-0 -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap">
                <button
                    type="button"
                    class="segment-chip shrink-0"
                    :class="{ 'segment-chip-active': segmentId === '' }"
                    @click="selectSegment('')"
                >
                    {{ __('marketplace.all') }}
                </button>
                @foreach($segments as $segment)
                    <button
                        type="button"
                        class="segment-chip shrink-0"
                        :class="{ 'segment-chip-active': segmentId === '{{ $segment->value }}' }"
                        @click="selectSegment('{{ $segment->value }}')"
                    >
                        {{ $segment->label() }}
                    </button>
                @endforeach
            </div>

            <div class="flex gap-2 shrink-0">
                {{-- Country Filter --}}
                @if($countries->isNotEmpty())
                    <div class="relative">
                        <x-lucide-globe class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
                        <select
                            name="country"
                            x-model="country"
                            @change="selectCountry($event.target.value)"
                            class="rounded-xl border border-border bg-card pl-9 pr-4 py-2.5 text-sm font-medium text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none"
                        >
                            <option value="">{{ __('marketplace.allCountries') }}</option>
                            @foreach($countries as $countryOption)
                                <option value="{{ $countryOption }}">{{ $countryOption }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- City Filter --}}
                <div class="relative">
                    <x-lucide-map-pin class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
                    <select
                        id="city-select"
                        name="city"
                        x-model="city"
                        @change="selectCity($event.target.value)"
                        class="rounded-xl border border-border bg-card pl-9 pr-4 py-2.5 text-sm font-medium text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none"
                    >
                        @include('marketplace.partials.city-options', ['cities' => $cities])
                    </select>
                </div>
            </div>
        </div>

        {{-- Hidden inputs for HTMX includes --}}
        <input type="hidden" name="segment" :value="segmentId" />

        {{-- HTMX trigger: reload cities when country changes --}}
        <div
            id="cities-trigger"
            hx-get="{{ route('marketplace.cities') }}"
            hx-trigger="countryChanged"
            hx-target="#city-select"
            hx-include="[name='country']"
        ></div>

        {{-- HTMX trigger: search tenants --}}
        <div
            id="search-trigger"
            hx-get="{{ route('marketplace.search') }}"
            hx-trigger="filterChanged"
            hx-target="#marketplace-results"
            hx-include="[name='search'], [name='segment'], [name='country'], [name='city']"
            hx-indicator=".htmx-indicator"
        ></div>
    </section>

    {{-- Results --}}
    <section class="container mx-auto px-4 pb-20">
        {{-- Results count --}}
        <div class="mb-6 flex items-center justify-between">
            <p class="text-sm text-muted-foreground">
                {{ trans_choice('marketplace.companyFound', $tenants->total(), ['count' => $tenants->total()]) }}
            </p>
        </div>

        {{-- Tenant Grid --}}
        <div id="marketplace-results">
            @include('marketplace.partials.tenant-grid', ['tenants' => $tenants])
        </div>
    </section>
</div>
@endsection
