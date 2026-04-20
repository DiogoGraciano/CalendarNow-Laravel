import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useTranslation } from '@/hooks/use-translation';
import { useFormatCurrency } from '@/hooks/use-format-currency';
import { dre as dreReport } from '@/routes/reports';
import { index as reportsIndex } from '@/routes/reports';
import { pdf as drePdf, excel as dreExcel } from '@/routes/reports/dre';
import { FileText, ArrowLeft, Printer, ChevronDown, ChevronRight, FileDown, FileSpreadsheet } from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

interface Scheduling {
    id: number;
    code: string;
    start_time: string;
    end_time: string;
    customer?: {
        id: number;
        name: string;
    };
    calendar?: {
        id: number;
        name: string;
    };
    employee?: {
        id: number;
        name: string;
    };
    total: number;
}

interface DreGroup {
    dre: {
        id: number;
        code: string;
        description: string;
        type: 'receivable' | 'payable';
    } | null;
    dre_code: string;
    dre_description: string;
    total: number;
    schedulings: Scheduling[];
}

interface DreReportProps {
    schedulingsByDre: DreGroup[];
    calendars: Array<{ id: number; name: string }>;
    employees: Array<{ id: number; name: string }>;
    totals: {
        total_agendamentos: number;
        total_geral: number;
        ticket_medio: number;
    };
    filters: {
        calendar?: number;
        employee?: number;
        dt_ini?: string;
        dt_fim?: string;
    };
}

export default function DreReport({
    schedulingsByDre,
    calendars,
    employees,
    totals,
    filters,
}: DreReportProps) {
    const { t } = useTranslation();
    const [localFilters, setLocalFilters] = useState({
        calendar: filters.calendar?.toString() || 'all',
        employee: filters.employee?.toString() || 'all',
        dt_ini: filters.dt_ini || '',
        dt_fim: filters.dt_fim || '',
    });
    const [expandedDres, setExpandedDres] = useState<Set<number | string>>(new Set());

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('report.title'),
            href: reportsIndex().url,
        },
        {
            title: t('report.dre.title'),
            href: dreReport.url(),
        },
    ];

    const formatCurrency = useFormatCurrency();

    const formatDateTime = (date: string) => {
        return new Date(date).toLocaleString('pt-BR');
    };

    const handleFilter = () => {
        router.get(dreReport.url(), {
            calendar: localFilters.calendar && localFilters.calendar !== 'all' ? parseInt(localFilters.calendar) : undefined,
            employee: localFilters.employee && localFilters.employee !== 'all' ? parseInt(localFilters.employee) : undefined,
            dt_ini: localFilters.dt_ini || undefined,
            dt_fim: localFilters.dt_fim || undefined,
        });
    };

    const buildExportQuery = () => ({
        query: {
            calendar: localFilters.calendar !== 'all' ? parseInt(localFilters.calendar) : undefined,
            employee: localFilters.employee !== 'all' ? parseInt(localFilters.employee) : undefined,
            dt_ini: localFilters.dt_ini || undefined,
            dt_fim: localFilters.dt_fim || undefined,
        },
    });

    const handlePrint = () => {
        window.open(drePdf.url({ query: { ...buildExportQuery().query, mode: 'print' } }), '_blank');
    };

    const handleDownloadPdf = () => {
        window.open(drePdf.url(buildExportQuery()), '_blank');
    };

    const handleDownloadExcel = () => {
        window.open(dreExcel.url(buildExportQuery()), '_blank');
    };

    const toggleDre = (dreKey: number | string) => {
        const newExpanded = new Set(expandedDres);
        if (newExpanded.has(dreKey)) {
            newExpanded.delete(dreKey);
        } else {
            newExpanded.add(dreKey);
        }
        setExpandedDres(newExpanded);
    };

    const getDreBadgeClass = (type?: 'receivable' | 'payable') => {
        if (!type) return 'bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200';
        return type === 'receivable'
            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('report.dre.title')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl bg-[#F2F2F2] p-4 dark:bg-neutral-950">
                {/* Header */}
                <div className="flex items-center justify-between gap-4 rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div className="flex items-center gap-2">
                        <FileText className="h-5 w-5" />
                        <h1 className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">
                            {t('report.dre.title')}
                        </h1>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => router.visit(reportsIndex.url())}
                        >
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            {t('common.back')}
                        </Button>
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="outline" size="sm">
                                    <FileDown className="h-4 w-4 mr-2" />
                                    {t('report.export')}
                                    <ChevronDown className="h-4 w-4 ml-1" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem onClick={handlePrint}>
                                    <Printer className="h-4 w-4 mr-2" />
                                    {t('report.print')}
                                </DropdownMenuItem>
                                <DropdownMenuItem onClick={handleDownloadPdf}>
                                    <FileDown className="h-4 w-4 mr-2" />
                                    {t('report.downloadPdf')}
                                </DropdownMenuItem>
                                <DropdownMenuItem onClick={handleDownloadExcel}>
                                    <FileSpreadsheet className="h-4 w-4 mr-2" />
                                    {t('report.downloadExcel')}
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>

                {/* Filters */}
                <div className="rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-5">
                        <div className="space-y-2">
                            <Label htmlFor="calendar">{t('report.dre.calendar')}</Label>
                            <Select
                                value={localFilters.calendar}
                                onValueChange={(value) => setLocalFilters({ ...localFilters, calendar: value })}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder={t('report.dre.allCalendars')} />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('report.dre.allCalendars')}</SelectItem>
                                    {calendars.map((calendar) => (
                                        <SelectItem key={calendar.id} value={calendar.id.toString()}>
                                            {calendar.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="employee">{t('report.dre.employee')}</Label>
                            <Select
                                value={localFilters.employee}
                                onValueChange={(value) => setLocalFilters({ ...localFilters, employee: value })}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder={t('report.dre.allEmployees')} />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('report.dre.allEmployees')}</SelectItem>
                                    {employees.map((employee) => (
                                        <SelectItem key={employee.id} value={employee.id.toString()}>
                                            {employee.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="dt_ini">{t('report.dre.startDate')}</Label>
                            <Input
                                id="dt_ini"
                                type="datetime-local"
                                value={localFilters.dt_ini}
                                onChange={(e) => setLocalFilters({ ...localFilters, dt_ini: e.target.value })}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="dt_fim">{t('report.dre.endDate')}</Label>
                            <Input
                                id="dt_fim"
                                type="datetime-local"
                                value={localFilters.dt_fim}
                                onChange={(e) => setLocalFilters({ ...localFilters, dt_fim: e.target.value })}
                            />
                        </div>
                        <div className="space-y-2 flex items-end">
                            <Button onClick={handleFilter} className="w-full">
                                {t('common.search')}
                            </Button>
                        </div>
                    </div>
                </div>

                {/* Report Info (Print Only) */}
                <div className="hidden print:block rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div className="space-y-2">
                        <p className="text-sm text-neutral-600 dark:text-neutral-400">
                            {t('report.generatedAt')}: {new Date().toLocaleString('pt-BR')}
                        </p>
                        <p className="text-sm text-neutral-600 dark:text-neutral-400">
                            {t('report.dre.calendar')}:{' '}
                            {localFilters.calendar && localFilters.calendar !== 'all'
                                ? calendars.find((c) => c.id.toString() === localFilters.calendar)?.name
                                : t('report.dre.allCalendars')}
                        </p>
                        <p className="text-sm text-neutral-600 dark:text-neutral-400">
                            {t('report.dre.employee')}:{' '}
                            {localFilters.employee && localFilters.employee !== 'all'
                                ? employees.find((e) => e.id.toString() === localFilters.employee)?.name
                                : t('report.dre.allEmployees')}
                        </p>
                        <p className="text-sm text-neutral-600 dark:text-neutral-400">
                            {t('report.dre.startDate')}: {localFilters.dt_ini || '-'}
                        </p>
                        <p className="text-sm text-neutral-600 dark:text-neutral-400">
                            {t('report.dre.endDate')}: {localFilters.dt_fim || '-'}
                        </p>
                    </div>
                </div>

                {/* Totals */}
                <div className="rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div className="space-y-2">
                        <p className="text-sm text-neutral-600 dark:text-neutral-400">
                            {t('report.dre.totalAppointments')}: {totals.total_agendamentos}
                        </p>
                        {totals.ticket_medio > 0 && (
                            <p className="text-sm text-neutral-600 dark:text-neutral-400">
                                {t('report.dre.averageTicket')}: {formatCurrency(totals.ticket_medio)}
                            </p>
                        )}
                        <p className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">
                            {t('report.dre.total')}: {formatCurrency(totals.total_geral)}
                        </p>
                    </div>
                </div>

                {/* DRE Groups */}
                <div className="rounded-lg bg-white shadow-sm dark:bg-neutral-900 overflow-hidden">
                    <div className="divide-y divide-neutral-200 dark:divide-neutral-700">
                        {schedulingsByDre && schedulingsByDre.length > 0 ? (
                            schedulingsByDre.map((group) => {
                                const dreKey = group.dre?.id ?? '0';
                                const isExpanded = expandedDres.has(dreKey);

                                return (
                                    <Collapsible
                                        key={dreKey}
                                        open={isExpanded}
                                        onOpenChange={() => toggleDre(dreKey)}
                                    >
                                        <div className="px-4 py-3">
                                            <CollapsibleTrigger asChild>
                                                <button className="w-full text-left">
                                                    <div className="flex items-center justify-between">
                                                        <div className="flex items-center gap-2">
                                                            {isExpanded ? (
                                                                <ChevronDown className="h-4 w-4 text-neutral-500" />
                                                            ) : (
                                                                <ChevronRight className="h-4 w-4 text-neutral-500" />
                                                            )}
                                                            <span
                                                                className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getDreBadgeClass(group.dre?.type)}`}
                                                            >
                                                                {group.dre_code} - {group.dre_description}
                                                            </span>
                                                        </div>
                                                        <div className="flex items-center gap-4">
                                                            <span className="text-sm text-neutral-600 dark:text-neutral-400">
                                                                {group.schedulings.length} {t('report.dre.schedulings')}
                                                            </span>
                                                            <span className="text-sm font-semibold text-neutral-900 dark:text-neutral-50">
                                                                {formatCurrency(group.total)}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </button>
                                            </CollapsibleTrigger>
                                            <CollapsibleContent>
                                                <div className="mt-4 ml-6">
                                                    <div className="overflow-x-auto">
                                                        <table className="w-full">
                                                            <thead className="bg-neutral-50 dark:bg-neutral-800">
                                                                <tr>
                                                                    <th className="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                                                        ID
                                                                    </th>
                                                                    <th className="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                                                        {t('report.dre.customer')}
                                                                    </th>
                                                                    <th className="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                                                        {t('report.dre.calendar')}
                                                                    </th>
                                                                    <th className="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                                                        {t('report.dre.employee')}
                                                                    </th>
                                                                    <th className="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                                                        {t('report.dre.startDate')}
                                                                    </th>
                                                                    <th className="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                                                        {t('report.dre.endDate')}
                                                                    </th>
                                                                    <th className="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                                                        {t('report.dre.total')}
                                                                    </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody className="divide-y divide-neutral-200 dark:divide-neutral-700">
                                                                {group.schedulings.map((scheduling) => (
                                                                    <tr key={scheduling.id} className="hover:bg-neutral-50 dark:hover:bg-neutral-800">
                                                                        <td className="px-4 py-2 text-sm text-neutral-900 dark:text-neutral-50">
                                                                            {scheduling.id}
                                                                        </td>
                                                                        <td className="px-4 py-2 text-sm text-neutral-900 dark:text-neutral-50">
                                                                            {scheduling.customer?.name || '-'}
                                                                        </td>
                                                                        <td className="px-4 py-2 text-sm text-neutral-900 dark:text-neutral-50">
                                                                            {scheduling.calendar?.name || '-'}
                                                                        </td>
                                                                        <td className="px-4 py-2 text-sm text-neutral-900 dark:text-neutral-50">
                                                                            {scheduling.employee?.name || '-'}
                                                                        </td>
                                                                        <td className="px-4 py-2 text-sm text-neutral-900 dark:text-neutral-50">
                                                                            {formatDateTime(scheduling.start_time)}
                                                                        </td>
                                                                        <td className="px-4 py-2 text-sm text-neutral-900 dark:text-neutral-50">
                                                                            {formatDateTime(scheduling.end_time)}
                                                                        </td>
                                                                        <td className="px-4 py-2 text-sm text-right text-neutral-900 dark:text-neutral-50">
                                                                            {formatCurrency(scheduling.total)}
                                                                        </td>
                                                                    </tr>
                                                                ))}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </CollapsibleContent>
                                        </div>
                                    </Collapsible>
                                );
                            })
                        ) : (
                            <div className="px-4 py-8 text-center text-sm text-neutral-500">
                                {t('report.dre.noData')}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
