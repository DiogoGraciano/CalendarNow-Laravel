import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useTranslation } from '@/hooks/use-translation';
import { FileText, Users, Package, UserCheck } from 'lucide-react';
import { dre as dreReport, employeePerformance, serviceAnalysis, customerAnalysis } from '@/routes/reports';
import { index as reportsIndex } from '@/routes/reports';
import { type BreadcrumbItem } from '@/types';

export default function ReportsIndex() {
    const { t } = useTranslation();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('report.title'),
            href: reportsIndex().url,
        },
    ];

    const reports = [
        {
            id: 'dre',
            title: t('report.dre.title'),
            description: t('report.dre.description'),
            href: dreReport.url(),
            icon: FileText,
        },
        {
            id: 'employee-performance',
            title: t('report.employeePerformance.title'),
            description: t('report.employeePerformance.description'),
            href: employeePerformance.url(),
            icon: Users,
        },
        {
            id: 'service-analysis',
            title: t('report.serviceAnalysis.title'),
            description: t('report.serviceAnalysis.description'),
            href: serviceAnalysis.url(),
            icon: Package,
        },
        {
            id: 'customer-analysis',
            title: t('report.customerAnalysis.title'),
            description: t('report.customerAnalysis.description'),
            href: customerAnalysis.url(),
            icon: UserCheck,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('report.title')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl bg-[#F2F2F2] p-4 dark:bg-neutral-950">
                <div className="flex items-center justify-between gap-4 rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div>
                        <h1 className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">
                            {t('report.title')}
                        </h1>
                    </div>
                </div>

                {reports.length === 0 ? (
                    <div className="text-center py-8 text-muted-foreground rounded-lg bg-white dark:bg-neutral-900">
                        {t('report.noReports')}
                    </div>
                ) : (
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        {reports.map((report) => {
                            const Icon = report.icon;
                            return (
                                <Card key={report.id} className="hover:shadow-lg transition-shadow">
                                    <CardHeader>
                                        <div className="flex items-center gap-2">
                                            <Icon className="h-5 w-5" />
                                            <CardTitle>{report.title}</CardTitle>
                                        </div>
                                        <CardDescription>{report.description}</CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <Link
                                            href={report.href}
                                            className="inline-block w-full"
                                        >
                                            <button className="w-full rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                                                {t('report.view')}
                                            </button>
                                        </Link>
                                    </CardContent>
                                </Card>
                            );
                        })}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
