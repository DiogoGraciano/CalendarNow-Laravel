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
import { index as servicesIndex, destroy as servicesDestroy } from '@/routes/services';
import StoreServiceAction from '@/actions/App/Actions/Service/StoreServiceAction';
import UpdateServiceAction from '@/actions/App/Actions/Service/UpdateServiceAction';
import { useTranslation } from '@/hooks/use-translation';
import { useFormatCurrency } from '@/hooks/use-format-currency';
import { ServiceForm } from '@/components/forms/service-form';
import Pagination from '@/components/pagination';
import { type BreadcrumbItem } from '@/types';

interface EmployeeOption {
    id: number;
    name: string;
}

interface Service {
    id: number;
    name: string;
    description?: string;
    price: number;
    duration?: number;
    image_url?: string;
    order?: number;
    scheduling_items_count?: number;
    employees?: EmployeeOption[];
}

interface ServicesIndexProps {
    services: {
        data: Service[];
        links: any;
        meta: any;
    };
    employees?: EmployeeOption[];
}

export default function ServicesIndex({ services, employees = [] }: ServicesIndexProps) {
    const { t } = useTranslation();
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingService, setEditingService] = useState<Service | null>(null);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('service.title'),
            href: servicesIndex().url,
        },
    ];

    const openCreateModal = () => {
        setEditingService(null);
        setIsModalOpen(true);
    };

    const openEditModal = (service: Service) => {
        setEditingService(service);
        setIsModalOpen(true);
    };

    const closeModal = () => {
        setIsModalOpen(false);
        setEditingService(null);
    };

    const handleDelete = (id: number) => {
        if (confirm(t('common.confirm') + ' - ' + t('common.delete') + '?')) {
            router.delete(servicesDestroy.url({ service: id }), {
                onSuccess: () => {
                    router.reload();
                },
            });
        }
    };

    const formatPrice = useFormatCurrency();

    const formatDuration = (duration?: number) => {
        if (!duration) return '-';
        if (duration < 60) {
            return `${duration} min`;
        }
        const hours = Math.floor(duration / 60);
        const minutes = duration % 60;
        if (minutes === 0) {
            return `${hours}h`;
        }
        return `${hours}h ${minutes}min`;
    };

    const servicesData = services || {
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
            <Head title={t('service.list.title')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl bg-[#F2F2F2] p-4 dark:bg-neutral-950">
                {/* Header */}
                <div className="flex items-center justify-between gap-4 rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div>
                        <h1 className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">
                            {t('service.list.title')}
                        </h1>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex items-center justify-between gap-4 rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div className="flex-1"></div>
                    <div className="flex items-center gap-2">
                        <Button onClick={openCreateModal} size="sm">
                            <Plus className="h-4 w-4 mr-2" />
                            {t('service.form.new')}
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
                                        {t('service.form.image')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('service.list.name')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('service.list.description')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('service.list.price')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('service.list.duration')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('service.list.order')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('service.list.agendamentos')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('service.list.actions')}
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-neutral-200 dark:divide-neutral-700">
                                {servicesData.data && servicesData.data.length > 0 ? (
                                    servicesData.data.map((service) => (
                                        <tr key={service.id} className="hover:bg-neutral-50 dark:hover:bg-neutral-800">
                                            <td className="px-4 py-3 text-sm">
                                                {service.image_url ? (
                                                    <img
                                                        src={service.image_url}
                                                        alt={service.name}
                                                        className="h-12 w-12 rounded-md object-cover"
                                                    />
                                                ) : (
                                                    <div className="h-12 w-12 rounded-md bg-neutral-200 dark:bg-neutral-700 flex items-center justify-center">
                                                        <span className="text-xs text-neutral-500">Sem imagem</span>
                                                    </div>
                                                )}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {service.name}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {service.description ? (
                                                    <span className="truncate block max-w-xs" title={service.description}>
                                                        {service.description}
                                                    </span>
                                                ) : (
                                                    '-'
                                                )}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {formatPrice(service.price)}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {formatDuration(service.duration)}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {service.order ?? '-'}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {service.scheduling_items_count ?? 0}
                                            </td>
                                            <td className="px-4 py-3 text-sm">
                                                <div className="flex items-center gap-2">
                                                    <button
                                                        onClick={() => openEditModal(service)}
                                                        className="text-green-600 hover:text-green-700"
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </button>
                                                    <button
                                                        onClick={() => handleDelete(service.id)}
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
                                        <td colSpan={8} className="px-4 py-8 text-center text-sm text-neutral-500">
                                            {t('service.list.noServices')}
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Pagination */}
                {servicesData.links && servicesData.meta && (
                    <Pagination
                        links={servicesData.links}
                        meta={servicesData.meta}
                        itemLabel={t('service.list.services')}
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
                                {editingService ? t('service.form.edit') : t('service.form.create')}
                            </DialogTitle>
                            <DialogDescription>
                                {editingService
                                    ? t('service.form.editDescription')
                                    : t('service.form.createDescription')}
                            </DialogDescription>
                        </DialogHeader>
                        <ServiceForm
                            service={editingService}
                            employees={employees}
                            onSubmit={(formData) => {
                                const formDataObj = new FormData();
                                formDataObj.append('name', formData.name);
                                if (formData.description) {
                                    formDataObj.append('description', formData.description);
                                }
                                formDataObj.append('price', formData.price.toString());
                                if (formData.duration) {
                                    formDataObj.append('duration', formData.duration.toString());
                                }
                                if (formData.order) {
                                    formDataObj.append('order', formData.order.toString());
                                }
                                if (formData.employee_ids && formData.employee_ids.length > 0) {
                                    formData.employee_ids.forEach((id) => {
                                        formDataObj.append('employee_ids[]', id.toString());
                                    });
                                }
                                if (formData.image) {
                                    formDataObj.append('image', formData.image);
                                }
                                
                                if (editingService) {
                                    formDataObj.append('_method', 'PUT');
                                    router.post(
                                        UpdateServiceAction.url({ service: editingService.id }),
                                        formDataObj,
                                        {
                                            forceFormData: true,
                                            preserveScroll: true,
                                            onStart: () => {
                                                closeModal();
                                            },
                                            onSuccess: () => {
                                                router.reload();
                                            },
                                            onError: () => {
                                                setIsModalOpen(true);
                                                setEditingService(editingService);
                                            },
                                        }
                                    );
                                } else {
                                    router.post(
                                        StoreServiceAction.url(),
                                        formDataObj,
                                        {
                                            forceFormData: true,
                                            preserveScroll: true,
                                            onStart: () => {
                                                closeModal();
                                            },
                                            onSuccess: () => {
                                                router.reload();
                                            },
                                            onError: () => {
                                                setIsModalOpen(true);
                                                setEditingService(null);
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

