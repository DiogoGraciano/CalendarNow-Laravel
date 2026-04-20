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
import { serviceAnalysis as serviceAnalysisRoute } from '@/routes/reports';
import { index as reportsIndex } from '@/routes/reports';
import { pdf as exportPdf, excel as exportExcel } from '@/routes/reports/service-analysis';
import { Package, ArrowLeft, Printer, FileDown, FileSpreadsheet, ChevronDown } from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

interface ServiceMetric {
    service_id: number;
    service_name: string;
    catalog_price: number;
    times_booked: number;
    total_revenue: number;
    avg_price: number;
    total_discount: number;
    revenue_share: number;
}

interface ServiceAnalysisProps {
    services: ServiceMetric[];
    calendars: Array<{ id: number; name: string }>;
    employees: Array<{ id: number; name: string }>;
    summary: {
        total_services: number;
        total_bookings: number;
        total_revenue: number;
        avg_price: number;
    };
    filters: {
        calendar?: number;
        employee?: number;
        dt_ini?: string;
        dt_fim?: string;
    };
}

export default function ServiceAnalysisReport({
    services,
    calendars,
    employees,
    summary,
    filters,
}: ServiceAnalysisProps) {
    const { t } = useTranslation();
    const formatCurrency = useFormatCurrency();
    const [localFilters, setLocalFilters] = useState({
        calendar: filters.calendar?.toString() || 'all',
        employee: filters.employee?.toString() || 'all',
        dt_ini: filters.dt_ini || '',
        dt_fim: filters.dt_fim || '',
    });

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('report.title'),
            href: reportsIndex().url,
        },
        {
            title: t('report.serviceAnalysis.title'),
            href: serviceAnalysisRoute.url(),
        },
    ];

    const handleFilter = () => {
        router.get(serviceAnalysisRoute.url(), {
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
            <Head title={t('report.serviceAnalysis.title')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl bg-[#F2F2F2] p-4 dark:bg-neutral-950">
                {/* Header */}
                <div className="flex items-center justify-between gap-4 rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div className="flex items-center gap-2">
                        <Package className="h-5 w-5" />
                        <h1 className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">
                            {t('report.serviceAnalysis.title')}
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

                {/* Totals */}
                <div className="rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div>
                            <p className="text-sm text-neutral-600 dark:text-neutral-400">{t('report.serviceAnalysis.totalServices')}</p>
                            <p className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">{summary.total_services}</p>
                        </div>
                        <div>
                            <p className="text-sm text-neutral-600 dark:text-neutral-400">{t('report.serviceAnalysis.totalBookings')}</p>
                            <p className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">{summary.total_bookings}</p>
                        </div>
                        <div>
                            <p className="text-sm text-neutral-600 dark:text-neutral-400">{t('report.serviceAnalysis.avgPrice')}</p>
                            <p className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">{formatCurrency(summary.avg_price)}</p>
                        </div>
                        <div>
                            <p className="text-sm text-neutral-600 dark:text-neutral-400">{t('report.serviceAnalysis.totalRevenue')}</p>
                            <p className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">{formatCurrency(summary.total_revenue)}</p>
                        </div>
                    </div>
                </div>

                {/* Table */}
                <div className="rounded-lg bg-white shadow-sm dark:bg-neutral-900 overflow-hidden">
                    {services.length > 0 ? (
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="bg-neutral-50 dark:bg-neutral-800">
                                    <tr>
                                        <th className="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.serviceAnalysis.service')}
                                        </th>
                                        <th className="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.serviceAnalysis.catalogPrice')}
                                        </th>
                                        <th className="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.serviceAnalysis.timesBooked')}
                                        </th>
                                        <th className="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.serviceAnalysis.totalRevenue')}
                                        </th>
                                        <th className="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.serviceAnalysis.avgPrice')}
                                        </th>
                                        <th className="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.serviceAnalysis.totalDiscount')}
                                        </th>
                                        <th className="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.serviceAnalysis.revenueShare')}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200 dark:divide-neutral-700">
                                    {services.map((svc) => (
                                        <tr key={svc.service_id} className="hover:bg-neutral-50 dark:hover:bg-neutral-800">
                                            <td className="px-4 py-2 text-sm text-neutral-900 dark:text-neutral-50">
                                                {svc.service_name}
                                            </td>
                                            <td className="px-4 py-2 text-sm text-right text-neutral-900 dark:text-neutral-50">
                                                {formatCurrency(svc.catalog_price)}
                                            </td>
                                            <td className="px-4 py-2 text-sm text-center text-neutral-900 dark:text-neutral-50">
                                                {svc.times_booked}
                                            </td>
                                            <td className="px-4 py-2 text-sm text-right text-neutral-900 dark:text-neutral-50">
                                                {formatCurrency(svc.total_revenue)}
                                            </td>
                                            <td className="px-4 py-2 text-sm text-right text-neutral-900 dark:text-neutral-50">
                                                {formatCurrency(svc.avg_price)}
                                            </td>
                                            <td className="px-4 py-2 text-sm text-right text-neutral-900 dark:text-neutral-50">
                                                {formatCurrency(svc.total_discount)}
                                            </td>
                                            <td className="px-4 py-2 text-sm text-center text-neutral-900 dark:text-neutral-50">
                                                {svc.revenue_share}%
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <div className="px-4 py-8 text-center text-sm text-neutral-500">
                            {t('report.serviceAnalysis.noData')}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
