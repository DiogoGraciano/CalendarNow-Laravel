import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Pencil, Trash2, Plus } from 'lucide-react';
import { index as holidaysIndex, destroy as holidaysDestroy } from '@/routes/holidays';
import StoreHolidayAction from '@/actions/App/Actions/Holiday/StoreHolidayAction';
import UpdateHolidayAction from '@/actions/App/Actions/Holiday/UpdateHolidayAction';
import { useTranslation } from '@/hooks/use-translation';
import { HolidayForm } from '@/components/forms/holiday-form';
import Pagination from '@/components/pagination';
import { type BreadcrumbItem } from '@/types';

interface Holiday {
    id: number;
    name: string;
    date: string;
    recurring: boolean;
    notes?: string;
}

interface HolidaysIndexProps {
    holidays: {
        data: Holiday[];
        links: any;
        meta: any;
    };
}

export default function HolidaysIndex({ holidays }: HolidaysIndexProps) {
    const { t } = useTranslation();
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingHoliday, setEditingHoliday] = useState<Holiday | null>(null);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('holiday.title'),
            href: holidaysIndex().url,
        },
    ];

    const openCreateModal = () => {
        setEditingHoliday(null);
        setIsModalOpen(true);
    };

    const openEditModal = (holiday: Holiday) => {
        setEditingHoliday(holiday);
        setIsModalOpen(true);
    };

    const closeModal = () => {
        setIsModalOpen(false);
        setEditingHoliday(null);
    };

    const handleDelete = (id: number) => {
        if (confirm(t('common.confirm') + ' - ' + t('common.delete') + '?')) {
            router.delete(holidaysDestroy.url({ holiday: id }), {
                onSuccess: () => {
                    router.reload();
                },
            });
        }
    };

    const formatDate = (dateString: string) => {
        const date = new Date(dateString + 'T00:00:00');
        return date.toLocaleDateString('pt-BR');
    };

    const holidaysData = holidays || {
        data: [],
        links: { first: null, last: null, prev: null, next: null },
        meta: { current_page: 1, from: null, last_page: 1, path: '', per_page: 15, to: null, total: 0 },
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('holiday.list.title')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl bg-[#F2F2F2] p-4 dark:bg-neutral-950">
                {/* Header */}
                <div className="flex items-center justify-between gap-4 rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div>
                        <h1 className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">
                            {t('holiday.list.title')}
                        </h1>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex items-center justify-between gap-4 rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div className="flex-1"></div>
                    <div className="flex items-center gap-2">
                        <Button onClick={openCreateModal} size="sm">
                            <Plus className="h-4 w-4 mr-2" />
                            {t('holiday.form.new')}
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
                                        {t('holiday.list.name')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('holiday.list.date')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('holiday.list.recurring')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('holiday.list.notes')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('holiday.list.actions')}
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-neutral-200 dark:divide-neutral-700">
                                {holidaysData.data && holidaysData.data.length > 0 ? (
                                    holidaysData.data.map((holiday) => (
                                        <tr key={holiday.id} className="hover:bg-neutral-50 dark:hover:bg-neutral-800">
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {holiday.name}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {formatDate(holiday.date)}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {holiday.recurring ? t('common.yes') : t('common.no')}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {holiday.notes ? (
                                                    <span className="truncate block max-w-xs" title={holiday.notes}>
                                                        {holiday.notes}
                                                    </span>
                                                ) : '-'}
                                            </td>
                                            <td className="px-4 py-3 text-sm">
                                                <div className="flex items-center gap-2">
                                                    <button
                                                        onClick={() => openEditModal(holiday)}
                                                        className="text-green-600 hover:text-green-700"
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </button>
                                                    <button
                                                        onClick={() => handleDelete(holiday.id)}
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
                                        <td colSpan={5} className="px-4 py-8 text-center text-sm text-neutral-500">
                                            {t('holiday.list.noHolidays')}
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Pagination */}
                {holidaysData.links && holidaysData.meta && (
                    <Pagination
                        links={holidaysData.links}
                        meta={holidaysData.meta}
                        itemLabel={t('holiday.list.holidays')}
                    />
                )}

                {/* Create/Edit Modal */}
                <Dialog open={isModalOpen} onOpenChange={(open) => { if (!open) closeModal(); }}>
                    <DialogContent className="sm:max-w-lg max-h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>
                                {editingHoliday ? t('holiday.form.edit') : t('holiday.form.create')}
                            </DialogTitle>
                            <DialogDescription>
                                {editingHoliday
                                    ? t('holiday.form.editDescription')
                                    : t('holiday.form.createDescription')}
                            </DialogDescription>
                        </DialogHeader>
                        <HolidayForm
                            holiday={editingHoliday}
                            onSubmit={(formData) => {
                                if (editingHoliday) {
                                    router.put(
                                        UpdateHolidayAction.url({ holiday: editingHoliday.id }),
                                        formData,
                                        {
                                            preserveScroll: true,
                                            onStart: () => closeModal(),
                                            onSuccess: () => router.reload(),
                                            onError: () => {
                                                setIsModalOpen(true);
                                                setEditingHoliday(editingHoliday);
                                            },
                                        }
                                    );
                                } else {
                                    router.post(
                                        StoreHolidayAction.url(),
                                        formData,
                                        {
                                            preserveScroll: true,
                                            onStart: () => closeModal(),
                                            onSuccess: () => router.reload(),
                                            onError: () => {
                                                setIsModalOpen(true);
                                                setEditingHoliday(null);
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
