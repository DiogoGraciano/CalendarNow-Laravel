import { useState, useEffect, useMemo } from 'react';
import { router, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from '@/hooks/use-translation';
import { useFormatCurrency } from '@/hooks/use-format-currency';
import { Button } from '@/components/ui/button';
import { store as schedulingStore, update as schedulingUpdate } from '@/routes/scheduling';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

interface Service {
    id: number;
    name: string;
    price: number;
    /** Duração em minutos (number) ou string no formato "HH:MM:SS" / "H:MM:SS" do backend */
    duration?: number | string;
}

/** Soma minutos a uma string datetime-local (YYYY-MM-DDTHH:mm) e retorna no mesmo formato */
function addMinutesToDateTimeLocal(dateTimeLocal: string, minutes: number): string {
    const s = String(dateTimeLocal ?? '').trim();
    if (!s) return s;
    const d = new Date(s);
    if (Number.isNaN(d.getTime())) return s;
    d.setMinutes(d.getMinutes() + minutes);
    const pad = (n: number) => n.toString().padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

/** Converte duration do backend (número ou "HH:MM:SS") para minutos */
function parseDurationToMinutes(value: unknown): number {
    if (typeof value === 'number' && !Number.isNaN(value)) return value;
    if (typeof value !== 'string' || !value) return 0;
    const parts = value.trim().split(':').map((p) => parseInt(p, 10));
    if (parts.length >= 2) {
        const [h, m, s] = parts;
        const hours = Number.isNaN(h) ? 0 : h;
        const mins = Number.isNaN(m) ? 0 : m;
        const secs = parts.length >= 3 && !Number.isNaN(s) ? s : 0;
        return hours * 60 + mins + secs / 60;
    }
    const n = Number(value);
    return Number.isNaN(n) ? 0 : n;
}

export interface SelectedCustomer {
    id: number;
    name: string;
}

interface SchedulingFormProps {
    calendarId: number;
    employeeId: number;
    startTime?: string;
    endTime?: string;
    /** Código do agendamento (apenas create; vindo do backend ao abrir o modal) */
    code?: string;
    schedulingId?: number;
    services?: Service[];
    customers?: Array<{ id: number; name: string }>;
    statuses?: Array<{ id: string; name: string }>;
    selectedCustomer?: SelectedCustomer | null;
    onOpenCustomerSelection?: () => void;
    onClose?: () => void;
    /** Chamado após salvar com sucesso (criar/atualizar), antes de onClose */
    onSuccess?: () => void;
    initialData?: {
        customer_id?: number;
        status?: string;
        color?: string;
        notes?: string;
        items?: Array<{
            service_id: number;
            quantity: number;
            unit_amount: number;
            duration: number;
        }>;
    };
}

interface SchedulingItem {
    serviceId: number;
    quantity: number;
    unitAmount: number;
    duration: number;
    total: number;
    selected: boolean;
}

export function SchedulingForm({
    calendarId,
    employeeId,
    startTime,
    endTime,
    code: codeProp,
    schedulingId,
    services = [],
    customers = [],
    statuses = [
        { id: 'pending', name: 'Pendente' },
        { id: 'confirmed', name: 'Confirmado' },
        { id: 'completed', name: 'Concluído' },
        { id: 'cancelled', name: 'Cancelado' },
    ],
    selectedCustomer = null,
    onOpenCustomerSelection,
    onClose,
    onSuccess,
    initialData,
}: SchedulingFormProps) {
    const { t } = useTranslation();
    const formatCurrency = useFormatCurrency();
    const [items, setItems] = useState<SchedulingItem[]>([]);

    const formatDuration = (minutes: number | undefined | null): string => {
        const n = Number(minutes);
        if (Number.isNaN(n) || n < 0) return '0 min';
        if (n < 60) return `${Math.round(n)} min`;
        const h = Math.floor(n / 60);
        const m = Math.round(n % 60);
        return m > 0 ? `${h}h ${m} min` : `${h}h`;
    };

    const [total, setTotal] = useState(0);

    const pageProps = usePage().props as { errors?: Record<string, string> };
    const pageErrors = pageProps.errors ?? {};

    const { data, setData, processing, errors } = useForm({
        code: codeProp ?? '',
        calendar_id: calendarId,
        employee_id: employeeId,
        customer_id: initialData?.customer_id ?? selectedCustomer?.id ?? '',
        start_time: startTime || '',
        end_time: endTime || '',
        status: initialData?.status ?? 'pending',
        color: initialData?.color ?? '#4267b2',
        notes: initialData?.notes ?? '',
        items: [] as Array<{
            service_id: number;
            quantity: number;
            unit_amount: number;
            duration: number;
        }>,
    });

    /** Erros de validação: do useForm (quando usa post/put do form) + da página (quando usa router.post/put) */
    const displayErrors = { ...pageErrors, ...(errors as Record<string, string>) };

    useEffect(() => {
        if (selectedCustomer) {
            setData('customer_id', selectedCustomer.id);
        }
    }, [selectedCustomer]);

    useEffect(() => {
        if (startTime) setData('start_time', startTime);
        if (endTime) setData('end_time', endTime);
    }, [startTime, endTime]);

    const periodMinutes = useMemo(() => {
        const s = data.start_time && String(data.start_time).trim();
        const e = data.end_time && String(data.end_time).trim();
        if (!s || !e) return 0;
        const start = new Date(s).getTime();
        const end = new Date(e).getTime();
        if (Number.isNaN(start) || Number.isNaN(end) || end <= start) return 0;
        return Math.round((end - start) / (60 * 1000));
    }, [data.start_time, data.end_time]);

    const totalDurationMinutes = useMemo(() => {
        return items
            .filter((item) => item.selected)
            .reduce((sum, item) => sum + (Number(item.duration) || 0) * (Number(item.quantity) || 1), 0);
    }, [items]);

    const durationExceeded = periodMinutes > 0 && totalDurationMinutes > periodMinutes;

    // Atualizar end_time conforme a soma das durações dos serviços selecionados
    useEffect(() => {
        const start = String(data.start_time ?? '').trim();
        if (!start) return;
        const duration = Math.max(totalDurationMinutes, 1);
        const newEnd = addMinutesToDateTimeLocal(start, duration);
        setData('end_time', newEnd);
    }, [data.start_time, totalDurationMinutes]);

    useEffect(() => {
        // Inicializar itens com serviços disponíveis
        const toNum = (v: unknown) => (typeof v === 'number' && !Number.isNaN(v) ? v : Number(v) || 0);
        const normId = (v: unknown) => (typeof v === 'number' && !Number.isNaN(v) ? v : parseInt(String(v), 10) || 0);
        if (initialData?.items && Array.isArray(initialData.items) && initialData.items.length > 0) {
            // Se há dados iniciais, usar eles (comparar ids em número para evitar string vs number)
            const initialItems: SchedulingItem[] = services.map((service) => {
                const serviceIdNum = normId(service.id);
                const existingItem = initialData.items?.find(
                    (item) => normId(item.service_id) === serviceIdNum
                );
                const price = toNum(service.price);
                const duration = parseDurationToMinutes(service.duration);
                if (existingItem) {
                    const qty = toNum(existingItem.quantity);
                    const unit = toNum(existingItem.unit_amount);
                    const dur = parseDurationToMinutes(existingItem.duration);
                    return {
                        serviceId: service.id,
                        quantity: qty,
                        unitAmount: unit,
                        duration: dur,
                        total: unit * qty,
                        selected: true,
                    };
                }
                return {
                    serviceId: service.id,
                    quantity: 1,
                    unitAmount: price,
                    duration,
                    total: price,
                    selected: false,
                };
            });
            setItems(initialItems);
        } else {
            // Caso contrário, inicializar com todos os serviços não selecionados
            const initialItems: SchedulingItem[] = services.map((service) => {
                const price = toNum(service.price);
                const duration = parseDurationToMinutes(service.duration);
                return {
                    serviceId: service.id,
                    quantity: 1,
                    unitAmount: price,
                    duration,
                    total: price,
                    selected: false,
                };
            });
            setItems(initialItems);
        }
    }, [services, initialData]);

    useEffect(() => {
        // Calcular total
        const selectedItems = items.filter((item) => item.selected);
        const calculatedTotal = selectedItems.reduce(
            (sum, item) => sum + (Number(item.total) || 0),
            0
        );
        setTotal(calculatedTotal);
    }, [items]);

    const handleItemChange = (
        index: number,
        field: keyof SchedulingItem,
        value: number | boolean
    ) => {
        const newItems = [...items];
        const item = { ...newItems[index] };

        const num = (v: number | boolean) => (typeof v === 'number' ? (Number.isNaN(v) ? 0 : v) : 0);
        if (field === 'selected') {
            item.selected = value as boolean;
        } else if (field === 'quantity') {
            item.quantity = num(value as number);
            item.total = (Number(item.unitAmount) || 0) * item.quantity;
        } else if (field === 'unitAmount') {
            item.unitAmount = num(value as number);
            item.total = item.unitAmount * (Number(item.quantity) || 0);
        }

        newItems[index] = item;
        setItems(newItems);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (durationExceeded) return;

        const selectedItems = items
            .filter((item) => item.selected)
            .map((item) => ({
                service_id: item.serviceId,
                quantity: item.quantity,
                unit_amount: item.unitAmount,
                duration: item.duration,
            }));

        // Enviar start_time e end_time em UTC para o backend comparar corretamente com os agendamentos existentes
        const startDate = data.start_time ? new Date(data.start_time) : null;
        const endDate = data.end_time ? new Date(data.end_time) : null;
        const payload = {
            ...data,
            start_time: startDate && !Number.isNaN(startDate.getTime()) ? startDate.toISOString() : data.start_time,
            end_time: endDate && !Number.isNaN(endDate.getTime()) ? endDate.toISOString() : data.end_time,
            items: selectedItems,
        };

        const handleSuccess = () => {
            onSuccess?.();
            if (onClose) onClose();
        };

        if (schedulingId) {
            router.put(schedulingUpdate.url({ scheduling: schedulingId }), payload, {
                onSuccess: handleSuccess,
            });
        } else {
            router.post(schedulingStore.url(), payload, {
                onSuccess: handleSuccess,
            });
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4 w-full min-w-0">
            {displayErrors.error && (
                <p className="rounded-md bg-destructive/15 p-3 text-sm text-destructive" role="alert">
                    {t(displayErrors.error)}
                </p>
            )}
            {codeProp && (
                <div>
                    <Label>{t('scheduling.form.code')}</Label>
                    <p className="mt-1 text-sm font-mono text-muted-foreground">{codeProp}</p>
                </div>
            )}
            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <Label htmlFor="color">{t('scheduling.form.color')}</Label>
                    <Input
                        id="color"
                        type="color"
                        value={data.color}
                        onChange={(e) => setData('color', e.target.value)}
                        className="h-10"
                    />
                </div>

                <div>
                    <Label htmlFor="customer_id">{t('scheduling.form.customer')}</Label>
                    {onOpenCustomerSelection ? (
                        <Button
                            id="customer_id"
                            type="button"
                            variant="outline"
                            className="w-full justify-start font-normal h-9"
                            onClick={onOpenCustomerSelection}
                        >
                            {selectedCustomer?.name ?? t('scheduling.form.selectCustomer')}
                        </Button>
                    ) : (
                        <Select
                            value={data.customer_id?.toString() ?? ''}
                            onValueChange={(value) => setData('customer_id', value ? parseInt(value) : '')}
                        >
                            <SelectTrigger id="customer_id">
                                <SelectValue placeholder={t('scheduling.form.selectPlaceholder')} />
                            </SelectTrigger>
                            <SelectContent>
                                {customers.map((customer) => (
                                    <SelectItem key={customer.id} value={customer.id.toString()}>
                                        {customer.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    )}
                    {displayErrors.customer_id && (
                        <p className="text-sm text-red-500 mt-1">{t(displayErrors.customer_id)}</p>
                    )}
                </div>

                <div>
                    <Label htmlFor="status">{t('scheduling.form.status')}</Label>
                    <Select
                        value={data.status}
                        onValueChange={(value) => setData('status', value)}
                    >
                        <SelectTrigger id="status">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {statuses.map((status) => (
                                <SelectItem key={status.id} value={status.id}>
                                    {status.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                <div>
                    <Label htmlFor="start_time">{t('scheduling.form.startTime')}</Label>
                    <Input
                        id="start_time"
                        type="datetime-local"
                        value={data.start_time}
                        onChange={(e) => setData('start_time', e.target.value)}
                        required
                    />
                    {displayErrors.start_time && (
                        <p className="text-sm text-red-500 mt-1">{t(displayErrors.start_time)}</p>
                    )}
                </div>

                <div>
                    <Label htmlFor="end_time">{t('scheduling.form.endTime')}</Label>
                    <Input
                        id="end_time"
                        type="datetime-local"
                        value={data.end_time}
                        onChange={(e) => setData('end_time', e.target.value)}
                        required
                    />
                    {displayErrors.end_time && (
                        <p className="text-sm text-red-500 mt-1">{t(displayErrors.end_time)}</p>
                    )}
                </div>
            </div>

            <div>
                <Label>{t('scheduling.form.services')}</Label>
                {(durationExceeded || displayErrors.items) && (
                    <p className="mt-1 text-sm text-destructive" role="alert">
                        {displayErrors.items === 'validation.scheduling.duration_exceeds_period'
                            ? t('validation.scheduling.duration_exceeds_period', {
                                  duration: totalDurationMinutes,
                                  period: periodMinutes,
                              })
                            : (displayErrors.items ? t(displayErrors.items) : null) ||
                              t('scheduling.validation.durationExceedsPeriod', {
                                  duration: totalDurationMinutes,
                                  period: periodMinutes,
                              })}
                    </p>
                )}
                <div className="mt-2 border rounded-lg overflow-hidden">
                    <table className="w-full">
                        <thead className="bg-muted">
                            <tr>
                                <th className="p-2 text-left">
                                    <Checkbox disabled />
                                </th>
                                <th className="p-2 text-left">{t('scheduling.form.serviceName')}</th>
                                <th className="p-2 text-left">{t('scheduling.form.quantity')}</th>
                                <th className="p-2 text-left">{t('scheduling.form.duration')}</th>
                                <th className="p-2 text-left">{t('scheduling.form.total')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {items.map((item, index) => {
                                const service = services.find((s) => s.id === item.serviceId);
                                return (
                                    <tr key={item.serviceId} className="border-t">
                                        <td className="p-2">
                                            <Checkbox
                                                checked={item.selected}
                                                onCheckedChange={(checked) =>
                                                    handleItemChange(index, 'selected', checked === true)
                                                }
                                            />
                                        </td>
                                        <td className="p-2">{service?.name}</td>
                                        <td className="p-2">
                                            <Input
                                                type="number"
                                                min="1"
                                                value={item.quantity}
                                                onChange={(e) =>
                                                    handleItemChange(
                                                        index,
                                                        'quantity',
                                                        parseInt(e.target.value) || 1
                                                    )
                                                }
                                                className="w-20"
                                                disabled={!item.selected}
                                            />
                                        </td>
                                        <td className="p-2 text-muted-foreground">
                                            {formatDuration(item.duration)}
                                        </td>
                                        <td className="p-2">
                                            {formatCurrency(item.total)}
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <Label htmlFor="notes">{t('scheduling.form.notes')}</Label>
                <Textarea
                    id="notes"
                    value={data.notes}
                    onChange={(e) => setData('notes', e.target.value)}
                    rows={3}
                />
            </div>

            <div className="flex items-center justify-between border-t pt-4">
                <div>
                    <Label>{t('scheduling.form.total')}</Label>
                    <div className="text-2xl font-bold">
                        {formatCurrency(total)}
                    </div>
                </div>
                <div className="flex gap-2">
                    <Button type="button" variant="outline" onClick={onClose}>
                        {t('scheduling.form.cancel')}
                    </Button>
                    <Button type="submit" disabled={processing || durationExceeded}>
                        {schedulingId ? t('scheduling.form.update') : t('scheduling.form.save')}
                    </Button>
                </div>
            </div>
        </form>
    );
}

