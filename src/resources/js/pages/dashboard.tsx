import { DailyAppointmentsChart } from '@/components/dashboard/daily-appointments-chart';
import { DailyRevenueChart } from '@/components/dashboard/daily-revenue-chart';
import { ServicesPieChart } from '@/components/dashboard/services-pie-chart';
import { StatCard } from '@/components/dashboard/stat-card';
import { TrendLineChart } from '@/components/dashboard/trend-line-chart';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { useTranslation } from '@/hooks/use-translation';
import { useFormatCurrency } from '@/hooks/use-format-currency';
import { Calendar, Bell, Wallet } from 'lucide-react';

interface DashboardProps {
    stats: {
        totalAppointments: number;
        mostUsedService: {
            name: string;
            count: number;
        };
        totalRevenue: number;
        dateRange: {
            start: string;
            end: string;
        };
    };
    charts: {
        dailyAppointments: Array<{ date: string; count: number }>;
        dailyRevenue: Array<{ date: string; total: number }>;
        servicesDistribution: Array<{ name: string; value: number }>;
        trend: Array<{ date: string; count: number }>;
    };
}

export default function Dashboard({ 
    stats = {
        totalAppointments: 0,
        mostUsedService: { name: 'Nenhum', count: 0 },
        totalRevenue: 0,
        dateRange: { start: '', end: '' },
    },
    charts = {
        dailyAppointments: [],
        dailyRevenue: [],
        servicesDistribution: [],
        trend: [],
    },
}: DashboardProps) {
    const { t } = useTranslation();
    const formatCurrency = useFormatCurrency();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('common.dashboard'),
            href: dashboard().url,
        },
    ];

    const dateRangeText = t('dashboard.stats.dateRange', {
        start: stats.dateRange.start,
        end: stats.dateRange.end,
    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('dashboard.title')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
                <h1 className="text-3xl font-bold">{t('dashboard.title')}</h1>

                {/* Cards de Estatísticas */}
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <StatCard
                        title={t('dashboard.stats.totalAppointments')}
                        value={stats.totalAppointments}
                        description={dateRangeText}
                        icon={Calendar}
                    />
                    <StatCard
                        title={t('dashboard.stats.mostUsedService')}
                        value={
                            stats.mostUsedService.name && stats.mostUsedService.count
                                ? `${stats.mostUsedService.name} (${stats.mostUsedService.count})`
                                : 'Nenhum'
                        }
                        description={dateRangeText}
                        icon={Bell}
                    />
                    <StatCard
                        title={t('dashboard.stats.totalRevenue')}
                        value={formatCurrency(stats.totalRevenue)}
                        description={dateRangeText}
                        icon={Wallet}
                    />
                </div>

                {/* Gráficos do Meio */}
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <DailyAppointmentsChart data={charts.dailyAppointments} />
                    <DailyRevenueChart data={charts.dailyRevenue} />
                    <ServicesPieChart data={charts.servicesDistribution} />
                </div>

                {/* Gráfico de Tendência */}
                <div className="grid auto-rows-min gap-4">
                    <TrendLineChart data={charts.trend} />
                </div>
            </div>
        </AppLayout>
    );
}
