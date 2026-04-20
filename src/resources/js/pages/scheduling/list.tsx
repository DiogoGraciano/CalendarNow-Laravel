import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { list as schedulingList, edit as schedulingEdit, massCancel as schedulingMassCancel } from '@/routes/scheduling';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { useState } from 'react';
import { useFormatCurrency } from '@/hooks/use-format-currency';

interface Scheduling {
    id: number;
    start_time: string;
    end_time: string;
    status: string;
    customer?: {
        name: string;
    };
    employee?: {
        user?: {
            name: string;
        };
    };
    calendar?: {
        name: string;
    };
    items?: Array<{
        total_amount: number;
    }>;
}

interface SchedulingListProps {
    schedulings: {
        data: Scheduling[];
        links: any;
        meta: any;
    };
    calendars: Array<{ id: number; name: string }>;
    employees: Array<{ id: number; user?: { name: string } }>;
    filters: {
        calendar?: number;
        employee?: number;
        dt_ini?: string;
        dt_fim?: string;
    };
}

export default function SchedulingList({
    schedulings,
    calendars,
    employees,
    filters,
}: SchedulingListProps) {
    const [selectedIds, setSelectedIds] = useState<number[]>([]);

    const handleFilter = () => {
        router.get(schedulingList.url({
            query: {
                calendar: filters.calendar,
                employee: filters.employee,
                dt_ini: filters.dt_ini,
                dt_fim: filters.dt_fim,
            },
        }));
    };

    const handleMassCancel = () => {
        if (selectedIds.length === 0) {
            alert('Selecione ao menos um agendamento');
            return;
        }

        router.post(
            schedulingMassCancel.url(),
            { ids: selectedIds },
            {
                onSuccess: () => {
                    setSelectedIds([]);
                },
            }
        );
    };

    const formatCurrency = useFormatCurrency();

    const formatDateTime = (date: string) => {
        return new Date(date).toLocaleString('pt-BR');
    };

    return (
        <AppLayout>
            <Head title="Listagem de Agendamentos" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
                <h1 className="text-3xl font-bold">Listagem de Agendamentos</h1>

                <div className="rounded-lg border bg-card p-4">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-4 mb-4">
                        <div>
                            <Label htmlFor="calendar">Agenda</Label>
                                <Select
                                value={filters.calendar?.toString() ?? ''}
                                onValueChange={(value) => {
                                    router.get(schedulingList.url({
                                        query: {
                                            calendar: value ? parseInt(value) : null,
                                            employee: filters.employee,
                                            dt_ini: filters.dt_ini,
                                            dt_fim: filters.dt_fim,
                                        },
                                    }));
                                }}
                            >
                                <SelectTrigger id="calendar">
                                    <SelectValue placeholder="Todas" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todas</SelectItem>
                                    {calendars.map((calendar) => (
                                        <SelectItem key={calendar.id} value={calendar.id.toString()}>
                                            {calendar.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <Label htmlFor="employee">Funcionário</Label>
                            <Select
                                value={filters.employee?.toString() ?? ''}
                                onValueChange={(value) => {
                                    router.get(schedulingList.url({
                                        query: {
                                            calendar: filters.calendar,
                                            employee: value ? parseInt(value) : null,
                                            dt_ini: filters.dt_ini,
                                            dt_fim: filters.dt_fim,
                                        },
                                    }));
                                }}
                            >
                                <SelectTrigger id="employee">
                                    <SelectValue placeholder="Todos" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todos</SelectItem>
                                    {employees.map((employee) => (
                                        <SelectItem key={employee.id} value={employee.id.toString()}>
                                            {employee.user?.name ?? `Funcionário #${employee.id}`}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div>
                            <Label htmlFor="dt_ini">Data Inicial</Label>
                            <Input
                                id="dt_ini"
                                type="datetime-local"
                                value={filters.dt_ini ?? ''}
                                onChange={(e) => {
                                    router.get(schedulingList.url({
                                        query: {
                                            calendar: filters.calendar,
                                            employee: filters.employee,
                                            dt_ini: e.target.value,
                                            dt_fim: filters.dt_fim,
                                        },
                                    }));
                                }}
                            />
                        </div>

                        <div>
                            <Label htmlFor="dt_fim">Data Final</Label>
                            <Input
                                id="dt_fim"
                                type="datetime-local"
                                value={filters.dt_fim ?? ''}
                                onChange={(e) => {
                                    router.get(schedulingList.url({
                                        query: {
                                            calendar: filters.calendar,
                                            employee: filters.employee,
                                            dt_ini: filters.dt_ini,
                                            dt_fim: e.target.value,
                                        },
                                    }));
                                }}
                            />
                        </div>
                    </div>

                    <div className="mb-4">
                        <Button onClick={handleMassCancel} variant="destructive" disabled={selectedIds.length === 0}>
                            Cancelar Selecionados ({selectedIds.length})
                        </Button>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full border-collapse">
                            <thead>
                                <tr className="border-b">
                                    <th className="p-2 text-left">
                                        <Checkbox
                                            checked={selectedIds.length === schedulings.data.length && schedulings.data.length > 0}
                                            onCheckedChange={(checked) => {
                                                if (checked) {
                                                    setSelectedIds(schedulings.data.map((s) => s.id));
                                                } else {
                                                    setSelectedIds([]);
                                                }
                                            }}
                                        />
                                    </th>
                                    <th className="p-2 text-left">Cliente</th>
                                    <th className="p-2 text-left">Agenda</th>
                                    <th className="p-2 text-left">Funcionário</th>
                                    <th className="p-2 text-left">Data Inicial</th>
                                    <th className="p-2 text-left">Data Final</th>
                                    <th className="p-2 text-left">Status</th>
                                    <th className="p-2 text-left">Total</th>
                                    <th className="p-2 text-left">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                {schedulings.data.map((scheduling) => (
                                    <tr key={scheduling.id} className="border-b">
                                        <td className="p-2">
                                            <Checkbox
                                                checked={selectedIds.includes(scheduling.id)}
                                                onCheckedChange={(checked) => {
                                                    if (checked) {
                                                        setSelectedIds([...selectedIds, scheduling.id]);
                                                    } else {
                                                        setSelectedIds(selectedIds.filter((id) => id !== scheduling.id));
                                                    }
                                                }}
                                            />
                                        </td>
                                        <td className="p-2">{scheduling.customer?.name ?? '-'}</td>
                                        <td className="p-2">{scheduling.calendar?.name ?? '-'}</td>
                                        <td className="p-2">{scheduling.employee?.user?.name ?? '-'}</td>
                                        <td className="p-2">{formatDateTime(scheduling.start_time)}</td>
                                        <td className="p-2">{formatDateTime(scheduling.end_time)}</td>
                                        <td className="p-2">{scheduling.status}</td>
                                        <td className="p-2">
                                            {formatCurrency(
                                                scheduling.items?.reduce(
                                                    (sum, item) => sum + item.total_amount,
                                                    0
                                                ) ?? 0
                                            )}
                                        </td>
                                        <td className="p-2">
                                            <Link
                                                href={schedulingEdit.url({ scheduling: scheduling.id })}
                                                className="text-blue-600 hover:underline"
                                            >
                                                Editar
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Paginação */}
                    {schedulings.links && (
                        <div className="mt-4 flex justify-center gap-2">
                            {schedulings.links.map((link: any, index: number) => (
                                <Link
                                    key={index}
                                    href={link.url ?? '#'}
                                    className={`px-3 py-1 rounded ${
                                        link.active
                                            ? 'bg-primary text-primary-foreground'
                                            : 'bg-muted hover:bg-muted/80'
                                    }`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

