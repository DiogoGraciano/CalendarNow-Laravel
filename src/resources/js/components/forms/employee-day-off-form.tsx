import { useForm } from '@inertiajs/react';
import * as yup from 'yup';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { DialogFooter } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { useTranslation } from '@/hooks/use-translation';
import { useState } from 'react';

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
}

interface EmployeeDayOffFormData {
    employee_id: number | '';
    start_date: string;
    end_date: string;
    type: string;
    reason?: string;
    notes?: string;
}

interface EmployeeDayOffFormProps {
    dayOff?: EmployeeDayOff | null;
    employees: EmployeeOption[];
    onSubmit: (formData: EmployeeDayOffFormData) => void;
    onCancel: () => void;
}

const dayOffSchema = yup.object().shape({
    employee_id: yup.number().required('Funcionário é obrigatório').min(1, 'Selecione um funcionário'),
    start_date: yup.string().required('Data início é obrigatória'),
    end_date: yup.string().required('Data fim é obrigatória'),
    type: yup.string().required('Tipo é obrigatório'),
    reason: yup.string().nullable(),
    notes: yup.string().nullable(),
});

const DAY_OFF_TYPES = ['day_off', 'vacation', 'medical_leave', 'personal', 'other'] as const;

export function EmployeeDayOffForm({ dayOff, employees, onSubmit, onCancel }: EmployeeDayOffFormProps) {
    const { t } = useTranslation();

    const { data, setData, errors, processing, clearErrors } = useForm<EmployeeDayOffFormData>({
        employee_id: dayOff?.employee_id || '',
        start_date: dayOff?.start_date ? dayOff.start_date.substring(0, 10) : '',
        end_date: dayOff?.end_date ? dayOff.end_date.substring(0, 10) : '',
        type: dayOff?.type || 'day_off',
        reason: dayOff?.reason || '',
        notes: dayOff?.notes || '',
    });

    const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        clearErrors();
        setValidationErrors({});

        try {
            const formData: EmployeeDayOffFormData = {
                employee_id: data.employee_id,
                start_date: data.start_date,
                end_date: data.end_date,
                type: data.type,
                reason: data.reason || undefined,
                notes: data.notes || undefined,
            };

            await dayOffSchema.validate(formData, { abortEarly: false });
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
                <div className="space-y-2">
                    <Label htmlFor="employee_id">
                        {t('dayOff.form.employee')} <span className="text-red-500">*</span>
                    </Label>
                    <select
                        id="employee_id"
                        value={data.employee_id}
                        onChange={(e) => setData('employee_id', e.target.value ? parseInt(e.target.value) : '')}
                        className={`flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring ${getError('employee_id') ? 'border-red-500' : ''}`}
                    >
                        <option value="">{t('dayOff.form.employeePlaceholder')}</option>
                        {employees.map((employee) => (
                            <option key={employee.id} value={employee.id}>
                                {employee.name}
                            </option>
                        ))}
                    </select>
                    {getError('employee_id') && (
                        <p className="text-sm text-red-500">{getError('employee_id')}</p>
                    )}
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label htmlFor="start_date">
                            {t('dayOff.form.startDate')} <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="start_date"
                            type="date"
                            value={data.start_date}
                            onChange={(e) => {
                                setData('start_date', e.target.value);
                                if (!data.end_date || e.target.value > data.end_date) {
                                    setData('end_date', e.target.value);
                                }
                            }}
                            className={getError('start_date') ? 'border-red-500' : ''}
                        />
                        {getError('start_date') && (
                            <p className="text-sm text-red-500">{getError('start_date')}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="end_date">
                            {t('dayOff.form.endDate')} <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="end_date"
                            type="date"
                            value={data.end_date}
                            min={data.start_date}
                            onChange={(e) => setData('end_date', e.target.value)}
                            className={getError('end_date') ? 'border-red-500' : ''}
                        />
                        {getError('end_date') && (
                            <p className="text-sm text-red-500">{getError('end_date')}</p>
                        )}
                    </div>
                </div>

                <div className="space-y-2">
                    <Label htmlFor="type">
                        {t('dayOff.form.type')} <span className="text-red-500">*</span>
                    </Label>
                    <select
                        id="type"
                        value={data.type}
                        onChange={(e) => setData('type', e.target.value)}
                        className={`flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring ${getError('type') ? 'border-red-500' : ''}`}
                    >
                        <option value="">{t('dayOff.form.typePlaceholder')}</option>
                        {DAY_OFF_TYPES.map((type) => (
                            <option key={type} value={type}>
                                {t(`dayOff.type.${type}`)}
                            </option>
                        ))}
                    </select>
                    {getError('type') && (
                        <p className="text-sm text-red-500">{getError('type')}</p>
                    )}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="reason">{t('dayOff.form.reason')}</Label>
                    <Input
                        id="reason"
                        value={data.reason || ''}
                        onChange={(e) => setData('reason', e.target.value)}
                        placeholder={t('dayOff.form.reasonPlaceholder')}
                    />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="notes">{t('dayOff.form.notes')}</Label>
                    <Textarea
                        id="notes"
                        value={data.notes || ''}
                        onChange={(e) => setData('notes', e.target.value)}
                        placeholder={t('dayOff.form.notesPlaceholder')}
                        rows={3}
                    />
                </div>
            </div>
            <DialogFooter>
                <Button type="button" variant="outline" onClick={onCancel} disabled={processing}>
                    {t('common.cancel')}
                </Button>
                <Button type="submit" disabled={processing} className="bg-green-600 hover:bg-green-700">
                    {dayOff ? t('common.update') : t('common.create')} {t('dayOff.form.dayOff')}
                </Button>
            </DialogFooter>
        </form>
    );
}
