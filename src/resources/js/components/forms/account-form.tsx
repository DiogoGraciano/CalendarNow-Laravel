import { useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import * as yup from 'yup';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { DialogFooter } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslation } from '@/hooks/use-translation';
import { useState } from 'react';
import type { DreOption } from '@/components/selection-modals/dre-selection-modal';

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
}

interface AccountFormData {
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
}

export interface AccountFormCustomerOption {
    id: number;
    name: string;
}

interface AccountFormProps {
    account?: Account | null;
    selectedCustomer: AccountFormCustomerOption | null;
    onOpenCustomerSelection: () => void;
    selectedDre: DreOption | null;
    onOpenDreSelection: () => void;
    onSubmit: (formData: AccountFormData) => void;
    onCancel: () => void;
}

const accountSchema = yup.object().shape({
    dre_id: yup.number().required('DRE é obrigatório'),
    customer_id: yup.number().required('Cliente é obrigatório'),
    code: yup.string().nullable(),
    name: yup.string().required('Nome é obrigatório'),
    type: yup.string().oneOf(['receivable', 'payable']).required('Tipo é obrigatório'),
    type_interest: yup.string().oneOf(['fixed', 'variable']).required('Tipo de juros é obrigatório'),
    interest_rate: yup.number().nullable().min(0, 'Juros deve ser maior ou igual a zero').max(100, 'Juros deve ser menor ou igual a 100'),
    total: yup.number().required('Valor é obrigatório').min(0, 'Valor deve ser maior ou igual a zero'),
    paid: yup.number().nullable().min(0, 'Valor pago deve ser maior ou igual a zero'),
    due_date: yup.string().required('Data de vencimento é obrigatória'),
    payment_date: yup.string().nullable(),
    notes: yup.string().nullable(),
    status: yup.string().oneOf(['pending', 'paid', 'overdue', 'cancelled']).required('Status é obrigatório'),
});

function getDreDisplayLabel(dre: DreOption): string {
    if (dre.description?.trim()) {
        return `${dre.code} - ${dre.description}`;
    }
    return dre.code;
}

export function AccountForm({
    account,
    selectedCustomer,
    onOpenCustomerSelection,
    selectedDre,
    onOpenDreSelection,
    onSubmit,
    onCancel,
}: AccountFormProps) {
    const { t } = useTranslation();

    const { data, setData, errors, processing, clearErrors } = useForm<AccountFormData>({
        dre_id: account?.dre_id || 0,
        customer_id: account?.customer_id || 0,
        code: account?.code || '',
        name: account?.name || '',
        type: account?.type || 'receivable',
        type_interest: account?.type_interest || 'fixed',
        interest_rate: account?.interest_rate || 0,
        total: account?.total || 0,
        paid: account?.paid || 0,
        due_date: account?.due_date || '',
        payment_date: account?.payment_date || '',
        notes: account?.notes || '',
        status: account?.status || 'pending',
    });

    useEffect(() => {
        if (selectedCustomer?.id != null) {
            setData('customer_id', selectedCustomer.id);
        }
    }, [selectedCustomer?.id, setData]);

    useEffect(() => {
        if (selectedDre?.id != null) {
            setData('dre_id', selectedDre.id);
        }
    }, [selectedDre?.id, setData]);

    const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        clearErrors();
        setValidationErrors({});

        try {
            const formData: AccountFormData = {
                dre_id: data.dre_id,
                customer_id: data.customer_id,
                code: data.code || undefined,
                name: data.name,
                type: data.type,
                type_interest: data.type_interest,
                interest_rate: data.interest_rate || 0,
                total: data.total,
                paid: data.paid || 0,
                due_date: data.due_date,
                payment_date: data.payment_date || undefined,
                notes: data.notes || undefined,
                status: data.status,
            };

            await accountSchema.validate(formData, { abortEarly: false });
            onSubmit(formData);
        } catch (err) {
            if (err instanceof yup.ValidationError) {
                const yupErrors: Record<string, string> = {};
                err.inner.forEach((error) => {
                    if (error.path) {
                        yupErrors[error.path] = error.message;
                    }
                });
                setValidationErrors(yupErrors);
            }
        }
    };

    const getError = (field: string) => {
        return errors[field as keyof typeof errors] || validationErrors[field] || null;
    };

    return (
        <form onSubmit={handleSubmit}>
            <div className="grid gap-4 py-4">
                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label htmlFor="dre_id">
                            {t('account.form.dre')} <span className="text-red-500">*</span>
                        </Label>
                        <Button
                            id="dre_id"
                            type="button"
                            variant="outline"
                            className="w-full justify-start font-normal h-9"
                            onClick={onOpenDreSelection}
                        >
                            {selectedDre
                                ? getDreDisplayLabel(selectedDre)
                                : t('account.form.dre')}
                        </Button>
                        {getError('dre_id') && (
                            <p className="text-sm text-red-500">{getError('dre_id')}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="customer_id">
                            {t('account.form.customer')} <span className="text-red-500">*</span>
                        </Label>
                        <Button
                            id="customer_id"
                            type="button"
                            variant="outline"
                            className="w-full justify-start font-normal h-9"
                            onClick={onOpenCustomerSelection}
                        >
                            {selectedCustomer?.name ?? t('account.form.customer')}
                        </Button>
                        {getError('customer_id') && (
                            <p className="text-sm text-red-500">{getError('customer_id')}</p>
                        )}
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label htmlFor="name">
                            {t('account.form.name')} <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder={t('account.form.namePlaceholder')}
                            className={getError('name') ? 'border-red-500' : ''}
                        />
                        {getError('name') && (
                            <p className="text-sm text-red-500">{getError('name')}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="code">{t('account.form.code')}</Label>
                        <Input
                            id="code"
                            value={data.code || ''}
                            onChange={(e) => setData('code', e.target.value)}
                            placeholder={t('account.form.codePlaceholder')}
                        />
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label htmlFor="type">
                            {t('account.form.type')} <span className="text-red-500">*</span>
                        </Label>
                        <Select
                            value={data.type}
                            onValueChange={(value) => setData('type', value as 'receivable' | 'payable')}
                        >
                            <SelectTrigger className={getError('type') ? 'border-red-500' : ''}>
                                <SelectValue placeholder={t('account.form.type')} />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="receivable">{t('account.form.typeReceivable')}</SelectItem>
                                <SelectItem value="payable">{t('account.form.typePayable')}</SelectItem>
                            </SelectContent>
                        </Select>
                        {getError('type') && (
                            <p className="text-sm text-red-500">{getError('type')}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="status">
                            {t('account.form.status')} <span className="text-red-500">*</span>
                        </Label>
                        <Select
                            value={data.status}
                            onValueChange={(value) => setData('status', value as 'pending' | 'paid' | 'overdue' | 'cancelled')}
                        >
                            <SelectTrigger className={getError('status') ? 'border-red-500' : ''}>
                                <SelectValue placeholder={t('account.form.status')} />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="pending">{t('account.form.statusPending')}</SelectItem>
                                <SelectItem value="paid">{t('account.form.statusPaid')}</SelectItem>
                                <SelectItem value="overdue">{t('account.form.statusOverdue')}</SelectItem>
                                <SelectItem value="cancelled">{t('account.form.statusCancelled')}</SelectItem>
                            </SelectContent>
                        </Select>
                        {getError('status') && (
                            <p className="text-sm text-red-500">{getError('status')}</p>
                        )}
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label htmlFor="due_date">
                            {t('account.form.dueDate')} <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="due_date"
                            type="date"
                            value={data.due_date}
                            onChange={(e) => setData('due_date', e.target.value)}
                            className={getError('due_date') ? 'border-red-500' : ''}
                        />
                        {getError('due_date') && (
                            <p className="text-sm text-red-500">{getError('due_date')}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="payment_date">{t('account.form.paymentDate')}</Label>
                        <Input
                            id="payment_date"
                            type="date"
                            value={data.payment_date || ''}
                            onChange={(e) => setData('payment_date', e.target.value)}
                        />
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label htmlFor="total">
                            {t('account.form.total')} <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="total"
                            type="number"
                            step="0.01"
                            min="0"
                            value={data.total || ''}
                            onChange={(e) => setData('total', e.target.value ? parseFloat(e.target.value) : 0)}
                            placeholder="0.00"
                            className={getError('total') ? 'border-red-500' : ''}
                        />
                        {getError('total') && (
                            <p className="text-sm text-red-500">{getError('total')}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="paid">{t('account.form.paid')}</Label>
                        <Input
                            id="paid"
                            type="number"
                            step="0.01"
                            min="0"
                            value={data.paid || ''}
                            onChange={(e) => setData('paid', e.target.value ? parseFloat(e.target.value) : 0)}
                            placeholder="0.00"
                        />
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label htmlFor="type_interest">
                            {t('account.form.typeInterest')} <span className="text-red-500">*</span>
                        </Label>
                        <Select
                            value={data.type_interest}
                            onValueChange={(value) => setData('type_interest', value as 'fixed' | 'variable')}
                        >
                            <SelectTrigger className={getError('type_interest') ? 'border-red-500' : ''}>
                                <SelectValue placeholder={t('account.form.typeInterest')} />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="fixed">{t('account.form.typeInterestFixed')}</SelectItem>
                                <SelectItem value="variable">{t('account.form.typeInterestVariable')}</SelectItem>
                            </SelectContent>
                        </Select>
                        {getError('type_interest') && (
                            <p className="text-sm text-red-500">{getError('type_interest')}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="interest_rate">{t('account.form.interestRate')}</Label>
                        <Input
                            id="interest_rate"
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            value={data.interest_rate || ''}
                            onChange={(e) => setData('interest_rate', e.target.value ? parseFloat(e.target.value) : 0)}
                            placeholder="0.00"
                        />
                    </div>
                </div>

                <div className="space-y-2">
                    <Label htmlFor="notes">{t('account.form.notes')}</Label>
                    <Textarea
                        id="notes"
                        value={data.notes || ''}
                        onChange={(e) => setData('notes', e.target.value)}
                        placeholder={t('account.form.notesPlaceholder')}
                        rows={3}
                    />
                </div>
            </div>
            <DialogFooter>
                <Button
                    type="button"
                    variant="outline"
                    onClick={onCancel}
                    disabled={processing}
                >
                    {t('common.cancel')}
                </Button>
                <Button type="submit" disabled={processing} className="bg-green-600 hover:bg-green-700">
                    {account ? t('common.update') : t('common.create')} {t('account.form.account')}
                </Button>
            </DialogFooter>
        </form>
    );
}

