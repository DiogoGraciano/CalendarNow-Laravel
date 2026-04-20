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
import { index as employeeDaysOffIndex, destroy as employeeDaysOffDestroy } from '@/routes/employee-days-off';
import StoreEmployeeDayOffAction from '@/actions/App/Actions/EmployeeDayOff/StoreEmployeeDayOffAction';
import UpdateEmployeeDayOffAction from '@/actions/App/Actions/EmployeeDayOff/UpdateEmployeeDayOffAction';
import { useTranslation } from '@/hooks/use-translation';
import { EmployeeDayOffForm } from '@/components/forms/employee-day-off-form';
import Pagination from '@/components/pagination';
import { type BreadcrumbItem } from '@/types';

interface EmployeeOption {
    id: number;
    name: string;
}

interface EmployeeDayOff {
    id: number;
    employee_id: number;
    start_date: string;
    end_date: string;
    type: string;
    reason?: string;
    notes?: string;
    employee?: {
        id: number;
        email?: string;
        user?: { name: string } | null;
    };
}

interface EmployeeDaysOffIndexProps {
    daysOff: {
        data: EmployeeDayOff[];
        links: any;
        meta: any;
    };
    employees: EmployeeOption[];
    selectedEmployeeId?: string | null;
}

export default function EmployeeDaysOffIndex({ daysOff, employees = [], selectedEmployeeId }: EmployeeDaysOffIndexProps) {
    const { t } = useTranslation();
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingDayOff, setEditingDayOff] = useState<EmployeeDayOff | null>(null);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('dayOff.title'),
            href: employeeDaysOffIndex().url,
        },
    ];

    const openCreateModal = () => {
        setEditingDayOff(null);
        setIsModalOpen(true);
    };

    const openEditModal = (dayOff: EmployeeDayOff) => {
        setEditingDayOff(dayOff);
        setIsModalOpen(true);
    };

    const closeModal = () => {
        setIsModalOpen(false);
        setEditingDayOff(null);
    };

    const handleDelete = (id: number) => {
        if (confirm(t('common.confirm') + ' - ' + t('common.delete') + '?')) {
            router.delete(employeeDaysOffDestroy.url({ employeeDayOff: id }), {
                onSuccess: () => {
                    router.reload();
                },
            });
        }
    };

    const handleEmployeeFilter = (employeeId: string) => {
        router.get(employeeDaysOffIndex().url, employeeId ? { employee_id: employeeId } : {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const formatDate = (dateString: string) => {
        const date = new Date(dateString + 'T00:00:00');
        return date.toLocaleDateString('pt-BR');
    };

    const getEmployeeName = (dayOff: EmployeeDayOff) => {
        return dayOff.employee?.user?.name ?? dayOff.employee?.email ?? `Funcionário #${dayOff.employee_id}`;
    };

    const daysOffData = daysOff || {
        data: [],
        links: { first: null, last: null, prev: null, next: null },
        meta: { current_page: 1, from: null, last_page: 1, path: '', per_page: 15, to: null, total: 0 },
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('dayOff.list.title')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl bg-[#F2F2F2] p-4 dark:bg-neutral-950">
                {/* Header */}
                <div className="flex items-center justify-between gap-4 rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div>
                        <h1 className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">
                            {t('dayOff.list.title')}
                        </h1>
                    </div>
                </div>

                {/* Actions + Filter */}
                <div className="flex items-center justify-between gap-4 rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div className="flex items-center gap-2">
                        <select
                            value={selectedEmployeeId || ''}
                            onChange={(e) => handleEmployeeFilter(e.target.value)}
                            className="flex h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        >
                            <option value="">{t('dayOff.list.allEmployees')}</option>
                            {employees.map((employee) => (
                                <option key={employee.id} value={employee.id}>
                                    {employee.name}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button onClick={openCreateModal} size="sm">
                            <Plus className="h-4 w-4 mr-2" />
                            {t('dayOff.form.new')}
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
                                        {t('dayOff.list.employee')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('dayOff.list.startDate')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('dayOff.list.endDate')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('dayOff.list.type')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('dayOff.list.reason')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('dayOff.list.actions')}
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-neutral-200 dark:divide-neutral-700">
                                {daysOffData.data && daysOffData.data.length > 0 ? (
                                    daysOffData.data.map((dayOff) => (
                                        <tr key={dayOff.id} className="hover:bg-neutral-50 dark:hover:bg-neutral-800">
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {getEmployeeName(dayOff)}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {formatDate(dayOff.start_date)}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {formatDate(dayOff.end_date)}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {t(`dayOff.type.${dayOff.type}`)}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {dayOff.reason ? (
                                                    <span className="truncate block max-w-xs" title={dayOff.reason}>
                                                        {dayOff.reason}
                                                    </span>
                                                ) : '-'}
                                            </td>
                                            <td className="px-4 py-3 text-sm">
                                                <div className="flex items-center gap-2">
                                                    <button
                                                        onClick={() => openEditModal(dayOff)}
                                                        className="text-green-600 hover:text-green-700"
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </button>
                                                    <button
                                                        onClick={() => handleDelete(dayOff.id)}
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
                                        <td colSpan={6} className="px-4 py-8 text-center text-sm text-neutral-500">
                                            {t('dayOff.list.noDaysOff')}
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Pagination */}
                {daysOffData.links && daysOffData.meta && (
                    <Pagination
                        links={daysOffData.links}
                        meta={daysOffData.meta}
                        itemLabel={t('dayOff.list.daysOff')}
                    />
                )}

                {/* Create/Edit Modal */}
                <Dialog open={isModalOpen} onOpenChange={(open) => { if (!open) closeModal(); }}>
                    <DialogContent className="sm:max-w-lg max-h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>
                                {editingDayOff ? t('dayOff.form.edit') : t('dayOff.form.create')}
                            </DialogTitle>
                            <DialogDescription>
                                {editingDayOff
                                    ? t('dayOff.form.editDescription')
                                    : t('dayOff.form.createDescription')}
                            </DialogDescription>
                        </DialogHeader>
                        <EmployeeDayOffForm
                            dayOff={editingDayOff}
                            employees={employees}
                            onSubmit={(formData) => {
                                if (editingDayOff) {
                                    router.put(
                                        UpdateEmployeeDayOffAction.url({ employeeDayOff: editingDayOff.id }),
                                        formData,
                                        {
                                            preserveScroll: true,
                                            onStart: () => closeModal(),
                                            onSuccess: () => router.reload(),
                                            onError: () => {
                                                setIsModalOpen(true);
                                                setEditingDayOff(editingDayOff);
                                            },
                                        }
                                    );
                                } else {
                                    router.post(
                                        StoreEmployeeDayOffAction.url(),
                                        formData,
                                        {
                                            preserveScroll: true,
                                            onStart: () => closeModal(),
                                            onSuccess: () => router.reload(),
                                            onError: () => {
                                                setIsModalOpen(true);
                                                setEditingDayOff(null);
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
