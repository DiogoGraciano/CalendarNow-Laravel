@if($tenants->isEmpty())
    <div class="col-span-full flex flex-col items-center justify-center py-20 text-center">
        <div class="w-16 h-16 rounded-2xl bg-muted flex items-center justify-center mb-4">
            <x-lucide-search-x class="w-8 h-8 text-muted-foreground" />
        </div>
        <h3 class="text-lg font-semibold text-foreground">{{ __('marketplace.noCompaniesFound') }}</h3>
        <p class="mt-1 text-sm text-muted-foreground max-w-sm">{{ __('marketplace.noCompaniesFoundHint') }}</p>
    </div>
@else
    <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($tenants as $tenant)
            @include('marketplace.partials.tenant-card', ['tenant' => $tenant])
        @endforeach
    </div>

    @if($tenants->hasPages())
        <div class="mt-10 flex justify-center" hx-boost="true" hx-target="#marketplace-results" hx-swap="innerHTML" hx-select="#marketplace-results > *">
            {{ $tenants->appends(request()->query())->links() }}
        </div>
    @endif
@endif
