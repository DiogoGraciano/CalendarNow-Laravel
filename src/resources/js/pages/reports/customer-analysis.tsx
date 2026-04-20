import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useTranslation } from '@/hooks/use-translation';
import { useFormatCurrency } from '@/hooks/use-format-currency';
import { customerAnalysis as customerAnalysisRoute } from '@/routes/reports';
import { index as reportsIndex } from '@/routes/reports';
import { pdf as exportPdf, excel as exportExcel } from '@/routes/reports/customer-analysis';
import { UserCheck, ArrowLeft, Printer, FileDown, FileSpreadsheet, ChevronDown } from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

interface CustomerMetric {
    customer_id: number;
    customer_name: string;
    customer_email: string | null;
    customer_phone: string | null;
    visit_count: number;
    total_spent: number;
    avg_ticket: number;
    last_visit: string | null;
}

interface CustomerAnalysisProps {
    customers: CustomerMetric[];
    newVsReturning: {
        new_customers: number;
        returning_customers: number;
    };
    summary: {
        total_customers: number;
        total_visits: number;
        total_revenue: number;
        avg_ticket: number;
    };
    filters: {
        dt_ini?: string;
        dt_fim?: string;
        min_visits?: number;
    };
}

export default function CustomerAnalysisReport({
    customers,
    newVsReturning,
    summary,
    filters,
}: CustomerAnalysisProps) {
    const { t } = useTranslation();
    const formatCurrency = useFormatCurrency();
    const [localFilters, setLocalFilters] = useState({
        dt_ini: filters.dt_ini || '',
        dt_fim: filters.dt_fim || '',
        min_visits: filters.min_visits?.toString() || '',
    });

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('report.title'),
            href: reportsIndex().url,
        },
        {
            title: t('report.customerAnalysis.title'),
            href: customerAnalysisRoute.url(),
        },
    ];

    const formatDateTime = (date: string) => {
        return new Date(date).toLocaleString('pt-BR');
    };

    const handleFilter = () => {
        router.get(customerAnalysisRoute.url(), {
            dt_ini: localFilters.dt_ini || undefined,
            dt_fim: localFilters.dt_fim || undefined,
            min_visits: localFilters.min_visits ? parseInt(localFilters.min_visits) : undefined,
        });
    };

    const buildExportQuery = () => ({
        query: {
            dt_ini: localFilters.dt_ini || undefined,
            dt_fim: localFilters.dt_fim || undefined,
            min_visits: localFilters.min_visits ? parseInt(localFilters.min_visits) : undefined,
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
            <Head title={t('report.customerAnalysis.title')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl bg-[#F2F2F2] p-4 dark:bg-neutral-950">
                {/* Header */}
                <div className="flex items-center justify-between gap-4 rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div className="flex items-center gap-2">
                        <UserCheck className="h-5 w-5" />
                        <h1 className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">
                            {t('report.customerAnalysis.title')}
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
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
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
                        <div className="space-y-2">
                            <Label htmlFor="min_visits">{t('report.customerAnalysis.minVisits')}</Label>
                            <Input
                                id="min_visits"
                                type="number"
                                min="1"
                                placeholder="1"
                                value={localFilters.min_visits}
                                onChange={(e) => setLocalFilters({ ...localFilters, min_visits: e.target.value })}
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
                            <p className="text-sm text-neutral-600 dark:text-neutral-400">{t('report.customerAnalysis.totalCustomers')}</p>
                            <p className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">{summary.total_customers}</p>
                        </div>
                        <div>
                            <p className="text-sm text-neutral-600 dark:text-neutral-400">{t('report.customerAnalysis.totalVisits')}</p>
                            <p className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">{summary.total_visits}</p>
                        </div>
                        <div>
                            <p className="text-sm text-neutral-600 dark:text-neutral-400">{t('report.customerAnalysis.averageTicket')}</p>
                            <p className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">{formatCurrency(summary.avg_ticket)}</p>
                        </div>
                        <div>
                            <p className="text-sm text-neutral-600 dark:text-neutral-400">{t('report.customerAnalysis.totalRevenue')}</p>
                            <p className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">{formatCurrency(summary.total_revenue)}</p>
                        </div>
                    </div>
                </div>

                {/* New vs Returning */}
                <div className="rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div className="flex items-center gap-4">
                        <span className="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                            {t('report.customerAnalysis.newCustomers')}: {newVsReturning.new_customers}
                        </span>
                        <span className="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {t('report.customerAnalysis.returningCustomers')}: {newVsReturning.returning_customers}
                        </span>
                    </div>
                </div>

                {/* Table */}
                <div className="rounded-lg bg-white shadow-sm dark:bg-neutral-900 overflow-hidden">
                    {customers.length > 0 ? (
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="bg-neutral-50 dark:bg-neutral-800">
                                    <tr>
                                        <th className="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.customerAnalysis.customer')}
                                        </th>
                                        <th className="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.customerAnalysis.email')}
                                        </th>
                                        <th className="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.customerAnalysis.phone')}
                                        </th>
                                        <th className="px-4 py-2 text-center text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.customerAnalysis.visitCount')}
                                        </th>
                                        <th className="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.customerAnalysis.totalSpent')}
                                        </th>
                                        <th className="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.customerAnalysis.averageTicket')}
                                        </th>
                                        <th className="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                            {t('report.customerAnalysis.lastVisit')}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-neutral-200 dark:divide-neutral-700">
                                    {customers.map((customer) => (
                                        <tr key={customer.customer_id} className="hover:bg-neutral-50 dark:hover:bg-neutral-800">
                                            <td className="px-4 py-2 text-sm text-neutral-900 dark:text-neutral-50">
                                                {customer.customer_name}
                                            </td>
                                            <td className="px-4 py-2 text-sm text-neutral-900 dark:text-neutral-50">
                                                {customer.customer_email || '-'}
                                            </td>
                                            <td className="px-4 py-2 text-sm text-neutral-900 dark:text-neutral-50">
                                                {customer.customer_phone || '-'}
                                            </td>
                                            <td className="px-4 py-2 text-sm text-center text-neutral-900 dark:text-neutral-50">
                                                {customer.visit_count}
                                            </td>
                                            <td className="px-4 py-2 text-sm text-right text-neutral-900 dark:text-neutral-50">
                                                {formatCurrency(customer.total_spent)}
                                            </td>
                                            <td className="px-4 py-2 text-sm text-right text-neutral-900 dark:text-neutral-50">
                                                {formatCurrency(customer.avg_ticket)}
                                            </td>
                                            <td className="px-4 py-2 text-sm text-neutral-900 dark:text-neutral-50">
                                                {customer.last_visit ? formatDateTime(customer.last_visit) : '-'}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <div className="px-4 py-8 text-center text-sm text-neutral-500">
                            {t('report.customerAnalysis.noData')}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
