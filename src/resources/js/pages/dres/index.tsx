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
import { index as dresIndex, destroy as dresDestroy } from '@/routes/dres/index';
import StoreDreAction from '@/actions/App/Actions/Dre/StoreDreAction';
import UpdateDreAction from '@/actions/App/Actions/Dre/UpdateDreAction';
import { useTranslation } from '@/hooks/use-translation';
import { DreForm } from '@/components/forms/dre-form';
import Pagination from '@/components/pagination';
import { type BreadcrumbItem } from '@/types';

interface Dre {
    id: number;
    code: string;
    description?: string;
    type: 'receivable' | 'payable';
}

interface DresIndexProps {
    dres: {
        data: Dre[];
        links: any;
        meta: any;
    };
}

export default function DresIndex({ dres }: DresIndexProps) {
    const { t } = useTranslation();
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingDre, setEditingDre] = useState<Dre | null>(null);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('dre.title'),
            href: dresIndex().url,
        },
    ];

    const openCreateModal = () => {
        setEditingDre(null);
        setIsModalOpen(true);
    };

    const openEditModal = (dre: Dre) => {
        setEditingDre(dre);
        setIsModalOpen(true);
    };

    const closeModal = () => {
        setIsModalOpen(false);
        setEditingDre(null);
    };

    const handleDelete = (id: number) => {
        if (confirm(t('common.confirm') + ' - ' + t('common.delete') + '?')) {
            router.delete(dresDestroy.url({ dre: id }), {
                onSuccess: () => {
                    router.reload();
                },
            });
        }
    };

    const getTypeLabel = (type: 'receivable' | 'payable') => {
        return type === 'receivable' ? t('dre.form.receivable') : t('dre.form.payable');
    };

    const getTypeBadgeClass = (type: 'receivable' | 'payable') => {
        return type === 'receivable'
            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
    };

    const dresData = dres || {
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
            <Head title={t('dre.list.title')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl bg-[#F2F2F2] p-4 dark:bg-neutral-950">
                {/* Header */}
                <div className="flex items-center justify-between gap-4 rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div>
                        <h1 className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">
                            {t('dre.list.title')}
                        </h1>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex items-center justify-between gap-4 rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div className="flex-1"></div>
                    <div className="flex items-center gap-2">
                        <Button onClick={openCreateModal} size="sm">
                            <Plus className="h-4 w-4 mr-2" />
                            {t('dre.form.new')}
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
                                        {t('dre.list.code')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('dre.list.description')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('dre.list.type')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('dre.list.actions')}
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-neutral-200 dark:divide-neutral-700">
                                {dresData.data && dresData.data.length > 0 ? (
                                    dresData.data.map((dre) => (
                                        <tr key={dre.id} className="hover:bg-neutral-50 dark:hover:bg-neutral-800">
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {dre.code}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {dre.description ? (
                                                    <span className="truncate block max-w-xs" title={dre.description}>
                                                        {dre.description}
                                                    </span>
                                                ) : (
                                                    '-'
                                                )}
                                            </td>
                                            <td className="px-4 py-3 text-sm">
                                                <span
                                                    className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getTypeBadgeClass(dre.type)}`}
                                                >
                                                    {getTypeLabel(dre.type)}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-sm">
                                                <div className="flex items-center gap-2">
                                                    <button
                                                        onClick={() => openEditModal(dre)}
                                                        className="text-green-600 hover:text-green-700"
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </button>
                                                    <button
                                                        onClick={() => handleDelete(dre.id)}
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
                                            {t('dre.list.noDres')}
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Pagination */}
                {dresData.links && dresData.meta && (
                    <Pagination
                        links={dresData.links}
                        meta={dresData.meta}
                        itemLabel={t('dre.list.dres')}
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
                                {editingDre ? t('dre.form.edit') : t('dre.form.create')}
                            </DialogTitle>
                            <DialogDescription>
                                {editingDre
                                    ? t('dre.form.editDescription')
                                    : t('dre.form.createDescription')}
                            </DialogDescription>
                        </DialogHeader>
                        <DreForm
                            dre={editingDre}
                            onSubmit={(formData) => {
                                if (editingDre) {
                                    router.put(
                                        UpdateDreAction.url({ dre: editingDre.id }),
                                        formData,
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
                                                setEditingDre(editingDre);
                                            },
                                        }
                                    );
                                } else {
                                    router.post(
                                        StoreDreAction.url(),
                                        formData,
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
                                                setEditingDre(null);
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

