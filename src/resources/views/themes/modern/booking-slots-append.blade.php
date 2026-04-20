@foreach($days as $day)
<div class="rounded-2xl border border-border bg-card overflow-hidden shadow-sm">
    <div class="px-5 py-3.5 bg-gradient-to-r from-primary/5 to-transparent border-b border-border">
        <p class="text-sm font-semibold text-foreground tracking-wide">{{ $day['label'] }}</p>
    </div>
    <div class="p-4 flex flex-wrap gap-2">
        @foreach($day['slots'] as $slot)
            <button type="button"
                class="slot-btn rounded-xl border border-border bg-background px-4 py-2.5 text-sm font-medium text-foreground hover:border-primary hover:text-primary hover:shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-ring"
                aria-pressed="false"
                data-start="{{ $slot['start'] }}"
                data-end="{{ $slot['end'] }}"
                data-employee-id="{{ $slot['employee_id'] }}"
            >
                {{ $slot['label'] }}
            </button>
        @endforeach
    </div>
</div>
@endforeach
<div id="slots-load-more" hx-swap-oob="true" class="mt-4">
    @if($next_cursor)
        <button type="button"
            class="w-full rounded-xl border border-border bg-card px-4 py-3.5 text-sm font-medium text-foreground hover:bg-muted hover:shadow-sm transition-all"
            hx-get="{{ route('public.booking.slots') }}"
            hx-include="[name='calendar_id'],[name='service_ids[]'],[name='employee_id']"
            hx-vals='{"cursor": "{{ $next_cursor }}"}'
            hx-target="#slots-days-list"
            hx-swap="beforeend"
        >
            {{ __('Carregar mais dias') }}
        </button>
    @else
        <p class="text-sm text-muted-foreground text-center py-2">{{ __('Não há mais dias disponíveis.') }}</p>
    @endif
</div>
