@fragment('slots_initial')
<div id="slots-days-list" class="space-y-4">
    @forelse($days as $day)
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
    @empty
        <div class="rounded-2xl border border-border bg-card p-10 text-center shadow-sm">
            <div class="mx-auto w-14 h-14 rounded-full bg-muted flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-muted-foreground/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
            </div>
            <p class="text-sm text-muted-foreground">{{ __('Nenhum horário disponível para os próximos dias.') }}</p>
        </div>
    @endforelse
</div>
<div id="slots-load-more" class="mt-4">
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
@endfragment
