@extends('themes.default.layouts.public')

@section('content')
<div class="min-h-screen flex flex-col" x-data="bookingWizard({{ Js::from($employees) }}, {{ Js::from($services->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'price' => (float) $s->price, 'duration_minutes' => $s->duration_minutes, 'image_url' => $s->getFirstMediaUrl('default', 'preview') ?: $s->getFirstMediaUrl()])) }})">

    {{-- Header --}}
    <header class="sticky top-0 z-50 border-b border-border bg-card/95 backdrop-blur-sm">
        <div class="container mx-auto px-4 py-4 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                @if($tenant->logo)
                    <img src="{{ asset($tenant->logo) }}" alt="{{ $tenant->name }}" class="h-8 object-contain" />
                @else
                    <span class="text-lg font-semibold text-foreground">{{ $tenant->name }}</span>
                @endif
            </div>
            <a href="{{ url('/') }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                <svg class="inline-block w-4 h-4 mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                {{ __('Voltar') }}
            </a>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8 flex-1 max-w-3xl">

        {{-- Validation errors --}}
        @if($errors->any())
            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 text-red-800 dark:text-red-200 mb-6">
                <ul class="list-disc list-inside space-y-1 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Step Progress Indicator --}}
        <div class="mb-8">
            <div class="flex items-center justify-between">
                @php $stepLabels = [__('Profissional'), __('Serviços'), __('Horário'), __('Dados'), __('Confirmação')]; @endphp
                @for($i = 1; $i <= 5; $i++)
                    <div class="flex flex-col items-center flex-1" :class="{ 'opacity-100': currentStep >= {{ $i }}, 'opacity-40': currentStep < {{ $i }} }">
                        <div class="step-circle"
                             :class="currentStep > {{ $i }} ? 'step-circle-done' : (currentStep === {{ $i }} ? 'step-circle-active' : 'step-circle-inactive')">
                            <template x-if="currentStep > {{ $i }}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <template x-if="currentStep <= {{ $i }}">
                                <span class="text-sm font-semibold">{{ $i }}</span>
                            </template>
                        </div>
                        <span class="hidden sm:block text-xs mt-1.5 font-medium text-center" :class="currentStep === {{ $i }} ? 'text-foreground' : 'text-muted-foreground'">{{ $stepLabels[$i-1] }}</span>
                    </div>
                    @if($i < 5)
                        <div class="step-line flex-1 mx-1" :class="currentStep > {{ $i }} ? 'step-line-done' : 'step-line-inactive'"></div>
                    @endif
                @endfor
            </div>
        </div>

        {{-- Form --}}
        <form action="{{ route('public.booking.store') }}" method="POST" id="booking-form" @click="handleSlotClick($event)">
            @csrf

            {{-- Hidden fields for submission --}}
            <input type="hidden" name="calendar_id" :value="calendarId || ''" />
            <input type="hidden" name="employee_id" :value="selectedEmployeeId || ''" />
            <input type="hidden" name="start_time" :value="selectedSlotStart" />
            <input type="hidden" name="end_time" :value="selectedSlotEnd" />
            <template x-for="sid in selectedServiceIds" :key="sid">
                <input type="hidden" name="service_ids[]" :value="sid" />
            </template>
            <input type="hidden" name="name" :value="customerName" />
            <input type="hidden" name="email" :value="customerEmail" />
            <input type="hidden" name="phone" :value="customerPhone" />

            {{-- Step 1: Select Professional --}}
            <div x-show="currentStep === 1" x-transition:enter="step-enter" x-transition:leave="step-leave">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-foreground">{{ __('Escolha o profissional') }}</h2>
                    <p class="text-sm text-muted-foreground mt-1">{{ __('Selecione o profissional que irá atendê-lo.') }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($employees as $emp)
                        <button type="button"
                                class="card-selectable group"
                                :class="{ 'card-selected': selectedEmployeeId === {{ $emp['id'] }} }"
                                @click="selectEmployee({{ $emp['id'] }})">
                            <div class="flex flex-col items-center text-center p-6">
                                @if($emp['photo_url'])
                                    <img src="{{ $emp['photo_url'] }}" alt="{{ $emp['name'] }}" class="w-20 h-20 rounded-full object-cover mb-3 ring-2 ring-border group-hover:ring-primary/50 transition-all" :class="{ 'ring-primary': selectedEmployeeId === {{ $emp['id'] }} }" />
                                @else
                                    <div class="w-20 h-20 rounded-full bg-muted flex items-center justify-center mb-3 ring-2 ring-border group-hover:ring-primary/50 transition-all" :class="{ 'ring-primary': selectedEmployeeId === {{ $emp['id'] }} }">
                                        <svg class="w-8 h-8 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                    </div>
                                @endif
                                <span class="font-medium text-foreground">{{ $emp['name'] }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Step 2: Select Services --}}
            <div x-show="currentStep === 2" x-transition:enter="step-enter" x-transition:leave="step-leave">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-foreground">{{ __('Escolha os serviços') }}</h2>
                    <p class="text-sm text-muted-foreground mt-1">{{ __('Selecione um ou mais serviços desejados.') }}</p>
                </div>

                <div class="space-y-3">
                    <template x-for="service in filteredServices" :key="service.id">
                        <button type="button"
                                class="card-selectable w-full text-left"
                                :class="{ 'card-selected': isServiceSelected(service.id) }"
                                @click="toggleService(service.id)">
                            <div class="flex items-center gap-4 p-4">
                                <template x-if="service.image_url">
                                    <img :src="service.image_url" :alt="service.name" class="w-16 h-16 rounded-lg object-cover shrink-0" />
                                </template>
                                <template x-if="!service.image_url">
                                    <div class="w-16 h-16 rounded-lg bg-muted flex items-center justify-center shrink-0">
                                        <svg class="w-6 h-6 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z"/></svg>
                                    </div>
                                </template>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-foreground" x-text="service.name"></p>
                                    <p class="text-sm text-muted-foreground">
                                        <span x-text="formatPrice(service.price)"></span> &middot;
                                        <span x-text="service.duration_minutes + ' min'"></span>
                                    </p>
                                </div>
                                <div class="shrink-0">
                                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors"
                                         :class="isServiceSelected(service.id) ? 'border-primary bg-primary' : 'border-border'">
                                        <svg x-show="isServiceSelected(service.id)" class="w-3.5 h-3.5 text-primary-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>

                {{-- Running total --}}
                <div x-show="selectedServiceIds.length > 0" x-transition class="mt-6 rounded-lg bg-muted/50 border border-border p-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-muted-foreground">
                            <span x-text="selectedServiceIds.length"></span> {{ __('serviço(s)') }} &middot;
                            <span x-text="totalDuration + ' min'"></span>
                        </span>
                        <span class="text-lg font-semibold text-foreground">
                            <span x-text="formatPrice(totalPrice)"></span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Step 3: Date & Time --}}
            <div x-show="currentStep === 3" x-transition:enter="step-enter" x-transition:leave="step-leave">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-foreground">{{ __('Escolha o horário') }}</h2>
                    <p class="text-sm text-muted-foreground mt-1">{{ __('Selecione a data e o horário desejado.') }}</p>
                </div>

                <div class="mb-4">
                    <label for="booking_from_date" class="block text-sm font-medium text-foreground mb-1.5">{{ __('A partir de') }}</label>
                    <input type="date"
                           id="booking_from_date"
                           value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                           min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                           class="w-full sm:w-auto rounded-lg border border-input bg-background px-4 py-2.5 text-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
                </div>

                {{-- HTMX trigger --}}
                <div id="slots-trigger"
                     hx-get="{{ route('public.booking.slots') }}"
                     hx-vals='js:{
                         "calendar_id": document.querySelector("[name=calendar_id]")?.value || "",
                         "employee_id": document.querySelector("[name=employee_id]")?.value || "",
                         "cursor": document.getElementById("booking_from_date")?.value || ""
                     }'
                     hx-include="[name='service_ids[]']"
                     hx-trigger="loadSlots, change from:#booking_from_date delay:300ms"
                     hx-target="#slots-container"
                     hx-swap="innerHTML"
                ></div>

                <div id="slots-container" class="mt-4">
                    <p class="text-sm text-muted-foreground">{{ __('Carregando horários disponíveis...') }}</p>
                </div>
            </div>

            {{-- Step 4: Customer Info --}}
            <div x-show="currentStep === 4" x-transition:enter="step-enter" x-transition:leave="step-leave">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-foreground">{{ __('Seus dados') }}</h2>
                    <p class="text-sm text-muted-foreground mt-1">{{ __('Informe seus dados para o agendamento.') }}</p>
                </div>

                <div class="rounded-xl border border-border bg-card p-6 space-y-4">
                    <div>
                        <label for="customer_name" class="block text-sm font-medium text-foreground mb-1.5">{{ __('Nome') }} *</label>
                        <input type="text"
                               id="customer_name"
                               x-model="customerName"
                               required
                               placeholder="{{ __('Seu nome completo') }}"
                               class="w-full rounded-lg border border-input bg-background px-4 py-2.5 text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
                    </div>
                    <div>
                        <label for="customer_email" class="block text-sm font-medium text-foreground mb-1.5">{{ __('E-mail') }}</label>
                        <input type="email"
                               id="customer_email"
                               x-model="customerEmail"
                               placeholder="{{ __('seu@email.com') }}"
                               class="w-full rounded-lg border border-input bg-background px-4 py-2.5 text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
                    </div>
                    <div>
                        <label for="customer_phone" class="block text-sm font-medium text-foreground mb-1.5">{{ __('Telefone') }}</label>
                        <input type="text"
                               id="customer_phone"
                               x-model="customerPhone"
                               placeholder="{{ __('(00) 00000-0000') }}"
                               class="w-full rounded-lg border border-input bg-background px-4 py-2.5 text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring" />
                    </div>
                </div>
            </div>

            {{-- Step 5: Confirmation --}}
            <div x-show="currentStep === 5" x-transition:enter="step-enter" x-transition:leave="step-leave">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-foreground">{{ __('Confirme seu agendamento') }}</h2>
                    <p class="text-sm text-muted-foreground mt-1">{{ __('Revise as informações antes de confirmar.') }}</p>
                </div>

                <div class="rounded-xl border border-border bg-card divide-y divide-border">
                    {{-- Professional --}}
                    <div class="p-5 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Profissional') }}</p>
                            <p class="font-medium text-foreground" x-text="selectedEmployeeName"></p>
                        </div>
                    </div>

                    {{-- Services --}}
                    <div class="p-5">
                        <div class="flex items-center gap-4 mb-3">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Serviços') }}</p>
                            </div>
                        </div>
                        <ul class="ml-14 space-y-1">
                            <template x-for="name in selectedServiceNames" :key="name">
                                <li class="text-sm text-foreground" x-text="name"></li>
                            </template>
                        </ul>
                        <p class="ml-14 mt-2 text-sm text-muted-foreground">
                            <span x-text="totalDuration + ' min'"></span> &middot;
                            <span class="font-medium text-foreground" x-text="formatPrice(totalPrice)"></span>
                        </p>
                    </div>

                    {{-- Date & Time --}}
                    <div class="p-5 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Data e horário') }}</p>
                            <p class="font-medium text-foreground">
                                <span x-text="formattedSlotDate"></span> &middot;
                                <span x-text="formattedSlotTime"></span>
                            </p>
                        </div>
                    </div>

                    {{-- Customer info --}}
                    <div class="p-5 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground uppercase tracking-wide">{{ __('Seus dados') }}</p>
                            <p class="font-medium text-foreground" x-text="customerName"></p>
                            <p class="text-sm text-muted-foreground" x-show="customerEmail" x-text="customerEmail"></p>
                            <p class="text-sm text-muted-foreground" x-show="customerPhone" x-text="customerPhone"></p>
                        </div>
                    </div>
                </div>

                <button type="submit"
                        class="mt-6 w-full rounded-lg bg-primary px-6 py-3.5 text-sm font-semibold text-primary-foreground hover:opacity-90 transition-opacity">
                    {{ __('Confirmar agendamento') }}
                </button>
            </div>

            {{-- Navigation buttons --}}
            <div class="mt-8 flex items-center justify-between" x-show="currentStep < 5">
                <button type="button"
                        x-show="currentStep > 1"
                        @click="prevStep()"
                        class="rounded-lg border border-border bg-card px-5 py-2.5 text-sm font-medium text-foreground hover:bg-muted transition-colors">
                    {{ __('Voltar') }}
                </button>
                <div x-show="currentStep === 1"></div>

                <button type="button"
                        @click="nextStep()"
                        :disabled="!canProceed()"
                        class="rounded-lg bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground hover:opacity-90 transition-opacity disabled:opacity-40 disabled:cursor-not-allowed ml-auto">
                    {{ __('Continuar') }}
                </button>
            </div>

            {{-- Back button on confirmation step --}}
            <div class="mt-4 text-center" x-show="currentStep === 5">
                <button type="button" @click="prevStep()" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                    {{ __('Voltar e editar') }}
                </button>
            </div>
        </form>
    </main>

    <footer class="border-t border-border bg-muted/30 py-6 mt-auto">
        <div class="container mx-auto px-4 text-center text-sm text-muted-foreground">
            &copy; {{ date('Y') }} {{ $tenant->name }}
        </div>
    </footer>
</div>
@endsection
