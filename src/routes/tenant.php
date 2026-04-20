<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantProfileComplete;
use App\Support\ThemeResolver;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    EnsureTenantProfileComplete::class,
])->group(function () {
    // Rotas públicas (página do tenant e agendamento)
    Route::get('/', \App\Actions\Public\ShowPublicTenantPageAction::class)->name('public.home');
    Route::get('/agendar', \App\Actions\Public\ShowBookingFormAction::class)->name('public.booking');
    Route::get('/agendar/slots', \App\Actions\Public\LoadPublicSlotsAction::class)->name('public.booking.slots');
    Route::post('/agendar', \App\Actions\Public\StorePublicBookingAction::class)->name('public.booking.store');
    Route::get('/agendamento-confirmado', function () {
        $tenant = tenant();
        if (! $tenant) {
            abort(404);
        }

        return view(ThemeResolver::viewPath('booking-confirmed'), ['tenant' => $tenant]);
    })->name('public.booking-confirmed');

    Route::middleware(['auth', 'verified', EnsureTenantProfileComplete::class])->group(function () {
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

        // Agendamentos
        Route::get('/scheduling/{calendar}/employee/{employee?}',
            \App\Actions\Scheduling\ShowSchedulingCalendarAction::class)
            ->name('scheduling.index');

        Route::get('/scheduling/events/{calendar}/{employee}',
            \App\Actions\Scheduling\LoadSchedulingEventsAction::class)
            ->name('scheduling.events');

        Route::get('/scheduling/list',
            \App\Actions\Scheduling\ListSchedulingsAction::class)
            ->name('scheduling.list');

        Route::get('/scheduling/create/{calendar}/{employee}',
            \App\Actions\Scheduling\ShowSchedulingFormAction::class)
            ->name('scheduling.create');

        Route::get('/scheduling/{scheduling}/data',
            \App\Actions\Scheduling\GetSchedulingDataAction::class)
            ->name('scheduling.data')
            ->where('scheduling', '[0-9]+');

        Route::get('/scheduling/{scheduling}/edit',
            \App\Actions\Scheduling\ShowSchedulingFormAction::class)
            ->name('scheduling.edit')
            ->where('scheduling', '[0-9]+');

        Route::post('/scheduling',
            \App\Actions\Scheduling\StoreSchedulingAction::class)
            ->name('scheduling.store');

        Route::put('/scheduling/{scheduling}',
            \App\Actions\Scheduling\UpdateSchedulingAction::class)
            ->name('scheduling.update');

        Route::delete('/scheduling/{scheduling}',
            \App\Actions\Scheduling\DeleteSchedulingAction::class)
            ->name('scheduling.destroy');

        Route::post('/scheduling/mass-cancel',
            \App\Actions\Scheduling\MassCancelSchedulingsAction::class)
            ->name('scheduling.mass-cancel');

        // Clientes (JSON para modal de seleção)
        Route::post('/customers',
            \App\Actions\Customer\StoreCustomerAction::class)
            ->name('customers.store');

        // Agendas
        Route::get('/calendars',
            \App\Actions\Calendar\ListCalendarsAction::class)
            ->name('calendars.index');

        Route::get('/calendars/create',
            \App\Actions\Calendar\ShowCalendarFormAction::class)
            ->name('calendars.create');

        Route::get('/calendars/{calendar}/edit',
            \App\Actions\Calendar\ShowCalendarFormAction::class)
            ->name('calendars.edit');

        Route::post('/calendars',
            \App\Actions\Calendar\StoreCalendarAction::class)
            ->name('calendars.store');

        Route::put('/calendars/{calendar}',
            \App\Actions\Calendar\UpdateCalendarAction::class)
            ->name('calendars.update');

        Route::delete('/calendars/{calendar}',
            \App\Actions\Calendar\DeleteCalendarAction::class)
            ->name('calendars.destroy');

        // Schedule (lista de agendas)
        Route::get('/schedule',
            \App\Actions\Schedule\ListAvailableCalendarsAction::class)
            ->name('schedule.index');

        // Funcionários
        Route::get('/employees',
            \App\Actions\Employee\ListEmployeesAction::class)
            ->name('employees.index');

        Route::get('/employees/create',
            \App\Actions\Employee\ShowEmployeeFormAction::class)
            ->name('employees.create');

        Route::get('/employees/{employee}/edit',
            \App\Actions\Employee\ShowEmployeeFormAction::class)
            ->name('employees.edit');

        Route::post('/employees',
            \App\Actions\Employee\StoreEmployeeAction::class)
            ->name('employees.store');

        Route::put('/employees/{employee}',
            \App\Actions\Employee\UpdateEmployeeAction::class)
            ->name('employees.update');

        Route::delete('/employees/{employee}',
            \App\Actions\Employee\DeleteEmployeeAction::class)
            ->name('employees.destroy');

        // Folgas (Dias de Folga dos Funcionários)
        Route::get('/folgas',
            \App\Actions\EmployeeDayOff\ListEmployeeDaysOffAction::class)
            ->name('employee-days-off.index');

        Route::post('/folgas',
            \App\Actions\EmployeeDayOff\StoreEmployeeDayOffAction::class)
            ->name('employee-days-off.store');

        Route::put('/folgas/{employeeDayOff}',
            \App\Actions\EmployeeDayOff\UpdateEmployeeDayOffAction::class)
            ->name('employee-days-off.update');

        Route::delete('/folgas/{employeeDayOff}',
            \App\Actions\EmployeeDayOff\DeleteEmployeeDayOffAction::class)
            ->name('employee-days-off.destroy');

        // Feriados
        Route::get('/feriados',
            \App\Actions\Holiday\ListHolidaysAction::class)
            ->name('holidays.index');

        Route::post('/feriados',
            \App\Actions\Holiday\StoreHolidayAction::class)
            ->name('holidays.store');

        Route::put('/feriados/{holiday}',
            \App\Actions\Holiday\UpdateHolidayAction::class)
            ->name('holidays.update');

        Route::delete('/feriados/{holiday}',
            \App\Actions\Holiday\DeleteHolidayAction::class)
            ->name('holidays.destroy');

        // Serviços
        Route::get('/services',
            \App\Actions\Service\ListServicesAction::class)
            ->name('services.index');

        Route::get('/services/create',
            \App\Actions\Service\ShowServiceFormAction::class)
            ->name('services.create');

        Route::get('/services/{service}/edit',
            \App\Actions\Service\ShowServiceFormAction::class)
            ->name('services.edit')
            ->where('service', '[0-9]+');

        Route::post('/services',
            \App\Actions\Service\StoreServiceAction::class)
            ->name('services.store');

        Route::put('/services/{service}',
            \App\Actions\Service\UpdateServiceAction::class)
            ->name('services.update');

        Route::delete('/services/{service}',
            \App\Actions\Service\DeleteServiceAction::class)
            ->name('services.destroy');

        // Contas (Financeiro)
        Route::get('/accounts',
            \App\Actions\Account\ListAccountsAction::class)
            ->name('accounts.index');

        Route::get('/accounts/create',
            \App\Actions\Account\ShowAccountFormAction::class)
            ->name('accounts.create');

        Route::get('/accounts/{account}/edit',
            \App\Actions\Account\ShowAccountFormAction::class)
            ->name('accounts.edit')
            ->where('account', '[0-9]+');

        Route::post('/accounts',
            \App\Actions\Account\StoreAccountAction::class)
            ->name('accounts.store');

        Route::put('/accounts/{account}',
            \App\Actions\Account\UpdateAccountAction::class)
            ->name('accounts.update');

        Route::delete('/accounts/{account}',
            \App\Actions\Account\DeleteAccountAction::class)
            ->name('accounts.destroy');

        Route::post('/accounts/mass-cancel',
            \App\Actions\Account\MassCancelAccountsAction::class)
            ->name('accounts.mass-cancel');

        Route::post('/accounts/mass-payment',
            \App\Actions\Account\MassPaymentAccountsAction::class)
            ->name('accounts.mass-payment');

        // Contas DRE
        Route::get('/dres',
            \App\Actions\Dre\ListDresAction::class)
            ->name('dres.index');

        Route::get('/dres/create',
            \App\Actions\Dre\ShowDreFormAction::class)
            ->name('dres.create');

        Route::get('/dres/{dre}/edit',
            \App\Actions\Dre\ShowDreFormAction::class)
            ->name('dres.edit')
            ->where('dre', '[0-9]+');

        Route::post('/dres',
            \App\Actions\Dre\StoreDreAction::class)
            ->name('dres.store');

        Route::put('/dres/{dre}',
            \App\Actions\Dre\UpdateDreAction::class)
            ->name('dres.update');

        Route::delete('/dres/{dre}',
            \App\Actions\Dre\DeleteDreAction::class)
            ->name('dres.destroy');

        // Relatórios
        Route::get('/reports',
            \App\Actions\Report\ListReportsAction::class)
            ->name('reports.index');

        Route::get('/reports/dre',
            \App\Actions\Report\ShowDreReportAction::class)
            ->name('reports.dre');

        Route::get('/reports/dre/pdf',
            \App\Actions\Report\ExportDrePdfAction::class)
            ->name('reports.dre.pdf');

        Route::get('/reports/dre/excel',
            \App\Actions\Report\ExportDreExcelAction::class)
            ->name('reports.dre.excel');

        Route::get('/reports/employee-performance',
            \App\Actions\Report\ShowEmployeePerformanceReportAction::class)
            ->name('reports.employee-performance');

        Route::get('/reports/employee-performance/pdf',
            \App\Actions\Report\ExportEmployeePerformancePdfAction::class)
            ->name('reports.employee-performance.pdf');

        Route::get('/reports/employee-performance/excel',
            \App\Actions\Report\ExportEmployeePerformanceExcelAction::class)
            ->name('reports.employee-performance.excel');

        Route::get('/reports/service-analysis',
            \App\Actions\Report\ShowServiceReportAction::class)
            ->name('reports.service-analysis');

        Route::get('/reports/service-analysis/pdf',
            \App\Actions\Report\ExportServicePdfAction::class)
            ->name('reports.service-analysis.pdf');

        Route::get('/reports/service-analysis/excel',
            \App\Actions\Report\ExportServiceExcelAction::class)
            ->name('reports.service-analysis.excel');

        Route::get('/reports/customer-analysis',
            \App\Actions\Report\ShowCustomerReportAction::class)
            ->name('reports.customer-analysis');

        Route::get('/reports/customer-analysis/pdf',
            \App\Actions\Report\ExportCustomerPdfAction::class)
            ->name('reports.customer-analysis.pdf');

        Route::get('/reports/customer-analysis/excel',
            \App\Actions\Report\ExportCustomerExcelAction::class)
            ->name('reports.customer-analysis.excel');

        // Configurações do tenant (junto das outras em /settings)
        Route::get('/settings/tenant',
            \App\Actions\Tenant\ShowTenantSettingsAction::class)
            ->name('configuracoes.index');

        Route::put('/settings/tenant',
            \App\Actions\Tenant\UpdateTenantSettingsAction::class)
            ->name('configuracoes.update');

        Route::get('/settings/public-page',
            \App\Actions\Tenant\ShowPublicPageSettingsAction::class)
            ->name('configuracoes.public-page');

        Route::post('/settings/public-page',
            \App\Actions\Tenant\UpdatePublicPageSettingsAction::class)
            ->name('configuracoes.public-page.update');

    });

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/tenant/complete-profile',
            \App\Actions\Tenant\ShowCompleteTenantFormAction::class)
            ->name('tenant.complete-profile');

        Route::put('/tenant',
            \App\Actions\Tenant\UpdateTenantAction::class)
            ->name('tenant.update');
    });
});
