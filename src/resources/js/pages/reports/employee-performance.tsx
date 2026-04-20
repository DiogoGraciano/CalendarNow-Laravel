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
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useTranslation } from '@/hooks/use-translation';
import { useFormatCurrency } from '@/hooks/use-format-currency';
import { employeePerformance as employeePerformanceRoute } from '@/routes/reports';
import { index as reportsIndex } from '@/routes/reports';
import { pdf as exportPdf, excel as exportExcel } from '@/routes/reports/employee-performance';
import { Users, ArrowLeft, Printer, FileDown, FileSpreadsheet, ChevronDown } from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

interface EmployeeMetric {
    employee_id: number;
    employee_name: string;
    total_schedulings: number;
    completed_count: number;
    cancelled_count: number;
    cancellation_rate: number;
    total_revenue: number;
    avg_ticket: number;
}

interface EmployeePerformanceProps {
    employees: EmployeeMetric[];
    allEmployees: Array<{ id: number; name: string }>;
    services: Array<{ id: number; name: string }>;
    summary: {
        total_employees: number;
        total_schedulings: number;
        total_revenue: number;
        avg_ticket: number;
    };
    filters: {
        employee?: number;
        service?: number;
        dt_ini?: string;
        dt_fim?: string;
    };
}

export default function EmployeePerformanceReport({
    employees,
    allEmployees,
    services,
    summary,
    filters,
}: EmployeePerformanceProps) {
    const { t } = useTranslation();
    const formatCurrency = useFormatCurrency();
    const [localFilters, setLocalFilters] = useState({
        employee: filters.employee?.toString() || 'all',
        service: filters.service?.toString() || 'all',
        dt_ini: filters.dt_ini || '',
        dt_fim: filters.dt_fim || '',
    });

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('report.title'),
            href: reportsIndex().url,
        },
        {
            title: t('report.employeePerformance.title'),
            href: employeePerformanceRoute.url(),
        },
    ];

    const handleFilter = () => {
        router.get(employeePerformanceRoute.url(), {
            employee: localFilters.employee && localFilters.employee !== 'all' ? parseInt(localFilters.employee) : undefined,
            service: localFilters.service && localFilters.service !== 'all' ? parseInt(localFilters.service) : undefined,
            dt_ini: localFilters.dt_ini || undefined,
            dt_fim: localFilters.dt_fim || undefined,
        });
    };

    const buildExportQuery = () => ({
        query: {
            employee: localFilters.employee !== 'all' ? parseInt(localFilters.employee) : undefined,
            service: localFilters.service !== 'all' ? parseInt(localFilters.service) : undefined,
            dt_ini: localFilters.dt_ini || undefined,
            dt_fim: localFilters.dt_fim || undefined,
        },
    });

    const handlePrint = () => {
        window.open(exportPdf.url({ query: { ...buildExportQuery().query, mode: 'print' } }), '_blank');
    };

    const handleDownloadPdf = () => {
        window.open(exportPdf.url(buildExportQuery()), '_blank');
    };

    const handleDownloadExcel = () => {
        window.open(exportExcel.url(buildExportQuery()), '_blank');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('report.employeePerformance.title')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl bg-[#F2F2F2] p-4 dark:bg-neutral-950">
                {/* Header */}
                <div className="flex items-center justify-between gap-4 rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div className="flex items-center gap-2">
                        <Users className="h-5 w-5" />
                        <h1 className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">
                            {t('report.employeePerformance.title')}
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
                                    {allEmployees.map((emp) => (
                                        <SelectItem key={emp.id} value={emp.id.toString()}>
                                            {emp.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="service">{t('report.employeePerformance.service')}</Label>
                            <Select
                                value={localFilters.service}
                                onValueChange={(value) => setLocalFilters({ ...localFilters, service: value })}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder={t('report.employeePerformance.allServices')} />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('report.employeePerformance.allServices')}</SelectItem>
                                    {services.map((svc) => (
                                        <SelectItem key={svc.id} value={svc.id.toString()}>
                                            {svc.name}
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

                {/* Totals */}
                <div className="rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div>
                            <p className="text-sm text-neutral-600 dark:text-neutral-400">{t('report.employeePerformance.totalEmployees')}</p>
                            <p className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">{summary.total_employees}</p>
                        </div>
                        <div>
                            <p className="text-sm text-neutral-600 dark:text-neutral-400">{t('report.employeePerformance.totalSchedulings')}</p>
                            <p className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">{summary.total_schedulings}</p>
                        </div>
                        <div>
                            <p className="text-sm text-neutral-600 dark:text-neutral-400">{t('report.employeePerformance.averageTicket')}</p>
                            <p className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">{formatCurrency(summary.avg_ticket)}</p>
                        </div>
                        <div>
                            <p className="text-sm text-neutral-600 dark:text-neutral-400">{t('report.employeePerformance.totalRevenue')}</p>
                            <p className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">{formatCurrency(summary.total_revenue)}</p>
                        </div>
                    </div>
                </div>

                {/* Table */}
                <div className="rounded-lg bg-white shadow-sm dark:bg-neutral-900 overflow-hidden">
                    {employees.length > 0 ? (
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="bg-neutral-50 dark:bg-neutral-800">
                                    <tr>
                                        <th className="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.dre.employee')}
                                        </th>
                                        <th className="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.employeePerformance.totalSchedulings')}
                                        </th>
                                        <th className="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.employeePerformance.completedCount')}
                                        </th>
                                        <th className="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.employeePerformance.cancelledCount')}
                                        </th>
                                        <th className="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.employeePerformance.cancellationRate')}
                                        </th>
                                        <th className="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.employeePerformance.revenue')}
                                        </th>
                                        <th className="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.employeePerformance.averageTicket')}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200 dark:divide-neutral-700">
                                    {employees.map((emp) => (
                                        <tr key={emp.employee_id} className="hover:bg-neutral-50 dark:hover:bg-neutral-800">
                                            <td className="px-4 py-2 text-sm text-neutral-900 dark:text-neutral-50">
                                                {emp.employee_name}
                                            </td>
                                            <td className="px-4 py-2 text-sm text-center text-neutral-900 dark:text-neutral-50">
                                                {emp.total_schedulings}
                                            </td>
                                            <td className="px-4 py-2 text-sm text-center text-neutral-900 dark:text-neutral-50">
                                                {emp.completed_count}
                                            </td>
                                            <td className="px-4 py-2 text-sm text-center text-neutral-900 dark:text-neutral-50">
                                                {emp.cancelled_count}
                                            </td>
                                            <td className="px-4 py-2 text-sm text-center text-neutral-900 dark:text-neutral-50">
                                                {emp.cancellation_rate}%
                                            </td>
                                            <td className="px-4 py-2 text-sm text-right text-neutral-900 dark:text-neutral-50">
                                                {formatCurrency(emp.total_revenue)}
                                            </td>
                                            <td className="px-4 py-2 text-sm text-right text-neutral-900 dark:text-neutral-50">
                                                {formatCurrency(emp.avg_ticket)}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <div className="px-4 py-8 text-center text-sm text-neutral-500">
                            {t('report.employeePerformance.noData')}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
