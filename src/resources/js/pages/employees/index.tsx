import { useState, useEffect } from 'react';
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
import { index as employeesIndex, destroy as employeesDestroy } from '@/routes/employees';
import StoreEmployeeAction from '@/actions/App/Actions/Employee/StoreEmployeeAction';
import UpdateEmployeeAction from '@/actions/App/Actions/Employee/UpdateEmployeeAction';
import { useTranslation } from '@/hooks/use-translation';
import { EmployeeForm } from '@/components/forms/employee-form';
import Pagination from '@/components/pagination';
import { type BreadcrumbItem } from '@/types';

interface User {
    id: number;
    name: string;
    email: string;
}

interface ServiceOption {
    id: number;
    name: string;
}

interface CalendarOption {
    id: number;
    name: string;
}

interface Employee {
    id: number;
    user_id?: number;
    user?: User;
    services?: ServiceOption[];
    calendars?: Array<{ id: number; name: string; pivot?: { is_public: boolean } }>;
    cpf_cnpj?: string;
    email?: string;
    phone?: string;
    status?: string;
    schedulings_count?: number;
}

interface EmployeesIndexProps {
    employees: {
        data: Employee[];
        links: any;
        meta: any;
    };
    services?: ServiceOption[];
    calendars?: CalendarOption[];
    shouldOpenCreateModal?: boolean;
    employeeToEdit?: Employee | null;
}

export default function EmployeesIndex({ employees, services = [], calendars = [], shouldOpenCreateModal = false, employeeToEdit = null }: EmployeesIndexProps) {
    const { t } = useTranslation();
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingEmployee, setEditingEmployee] = useState<Employee | null>(null);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('employee.title'),
            href: employeesIndex().url,
        },
    ];

    const openCreateModalHandler = () => {
        setEditingEmployee(null);
        setIsModalOpen(true);
    };

    const openCreateModal = () => {
        openCreateModalHandler();
    };

    const openEditModal = (employee: Employee) => {
        setEditingEmployee(employee);
        setIsModalOpen(true);
    };

    // Abrir modal automaticamente quando necessário
    useEffect(() => {
        if (shouldOpenCreateModal) {
            openCreateModalHandler();
        } else if (employeeToEdit) {
            openEditModal(employeeToEdit);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [shouldOpenCreateModal, employeeToEdit]);

    const closeModal = () => {
        setIsModalOpen(false);
        setEditingEmployee(null);
    };

    const handleDelete = (id: number) => {
        if (confirm(t('common.confirm') + ' - ' + t('common.delete') + '?')) {
            router.delete(employeesDestroy.url({ employee: id }), {
                onSuccess: () => {
                    router.reload();
                },
            });
        }
    };

    const getStatusLabel = (status?: string) => {
        if (!status) return '-';
        const statusMap: Record<string, string> = {
            working: t('employee.status.working'),
            vacation: t('employee.status.vacation'),
            sick_leave: t('employee.status.sickLeave'),
            fired: t('employee.status.fired'),
            resigned: t('employee.status.resigned'),
        };
        return statusMap[status] || status;
    };

    const employeesData = employees || {
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
            <Head title={t('employee.list.title')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl bg-[#F2F2F2] p-4 dark:bg-neutral-950">
                {/* Header */}
                <div className="flex items-center justify-between gap-4 rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div>
                        <h1 className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">
                            {t('employee.list.title')}
                        </h1>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex items-center justify-between gap-4 rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div className="flex-1"></div>
                    <div className="flex items-center gap-2">
                        <Button onClick={openCreateModal} size="sm">
                            <Plus className="h-4 w-4 mr-2" />
                            {t('employee.form.new')}
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
                                        {t('employee.list.name')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('employee.list.email')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('employee.list.phone')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('employee.list.status')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('employee.list.agendamentos')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('employee.list.actions')}
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-neutral-200 dark:divide-neutral-700">
                                {employeesData.data && employeesData.data.length > 0 ? (
                                    employeesData.data.map((employee) => (
                                        <tr key={employee.id} className="hover:bg-neutral-50 dark:hover:bg-neutral-800">
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {employee.user?.name ?? '-'}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {employee.email ?? employee.user?.email ?? '-'}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {employee.phone ?? '-'}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {getStatusLabel(employee.status)}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {employee.schedulings_count ?? 0}
                                            </td>
                                            <td className="px-4 py-3 text-sm">
                                                <div className="flex items-center gap-2">
                                                    <button
                                                        onClick={() => openEditModal(employee)}
                                                        className="text-green-600 hover:text-green-700"
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </button>
                                                    <button
                                                        onClick={() => handleDelete(employee.id)}
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
                                            {t('employee.list.noEmployees')}
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Pagination */}
                {employeesData.links && employeesData.meta && (
                    <Pagination
                        links={employeesData.links}
                        meta={employeesData.meta}
                        itemLabel={t('employee.list.employees')}
                    />
                )}

                {/* Create/Edit Modal */}
                <Dialog open={isModalOpen} onOpenChange={(open) => {
                    if (!open) {
                        closeModal();
                    }
                }}>
                    <DialogContent className="sm:max-w-4xl max-h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>
                                {editingEmployee ? t('employee.form.edit') : t('employee.form.create')}
                            </DialogTitle>
                            <DialogDescription>
                                {editingEmployee
                                    ? t('employee.form.editDescription')
                                    : t('employee.form.createDescription')}
                            </DialogDescription>
                        </DialogHeader>
                        <EmployeeForm
                            employee={editingEmployee}
                            services={services}
                            calendars={calendars}
                            onSubmit={(formData) => {
                                const payload: Record<string, any> = {
                                    create_user: formData.create_user || false,
                                    cpf_cnpj: formData.cpf_cnpj || null,
                                    rg: formData.rg || null,
                                    phone: formData.phone || null,
                                    status: formData.status || 'working',
                                    gender: formData.gender || null,
                                    birth_date: formData.birth_date || null,
                                    admission_date: formData.admission_date || null,
                                    work_start_date: formData.work_start_date || null,
                                    work_start_time: formData.work_start_time || null,
                                    work_end_time: formData.work_end_time || null,
                                    launch_start_time: formData.launch_start_time || null,
                                    launch_end_time: formData.launch_end_time || null,
                                    work_days: formData.work_days && formData.work_days.length > 0 ? formData.work_days : null,
                                    work_end_date: formData.work_end_date || null,
                                    fired_date: formData.fired_date || null,
                                    salary: formData.salary || null,
                                    pay_day: formData.pay_day || null,
                                    notes: formData.notes || null,
                                    service_ids: formData.service_ids && formData.service_ids.length > 0 ? formData.service_ids : [],
                                    calendar_ids: formData.calendar_ids && formData.calendar_ids.length > 0 ? formData.calendar_ids : [],
                                    public_calendar_id: formData.public_calendar_id ?? null,
                                };

                                if (formData.photo) {
                                    payload.photo = formData.photo;
                                }

                                // Se criar usuário, adicionar campos de usuário
                                if (formData.create_user) {
                                    payload.name = formData.name;
                                    payload.email = formData.email;
                                    payload.password = formData.password;
                                    payload.password_confirmation = formData.password_confirmation;
                                } else {
                                    payload.email = formData.email || null;
                                }

                                if (editingEmployee) {
                                    router.post(
                                        UpdateEmployeeAction.url({ employee: editingEmployee.id }),
                                        { ...payload, _method: 'put' },
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
                                                setEditingEmployee(editingEmployee);
                                            },
                                        }
                                    );
                                } else {
                                    router.post(
                                        StoreEmployeeAction.url(),
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
                                                setEditingEmployee(null);
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

