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

interface Dre {
    id: number;
    code: string;
    description?: string;
    type: 'receivable' | 'payable';
}

export interface DreFormData {
    code: string;
    description?: string;
    type: 'receivable' | 'payable';
}

interface DreFormProps {
    dre?: Dre | null;
    onSubmit: (formData: DreFormData) => void;
    onCancel: () => void;
    processing?: boolean;
}

const dreSchema = yup.object().shape({
    code: yup.string().required('Código é obrigatório'),
    description: yup.string().nullable(),
    type: yup.string().oneOf(['receivable', 'payable'], 'Tipo inválido').required('Tipo é obrigatório'),
});

export function DreForm({ dre, onSubmit, onCancel, processing: processingProp }: DreFormProps) {
    const { t } = useTranslation();

    const { data, setData, errors, processing, clearErrors } = useForm<DreFormData>({
        code: dre?.code || '',
        description: dre?.description || '',
        type: dre?.type || 'receivable',
    });

    const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        clearErrors();
        setValidationErrors({});

        try {
            const formData: DreFormData = {
                code: data.code,
                description: data.description || undefined,
                type: data.type,
            };

            await dreSchema.validate(formData, { abortEarly: false });
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
                        <Label htmlFor="code">
                            {t('dre.form.code')} <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="code"
                            value={data.code}
                            onChange={(e) => setData('code', e.target.value)}
                            placeholder={t('dre.form.codePlaceholder')}
                            className={getError('code') ? 'border-red-500' : ''}
                        />
                        {getError('code') && (
                            <p className="text-sm text-red-500">{getError('code')}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="type">
                            {t('dre.form.type')} <span className="text-red-500">*</span>
                        </Label>
                        <Select
                            value={data.type}
                            onValueChange={(value: 'receivable' | 'payable') => setData('type', value)}
                        >
                            <SelectTrigger className={getError('type') ? 'border-red-500' : ''}>
                                <SelectValue placeholder={t('dre.form.typePlaceholder')} />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="receivable">{t('dre.form.receivable')}</SelectItem>
                                <SelectItem value="payable">{t('dre.form.payable')}</SelectItem>
                            </SelectContent>
                        </Select>
                        {getError('type') && (
                            <p className="text-sm text-red-500">{getError('type')}</p>
                        )}
                    </div>
                </div>

                <div className="space-y-2">
                    <Label htmlFor="description">
                        {t('dre.form.description')}
                    </Label>
                    <Textarea
                        id="description"
                        value={data.description || ''}
                        onChange={(e) => setData('description', e.target.value)}
                        placeholder={t('dre.form.descriptionPlaceholder')}
                        rows={3}
                    />
                </div>
            </div>
            <DialogFooter>
                <Button
                    type="button"
                    variant="outline"
                    onClick={onCancel}
                    disabled={processingProp ?? processing}
                >
                    {t('common.cancel')}
                </Button>
                <Button type="submit" disabled={processingProp ?? processing} className="bg-green-600 hover:bg-green-700">
                    {dre ? t('common.update') : t('common.create')} {t('dre.form.dre')}
                </Button>
            </DialogFooter>
        </form>
    );
}

