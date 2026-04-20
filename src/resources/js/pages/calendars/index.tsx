import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Pencil, Trash2, Plus } from 'lucide-react';
import { index as calendarsIndex, destroy as calendarsDestroy } from '@/routes/calendars';
import StoreCalendarAction from '@/actions/App/Actions/Calendar/StoreCalendarAction';
import UpdateCalendarAction from '@/actions/App/Actions/Calendar/UpdateCalendarAction';
import { useTranslation } from '@/hooks/use-translation';
import { CalendarForm } from '@/components/forms/calendar-form';
import Pagination from '@/components/pagination';
import { type BreadcrumbItem } from '@/types';

interface Calendar {
    id: number;
    name: string;
    code?: string;
    schedulings_count?: number;
}

interface CalendarsIndexProps {
    calendars: {
        data: Calendar[];
        links: any;
        meta: any;
    };
}

export default function CalendarsIndex({ calendars }: CalendarsIndexProps) {
    const { t } = useTranslation();
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingCalendar, setEditingCalendar] = useState<Calendar | null>(null);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('calendar.title'),
            href: calendarsIndex().url,
        },
    ];

    const openCreateModal = () => {
        setEditingCalendar(null);
        setIsModalOpen(true);
    };

    const openEditModal = (calendar: Calendar) => {
        setEditingCalendar(calendar);
        setIsModalOpen(true);
    };

    const closeModal = () => {
        setIsModalOpen(false);
        setEditingCalendar(null);
    };

    const handleDelete = (id: number) => {
        if (confirm(t('common.confirm') + ' - ' + t('common.delete') + '?')) {
            router.delete(calendarsDestroy.url({ calendar: id }), {
                onSuccess: () => {
                    router.reload();
                },
            });
        }
    };

    const calendarsData = calendars || {
        data: [],
        links: {
            first: null,
            last: null,
            prev: null,
            next: null,
        },
        meta: {
            current_page: 1,
            from: null,
            last_page: 1,
            path: '',
            per_page: 15,
            to: null,
            total: 0,
        },
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('calendar.list.title')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl bg-[#F2F2F2] p-4 dark:bg-neutral-950">
                {/* Header */}
                <div className="flex items-center justify-between gap-4 rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div>
                        <h1 className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">
                            {t('calendar.list.title')}
                        </h1>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex items-center justify-between gap-4 rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div className="flex-1"></div>
                    <div className="flex items-center gap-2">
                        <Button onClick={openCreateModal} size="sm">
                            <Plus className="h-4 w-4 mr-2" />
                            {t('calendar.form.new')}
                        </Button>
                    </div>
                </div>

                {/* Table */}
                <div className="rounded-lg bg-white shadow-sm dark:bg-neutral-900 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="bg-neutral-50 dark:bg-neutral-800">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('calendar.list.name')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('calendar.list.code')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('calendar.list.agendamentos')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('calendar.list.actions')}
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-neutral-200 dark:divide-neutral-700">
                                {calendarsData.data && calendarsData.data.length > 0 ? (
                                    calendarsData.data.map((calendar) => (
                                        <tr key={calendar.id} className="hover:bg-neutral-50 dark:hover:bg-neutral-800">
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {calendar.name}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {calendar.code ?? '-'}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {calendar.schedulings_count ?? 0}
                                            </td>
                                            <td className="px-4 py-3 text-sm">
                                                <div className="flex items-center gap-2">
                                                    <button
                                                        onClick={() => openEditModal(calendar)}
                                                        className="text-green-600 hover:text-green-700"
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </button>
                                                    <button
                                                        onClick={() => handleDelete(calendar.id)}
                                                        className="text-red-600 hover:text-red-700"
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan={4} className="px-4 py-8 text-center text-sm text-neutral-500">
                                            {t('calendar.list.noCalendars')}
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Pagination */}
                {calendarsData.links && calendarsData.meta && (
                    <Pagination
                        links={calendarsData.links}
                        meta={calendarsData.meta}
                        itemLabel={t('calendar.list.calendars')}
                    />
                )}

                {/* Create/Edit Modal */}
                <Dialog open={isModalOpen} onOpenChange={(open) => {
                    if (!open) {
                        closeModal();
                    }
                }}>
                    <DialogContent className="sm:max-w-2xl max-h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>
                                {editingCalendar ? t('calendar.form.edit') : t('calendar.form.create')}
                            </DialogTitle>
                            <DialogDescription>
                                {editingCalendar
                                    ? t('calendar.form.editDescription')
                                    : t('calendar.form.createDescription')}
                            </DialogDescription>
                        </DialogHeader>
                        <CalendarForm
                            calendar={editingCalendar}
                            onSubmit={(formData) => {
                                const payload: Record<string, any> = {
                                    name: formData.name,
                                };
                                
                                if (editingCalendar) {
                                    router.put(
                                        UpdateCalendarAction.url({ calendar: editingCalendar.id }),
                                        payload,
                                        {
                                            preserveScroll: true,
                                            onStart: () => {
                                                closeModal();
                                            },
                                            onSuccess: () => {
                                                router.reload();
                                            },
                                            onError: () => {
                                                setIsModalOpen(true);
                                                setEditingCalendar(editingCalendar);
                                            },
                                        }
                                    );
                                } else {
                                    router.post(
                                        StoreCalendarAction.url(),
                                        payload,
                                        {
                                            preserveScroll: true,
                                            onStart: () => {
                                                closeModal();
                                            },
                                            onSuccess: () => {
                                                router.reload();
                                            },
                                            onError: () => {
                                                setIsModalOpen(true);
                                                setEditingCalendar(null);
                                            },
                                        }
                                    );
                                }
                            }}
                            onCancel={closeModal}
                        />
                    </DialogContent>
                </Dialog>
            </div>
        </AppLayout>
    );
}

