import { FullCalendarComponent } from '@/components/scheduling/full-calendar';
import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { useTranslation } from '@/hooks/use-translation';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { SchedulingForm } from '@/components/scheduling/scheduling-form';
import { CustomerSelectionModal } from '@/components/selection-modals/customer-selection-modal';
import type { CustomerOption } from '@/components/selection-modals/customer-selection-modal';
import { index as schedulingIndex } from '@/routes/scheduling';
import type { DateSelectArg, EventClickArg } from '@fullcalendar/core';

/** Formata Date para valor do input datetime-local (horário local, YYYY-MM-DDTHH:mm) */
function toDateTimeLocalString(date: Date): string {
    const pad = (n: number) => n.toString().padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

interface Employee {
    id: number;
    user?: {
        name: string;
    };
}

interface Service {
    id: number;
    name: string;
    price: number;
    duration: number;
}

interface SchedulingIndexProps {
    calendar: {
        id: number;
        name: string;
        code?: string;
    };
    employees: Employee[];
    selectedEmployee: Employee | null;
    workDays?: number[];
    workStartTime?: string;
    workEndTime?: string;
    launchStartTime?: string;
    launchEndTime?: string;
    eventsUrl?: string;
    customers?: CustomerOption[];
    services?: Service[];
    statuses?: Array<{ id: string; name: string }>;
    customersStoreUrl?: string;
    createSchedulingCode?: string;
    error?: string;
}

export default function SchedulingIndex({
    calendar,
    employees,
    selectedEmployee,
    workDays = [1, 2, 3, 4, 5],
    workStartTime = '08:00',
    workEndTime = '18:00',
    eventsUrl,
    customers = [],
    services = [],
    statuses = [
        { id: 'pending', name: 'Pendente' },
        { id: 'confirmed', name: 'Confirmado' },
        { id: 'completed', name: 'Concluído' },
        { id: 'cancelled', name: 'Cancelado' },
    ],
    customersStoreUrl = '',
    createSchedulingCode,
    error,
}: SchedulingIndexProps) {
    const { t } = useTranslation();
    const [selectedEmployeeId, setSelectedEmployeeId] = useState<number | null>(
        selectedEmployee?.id ?? null
    );
    const [isFormOpen, setIsFormOpen] = useState(false);
    const [selectedDateRange, setSelectedDateRange] = useState<DateSelectArg | null>(null);
    const [selectedEvent, setSelectedEvent] = useState<EventClickArg | null>(null);
    const [isCustomerSelectionOpen, setIsCustomerSelectionOpen] = useState(false);
    const [selectedCustomer, setSelectedCustomer] = useState<CustomerOption | null>(null);
    const [customersList, setCustomersList] = useState<CustomerOption[]>(customers);
    const [eventsRefreshTrigger, setEventsRefreshTrigger] = useState(0);
    const [schedulingEditData, setSchedulingEditData] = useState<{
        id: number;
        calendar_id: number;
        employee_id: number;
        start_time: string;
        end_time: string;
        customer_id: number;
        status: string;
        color: string;
        notes: string;
        items: Array<{ service_id: number; quantity: number; unit_amount: number; duration: number }>;
    } | null>(null);
    const [loadingSchedulingData, setLoadingSchedulingData] = useState(false);

    useEffect(() => {
        const eventId = selectedEvent?.event?.id;
        if (!eventId || typeof eventId !== 'string') {
            setSchedulingEditData(null);
            return;
        }
        const id = parseInt(eventId, 10);
        if (Number.isNaN(id)) {
            setSchedulingEditData(null);
            return;
        }
        setLoadingSchedulingData(true);
        setSchedulingEditData(null);
        fetch(`/scheduling/${id}/data`, { credentials: 'same-origin' })
            .then((res) => {
                if (!res.ok) throw new Error('Failed to load');
                return res.json();
            })
            .then((data) => {
                setSchedulingEditData(data);
            })
            .catch(() => {
                setSchedulingEditData(null);
            })
            .finally(() => {
                setLoadingSchedulingData(false);
            });
    }, [selectedEvent?.event?.id]);

    const handleEmployeeChange = (value: string) => {
        const employeeId = parseInt(value);
        setSelectedEmployeeId(employeeId);
        router.get(
            schedulingIndex.url({
                calendar: calendar.id,
                employee: employeeId,
            })
        );
    };

    const handleDateClick = (arg: DateSelectArg) => {
        setSelectedDateRange(arg);
        setIsFormOpen(true);
    };

    const handleEventClick = (arg: EventClickArg) => {
        if (arg.event.id) {
            setSelectedEvent(arg);
            setIsFormOpen(true);
        }
    };

    const handleCloseForm = () => {
        setIsFormOpen(false);
        setSelectedDateRange(null);
        setSelectedEvent(null);
        setSchedulingEditData(null);
        setSelectedCustomer(null);
    };

    const handleSelectCustomer = (customer: CustomerOption) => {
        setSelectedCustomer(customer);
        setCustomersList((prev) =>
            prev.some((c) => c.id === customer.id) ? prev : [...prev, customer]
        );
    };

    // Converter workDays para formato do FullCalendar (0 = domingo, 6 = sábado)
    // workDays usa formato 1-7 (1=segunda, 7=domingo)
    // hiddenDays usa formato 0-6 (0=domingo, 6=sábado)
    const hiddenDays = [0, 1, 2, 3, 4, 5, 6].filter((day) => {
        // Converter formato do FullCalendar (0-6) para formato workDays (1-7)
        // 0 (domingo) -> 7, 1 (segunda) -> 1, ..., 6 (sábado) -> 6
        const workDayFormat = day === 0 ? 7 : day;
        return !workDays.includes(workDayFormat);
    });
    
    // Validar: não pode esconder todos os dias (seria inválido para o FullCalendar)
    // Se todos os dias estiverem ocultos, não passar hiddenDays
    const validHiddenDays = hiddenDays.length === 7 ? [] : hiddenDays;

    if (error || !selectedEmployee) {
        return (
            <AppLayout>
                <Head title={t('scheduling.title')} />
                <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
                    <h1 className="text-3xl font-bold">{t('scheduling.title')} - {calendar.name}</h1>
                    <div className="text-red-500">{error || t('scheduling.list.noEmployeeSelected')}</div>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout>
            <Head title={`${t('scheduling.title')} - ${calendar.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-bold">{t('scheduling.title')} - {calendar.name}</h1>
                </div>

                <div className="flex items-center gap-4">
                    <label htmlFor="employee-select" className="text-sm font-medium">
                        {t('scheduling.list.employee')}:
                    </label>
                    <Select
                        value={selectedEmployeeId?.toString() ?? ''}
                        onValueChange={handleEmployeeChange}
                    >
                        <SelectTrigger id="employee-select" className="w-[250px]">
                            <SelectValue placeholder={t('scheduling.list.selectEmployeePlaceholder')} />
                        </SelectTrigger>
                        <SelectContent>
                            {employees.map((employee) => (
                                <SelectItem key={employee.id} value={employee.id.toString()}>
                                    {employee.user?.name ?? `Funcionário #${employee.id}`}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                <div className="rounded-lg border bg-card p-4">
                    <FullCalendarComponent
                        events={[]}
                        slotMinTime={workStartTime}
                        slotMaxTime={workEndTime}
                        hiddenDays={validHiddenDays}
                        onDateClick={handleDateClick}
                        onEventClick={handleEventClick}
                        eventsUrl={eventsUrl}
                        eventsRefreshTrigger={eventsRefreshTrigger}
                    />
                </div>

                <Dialog open={isFormOpen} onOpenChange={(open) => { setIsFormOpen(open); if (!open) handleCloseForm(); }}>
                    <DialogContent className="w-full max-w-[calc(100%-2rem)] sm:max-w-4xl max-h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>
                                {selectedEvent ? t('scheduling.form.edit') : t('scheduling.form.new')}
                            </DialogTitle>
                        </DialogHeader>
                        {loadingSchedulingData && (
                            <div className="flex items-center justify-center py-12 text-muted-foreground">
                                {t('common.loading')}
                            </div>
                        )}
                        {!loadingSchedulingData && selectedDateRange && (
                            <SchedulingForm
                                calendarId={calendar.id}
                                employeeId={selectedEmployeeId!}
                                startTime={toDateTimeLocalString(selectedDateRange.start)}
                                endTime={toDateTimeLocalString(selectedDateRange.end)}
                                code={createSchedulingCode}
                                services={services}
                                customers={customersList}
                                statuses={statuses}
                                selectedCustomer={selectedCustomer}
                                onOpenCustomerSelection={() => setIsCustomerSelectionOpen(true)}
                                onClose={handleCloseForm}
                                onSuccess={() => setEventsRefreshTrigger((t) => t + 1)}
                            />
                        )}
                        {!loadingSchedulingData && schedulingEditData && selectedEvent && (
                            <SchedulingForm
                                calendarId={schedulingEditData.calendar_id}
                                employeeId={schedulingEditData.employee_id}
                                startTime={schedulingEditData.start_time}
                                endTime={schedulingEditData.end_time}
                                schedulingId={schedulingEditData.id}
                                services={services}
                                customers={customersList}
                                statuses={statuses}
                                selectedCustomer={customersList.find((c) => c.id === schedulingEditData.customer_id) ?? null}
                                onOpenCustomerSelection={() => setIsCustomerSelectionOpen(true)}
                                onClose={handleCloseForm}
                                onSuccess={() => setEventsRefreshTrigger((t) => t + 1)}
                                initialData={{
                                    customer_id: schedulingEditData.customer_id,
                                    status: schedulingEditData.status,
                                    color: schedulingEditData.color,
                                    notes: schedulingEditData.notes,
                                    items: schedulingEditData.items,
                                }}
                            />
                        )}
                    </DialogContent>
                </Dialog>

                <CustomerSelectionModal
                    isOpen={isCustomerSelectionOpen}
                    onOpenChange={setIsCustomerSelectionOpen}
                    selectedCustomerId={selectedCustomer?.id ?? null}
                    onSelectCustomer={handleSelectCustomer}
                    customers={customersList}
                    storeCustomerUrl={customersStoreUrl}
                />
            </div>
        </AppLayout>
    );
}

