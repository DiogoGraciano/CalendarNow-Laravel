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
import { index as accountsIndex, destroy as accountsDestroy } from '@/routes/accounts';
import StoreAccountAction from '@/actions/App/Actions/Account/StoreAccountAction';
import UpdateAccountAction from '@/actions/App/Actions/Account/UpdateAccountAction';
import MassCancelAccountsAction from '@/actions/App/Actions/Account/MassCancelAccountsAction';
import MassPaymentAccountsAction from '@/actions/App/Actions/Account/MassPaymentAccountsAction';
import { useTranslation } from '@/hooks/use-translation';
import { useFormatCurrency } from '@/hooks/use-format-currency';
import { AccountForm } from '@/components/forms/account-form';
import { CustomerSelectionModal } from '@/components/selection-modals/customer-selection-modal';
import type { CustomerOption } from '@/components/selection-modals/customer-selection-modal';
import { DreSelectionModal } from '@/components/selection-modals/dre-selection-modal';
import type { DreOption } from '@/components/selection-modals/dre-selection-modal';
import StoreCustomerAction from '@/actions/App/Actions/Customer/StoreCustomerAction';
import StoreDreAction from '@/actions/App/Actions/Dre/StoreDreAction';
import Pagination from '@/components/pagination';
import { type BreadcrumbItem } from '@/types';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Account {
    id: number;
    dre_id: number;
    customer_id: number;
    code?: string;
    name: string;
    type: 'receivable' | 'payable';
    type_interest: 'fixed' | 'variable';
    interest_rate?: number;
    total: number;
    paid?: number;
    due_date: string;
    payment_date?: string;
    notes?: string;
    status: 'pending' | 'paid' | 'overdue' | 'cancelled';
    dre?: {
        id: number;
        code: string;
        description?: string | null;
        type?: 'receivable' | 'payable';
    };
    customer?: {
        id: number;
        name: string;
    };
}

interface AccountsIndexProps {
    accounts: {
        data: Account[];
        links: any;
        meta: any;
    };
    filters: {
        name?: string;
        due_date?: string;
        payment_date?: string;
        status: string;
    };
    totals: {
        total_a_pagar: number;
        total_a_receber: number;
        total_pago_atrasado: number;
        total_recebido_atrasado: number;
        total_pago: number;
        total_recebido: number;
    };
    dres: DreOption[];
    customers: CustomerOption[];
}

export default function AccountsIndex({ accounts, filters, totals, dres, customers }: AccountsIndexProps) {
    const { t } = useTranslation();
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [isPaymentModalOpen, setIsPaymentModalOpen] = useState(false);
    const [editingAccount, setEditingAccount] = useState<Account | null>(null);
    const [selectedIds, setSelectedIds] = useState<number[]>([]);
    const [paymentDate, setPaymentDate] = useState<string>('');
    const [isCustomerSelectionOpen, setIsCustomerSelectionOpen] = useState(false);
    const [isDreSelectionOpen, setIsDreSelectionOpen] = useState(false);
    const [selectedCustomer, setSelectedCustomer] = useState<CustomerOption | null>(null);
    const [selectedDre, setSelectedDre] = useState<DreOption | null>(null);
    const [customersList, setCustomersList] = useState<CustomerOption[]>(customers);
    const [dresList, setDresList] = useState<DreOption[]>(dres);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('account.title'),
            href: accountsIndex().url,
        },
    ];

    const [localFilters, setLocalFilters] = useState({
        name: filters.name || '',
        due_date: filters.due_date || '',
        payment_date: filters.payment_date || '',
        status: filters.status || 'all',
    });

    const openCreateModal = () => {
        setEditingAccount(null);
        setSelectedCustomer(null);
        setSelectedDre(null);
        setIsModalOpen(true);
    };

    const openEditModal = (account: Account) => {
        setEditingAccount(account);
        setSelectedCustomer(
            account.customer
                ? { id: account.customer.id, name: account.customer.name, email: null, phone: null }
                : null
        );
        setSelectedDre(
            account.dre
                ? {
                      id: account.dre.id,
                      code: account.dre.code,
                      description: account.dre.description ?? null,
                      type: account.dre.type ?? 'receivable',
                  }
                : null
        );
        setIsModalOpen(true);
    };

    const closeModal = () => {
        setIsModalOpen(false);
        setEditingAccount(null);
        setSelectedCustomer(null);
        setSelectedDre(null);
    };

    const handleSelectCustomer = (customer: CustomerOption) => {
        setSelectedCustomer(customer);
        setCustomersList((prev) =>
            prev.some((c) => c.id === customer.id) ? prev : [...prev, customer]
        );
    };

    const handleSelectDre = (dre: DreOption) => {
        setSelectedDre(dre);
        setDresList((prev) => (prev.some((d) => d.id === dre.id) ? prev : [...prev, dre]));
    };

    const handleDelete = (id: number) => {
        if (confirm(t('common.confirm') + ' - ' + t('common.delete') + '?')) {
            router.delete(accountsDestroy.url({ account: id }), {
                onSuccess: () => {
                    router.reload();
                },
            });
        }
    };

    const handleSearch = () => {
        router.get(accountsIndex.url(), localFilters, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleSelectAll = () => {
        if (selectedIds.length === accounts.data.length) {
            setSelectedIds([]);
        } else {
            setSelectedIds(accounts.data.map((account) => account.id));
        }
    };

    const handleToggleSelect = (id: number) => {
        if (selectedIds.includes(id)) {
            setSelectedIds(selectedIds.filter((selectedId) => selectedId !== id));
        } else {
            setSelectedIds([...selectedIds, id]);
        }
    };

    const handleMassCancel = () => {
        if (selectedIds.length === 0) {
            alert(t('account.list.noAccounts'));
            return;
        }

        if (confirm(t('common.confirm') + ' - ' + t('account.list.cancelAccount') + '?')) {
            router.post(
                MassCancelAccountsAction.url(),
                { ids: selectedIds },
                {
                    onSuccess: () => {
                        setSelectedIds([]);
                        router.reload();
                    },
                }
            );
        }
    };

    const handleMassPayment = () => {
        if (selectedIds.length === 0) {
            alert(t('account.list.noAccounts'));
            return;
        }

        if (!paymentDate) {
            alert(t('account.form.massPayment.paymentDate'));
            return;
        }

        if (confirm(t('common.confirm') + ' - ' + t('account.list.payAccount') + '?')) {
            router.post(
                MassPaymentAccountsAction.url(),
                { ids: selectedIds, payment_date: paymentDate },
                {
                    onSuccess: () => {
                        setSelectedIds([]);
                        setPaymentDate('');
                        setIsPaymentModalOpen(false);
                        router.reload();
                    },
                }
            );
        }
    };

    const formatPrice = useFormatCurrency();

    const formatDate = (date: string) => {
        if (!date) return '-';
        return new Date(date).toLocaleDateString('pt-BR');
    };

    const getStatusLabel = (status: string) => {
        const labels: Record<string, string> = {
            pending: t('account.form.statusPending'),
            paid: t('account.form.statusPaid'),
            overdue: t('account.form.statusOverdue'),
            cancelled: t('account.form.statusCancelled'),
        };
        return labels[status] || status;
    };

    const getStatusColor = (status: string) => {
        const colors: Record<string, string> = {
            pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            paid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            overdue: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            cancelled: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        };
        return colors[status] || '';
    };

    const getTypeLabel = (type: string) => {
        return type === 'receivable' ? t('account.form.typeReceivable') : t('account.form.typePayable');
    };

    const accountsData = accounts || {
        data: [],
        links: [],
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
            <Head title={t('account.list.title')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl bg-[#F2F2F2] p-4 dark:bg-neutral-950">
                {/* Header */}
                <div className="flex items-center justify-between gap-4 rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div>
                        <h1 className="text-lg font-semibold text-neutral-900 dark:text-neutral-50">
                            {t('account.list.title')}
                        </h1>
                    </div>
                </div>

                {/* Filters */}
                <div className="rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-5">
                        <div className="space-y-2">
                            <Label htmlFor="name">{t('account.list.name')}</Label>
                            <Input
                                id="name"
                                value={localFilters.name}
                                onChange={(e) => setLocalFilters({ ...localFilters, name: e.target.value })}
                                placeholder="Nome da conta"
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="due_date">{t('account.list.dueDate')}</Label>
                            <Input
                                id="due_date"
                                type="date"
                                value={localFilters.due_date}
                                onChange={(e) => setLocalFilters({ ...localFilters, due_date: e.target.value })}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="payment_date">{t('account.list.paymentDate')}</Label>
                            <Input
                                id="payment_date"
                                type="date"
                                value={localFilters.payment_date}
                                onChange={(e) => setLocalFilters({ ...localFilters, payment_date: e.target.value })}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="status">{t('account.list.status')}</Label>
                            <Select
                                value={localFilters.status}
                                onValueChange={(value) => setLocalFilters({ ...localFilters, status: value })}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Todos" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('common.all')}</SelectItem>
                                    <SelectItem value="pending">{t('account.form.statusPending')}</SelectItem>
                                    <SelectItem value="paid">{t('account.form.statusPaid')}</SelectItem>
                                    <SelectItem value="overdue">{t('account.form.statusOverdue')}</SelectItem>
                                    <SelectItem value="cancelled">{t('account.form.statusCancelled')}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="flex items-end">
                            <Button onClick={handleSearch} className="w-full">
                                {t('account.list.search')}
                            </Button>
                        </div>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex items-center justify-between gap-4 rounded-lg bg-white px-4 py-3 shadow-sm dark:bg-neutral-900">
                    <div className="flex items-center gap-2">
                        <Button onClick={openCreateModal} size="sm">
                            <Plus className="h-4 w-4 mr-2" />
                            {t('account.list.add')}
                        </Button>
                        <Button onClick={handleMassCancel} size="sm" variant="outline" disabled={selectedIds.length === 0}>
                            {t('account.list.cancelAccount')}
                        </Button>
                        <Button onClick={() => setIsPaymentModalOpen(true)} size="sm" variant="outline" disabled={selectedIds.length === 0}>
                            {t('account.list.payAccount')}
                        </Button>
                    </div>
                    <div className="flex items-center gap-2 text-sm text-blue-600">
                        <button onClick={handleSelectAll} className="hover:underline">
                            {selectedIds.length === accountsData.data.length ? t('account.list.deselectAll') : t('account.list.selectAll')}
                        </button>
                    </div>
                </div>

                {/* Totals */}
                <div className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
                    <div className="rounded-lg bg-red-100 px-4 py-3 text-center dark:bg-red-900">
                        <p className="text-sm font-medium text-red-800 dark:text-red-200">{t('account.list.totals.accountsToPay')}</p>
                        <p className="text-lg font-semibold text-red-900 dark:text-red-100">
                            {formatPrice(totals.total_a_pagar)}
                        </p>
                    </div>
                    <div className="rounded-lg bg-green-100 px-4 py-3 text-center dark:bg-green-900">
                        <p className="text-sm font-medium text-green-800 dark:text-green-200">{t('account.list.totals.accountsToReceive')}</p>
                        <p className="text-lg font-semibold text-green-900 dark:text-green-100">
                            {formatPrice(totals.total_a_receber)}
                        </p>
                    </div>
                    <div className="rounded-lg bg-gray-100 px-4 py-3 text-center dark:bg-gray-800">
                        <p className="text-sm font-medium text-gray-800 dark:text-gray-200">{t('account.list.totals.paidOverdue')}</p>
                        <p className="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {formatPrice(totals.total_pago_atrasado)}
                        </p>
                    </div>
                    <div className="rounded-lg bg-black px-4 py-3 text-center text-white dark:bg-neutral-900">
                        <p className="text-sm font-medium">{t('account.list.totals.receivedOverdue')}</p>
                        <p className="text-lg font-semibold">
                            {formatPrice(totals.total_recebido_atrasado)}
                        </p>
                    </div>
                    <div className="rounded-lg bg-yellow-100 px-4 py-3 text-center dark:bg-yellow-900">
                        <p className="text-sm font-medium text-yellow-800 dark:text-yellow-200">{t('account.list.totals.totalPaid')}</p>
                        <p className="text-lg font-semibold text-yellow-900 dark:text-yellow-100">
                            {formatPrice(totals.total_pago)}
                        </p>
                    </div>
                    <div className="rounded-lg bg-blue-100 px-4 py-3 text-center dark:bg-blue-900">
                        <p className="text-sm font-medium text-blue-800 dark:text-blue-200">{t('account.list.totals.totalReceived')}</p>
                        <p className="text-lg font-semibold text-blue-900 dark:text-blue-100">
                            {formatPrice(totals.total_recebido)}
                        </p>
                    </div>
                </div>

                {/* Table */}
                <div className="rounded-lg bg-white shadow-sm dark:bg-neutral-900 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="bg-neutral-50 dark:bg-neutral-800">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500 w-12">
                                        <input
                                            type="checkbox"
                                            checked={selectedIds.length === accountsData.data.length && accountsData.data.length > 0}
                                            onChange={handleSelectAll}
                                            className="rounded border-gray-300"
                                        />
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        Id
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('account.list.name')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('account.list.type')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('account.list.dueDate')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('account.list.paymentDate')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('account.list.total')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('account.list.status')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">
                                        {t('account.list.actions')}
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-neutral-200 dark:divide-neutral-700">
                                {accountsData.data && accountsData.data.length > 0 ? (
                                    accountsData.data.map((account) => (
                                        <tr key={account.id} className="hover:bg-neutral-50 dark:hover:bg-neutral-800">
                                            <td className="px-4 py-3 text-sm">
                                                <input
                                                    type="checkbox"
                                                    checked={selectedIds.includes(account.id)}
                                                    onChange={() => handleToggleSelect(account.id)}
                                                    className="rounded border-gray-300"
                                                />
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {account.id}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {account.name}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {getTypeLabel(account.type)}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {formatDate(account.due_date)}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {formatDate(account.payment_date || '')}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-neutral-900 dark:text-neutral-50">
                                                {formatPrice(account.total)}
                                            </td>
                                            <td className="px-4 py-3 text-sm">
                                                <span className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${getStatusColor(account.status)}`}>
                                                    {getStatusLabel(account.status)}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-sm">
                                                <div className="flex items-center gap-2">
                                                    <button
                                                        onClick={() => openEditModal(account)}
                                                        className="text-green-600 hover:text-green-700"
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </button>
                                                    <button
                                                        onClick={() => handleDelete(account.id)}
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
                                        <td colSpan={9} className="px-4 py-8 text-center text-sm text-neutral-500">
                                            {t('account.list.noAccounts')}
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Pagination */}
                {Array.isArray(accountsData.links) && accountsData.links.length > 0 && accountsData.meta && (
                    <Pagination
                        links={accountsData.links}
                        meta={accountsData.meta}
                        itemLabel={t('account.list.accounts')}
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
                                {editingAccount ? t('account.form.edit') : t('account.form.create')}
                            </DialogTitle>
                            <DialogDescription>
                                {editingAccount
                                    ? t('account.form.editDescription')
                                    : t('account.form.createDescription')}
                            </DialogDescription>
                        </DialogHeader>
                        <AccountForm
                            account={editingAccount}
                            selectedCustomer={selectedCustomer}
                            onOpenCustomerSelection={() => setIsCustomerSelectionOpen(true)}
                            selectedDre={selectedDre}
                            onOpenDreSelection={() => setIsDreSelectionOpen(true)}
                            onSubmit={(formData) => {
                                const data = {
                                    dre_id: formData.dre_id,
                                    customer_id: formData.customer_id,
                                    code: formData.code,
                                    name: formData.name,
                                    type: formData.type,
                                    type_interest: formData.type_interest,
                                    interest_rate: formData.interest_rate,
                                    total: formData.total,
                                    paid: formData.paid,
                                    due_date: formData.due_date,
                                    payment_date: formData.payment_date,
                                    notes: formData.notes,
                                    status: formData.status,
                                };
                                
                                if (editingAccount) {
                                    router.put(
                                        UpdateAccountAction.url({ account: editingAccount.id }),
                                        data,
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
                                                setEditingAccount(editingAccount);
                                            },
                                        }
                                    );
                                } else {
                                    router.post(
                                        StoreAccountAction.url(),
                                        data,
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
                                                setEditingAccount(null);
                                            },
                                        }
                                    );
                                }
                            }}
                            onCancel={closeModal}
                        />
                    </DialogContent>
                </Dialog>

                <CustomerSelectionModal
                    isOpen={isCustomerSelectionOpen}
                    onOpenChange={setIsCustomerSelectionOpen}
                    selectedCustomerId={selectedCustomer?.id ?? null}
                    onSelectCustomer={handleSelectCustomer}
                    customers={customersList}
                    storeCustomerUrl={StoreCustomerAction.url()}
                />

                <DreSelectionModal
                    isOpen={isDreSelectionOpen}
                    onOpenChange={setIsDreSelectionOpen}
                    selectedDreId={selectedDre?.id ?? null}
                    onSelectDre={handleSelectDre}
                    dres={dresList}
                    storeDreUrl={StoreDreAction.url()}
                />

                {/* Payment Modal */}
                <Dialog open={isPaymentModalOpen} onOpenChange={(open) => {
                    if (!open) {
                        setIsPaymentModalOpen(false);
                        setPaymentDate('');
                    }
                }}>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>{t('account.form.massPayment.title')}</DialogTitle>
                            <DialogDescription>
                                {t('account.form.massPayment.description')}
                            </DialogDescription>
                        </DialogHeader>
                        <div className="space-y-4 py-4">
                            <div className="space-y-2">
                                <Label htmlFor="payment_date_modal">{t('account.form.massPayment.paymentDate')}</Label>
                                <Input
                                    id="payment_date_modal"
                                    type="date"
                                    value={paymentDate}
                                    onChange={(e) => setPaymentDate(e.target.value)}
                                    required
                                />
                            </div>
                        </div>
                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => {
                                    setIsPaymentModalOpen(false);
                                    setPaymentDate('');
                                }}
                            >
                                {t('common.cancel')}
                            </Button>
                            <Button onClick={handleMassPayment} className="bg-green-600 hover:bg-green-700">
                                {t('account.list.payAccount')}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </AppLayout>
    );
}

