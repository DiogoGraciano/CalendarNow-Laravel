import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { index as schedulingIndex } from '@/routes/scheduling';
import { index as scheduleIndex } from '@/routes/schedule';
import { type BreadcrumbItem } from '@/types';
import { useTranslation } from '@/hooks/use-translation';

interface Calendar {
    id: number;
    name: string;
    code?: string;
    schedulings_count?: number;
}

interface ScheduleIndexProps {
    calendars: Calendar[];
}

export default function ScheduleIndex({ calendars }: ScheduleIndexProps) {
    const { t } = useTranslation();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('schedule.title'),
            href: scheduleIndex().url,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Agendas Disponíveis" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
                <h1 className="text-3xl font-bold">Agendas Disponíveis</h1>

                {calendars.length === 0 ? (
                    <div className="text-center py-8 text-muted-foreground">
                        Nenhuma agenda disponível
                    </div>
                ) : (
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        {calendars.map((calendar) => (
                            <Card key={calendar.id} className="hover:shadow-lg transition-shadow">
                                <CardHeader>
                                    <CardTitle>{calendar.name}</CardTitle>
                                    {calendar.code && (
                                        <CardDescription>Código: {calendar.code}</CardDescription>
                                    )}
                                </CardHeader>
                                <CardContent>
                                    <p className="text-sm text-muted-foreground mb-4">
                                        {calendar.schedulings_count ?? 0} agendamento(s)
                                    </p>
                                    <Link
                                        href={schedulingIndex.url({
                                            calendar: calendar.id,
                                        })}
                                        className="inline-block"
                                    >
                                        <button className="w-full rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                                            Acessar Agenda
                                        </button>
                                    </Link>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

