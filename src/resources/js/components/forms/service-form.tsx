import { useForm } from '@inertiajs/react';
import * as yup from 'yup';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { DialogFooter } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { useTranslation } from '@/hooks/use-translation';
import { useState } from 'react';
import { MultiSelectionModal } from '@/components/selection-modals/multi-selection-modal';

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
    employees?: EmployeeOption[];
}

interface ServiceFormData {
    name: string;
    description?: string;
    price: number;
    duration?: number;
    image?: File | null;
    order?: number;
    employee_ids?: number[];
}

interface ServiceFormProps {
    service?: Service | null;
    employees?: EmployeeOption[];
    onSubmit: (formData: ServiceFormData) => void;
    onCancel: () => void;
}

const serviceSchema = yup.object().shape({
    name: yup.string().required('Nome é obrigatório'),
    description: yup.string().nullable(),
    price: yup.number().required('Preço é obrigatório').min(0, 'Preço deve ser maior ou igual a zero'),
    duration: yup.number().nullable().min(1, 'Duração deve ser maior que zero'),
    image: yup.mixed<File>().nullable(),
    order: yup.number().nullable().min(0, 'Ordem deve ser maior ou igual a zero'),
});

export function ServiceForm({ service, employees = [], onSubmit, onCancel }: ServiceFormProps) {
    const { t } = useTranslation();
    const [isEmployeesModalOpen, setIsEmployeesModalOpen] = useState(false);

    const initialEmployeeIds = service?.employees?.map((e) => e.id) ?? [];

    const { data, setData, errors, processing, clearErrors } = useForm<ServiceFormData>({
        name: service?.name || '',
        description: service?.description || '',
        price: service?.price || 0,
        duration: service?.duration || undefined,
        image: null,
        order: service?.order || undefined,
        employee_ids: initialEmployeeIds,
    });

    const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});
    const [imagePreview, setImagePreview] = useState<string | null>(service?.image_url || null);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        clearErrors();
        setValidationErrors({});

        try {
            const formData: ServiceFormData = {
                name: data.name,
                description: data.description || undefined,
                price: data.price,
                duration: data.duration || undefined,
                image: data.image || null,
                order: data.order || undefined,
                employee_ids: data.employee_ids && data.employee_ids.length > 0 ? data.employee_ids : undefined,
            };

            await serviceSchema.validate(formData, { abortEarly: false });
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
                        <Label htmlFor="name">
                            {t('service.form.name')} <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder={t('service.form.namePlaceholder')}
                            className={getError('name') ? 'border-red-500' : ''}
                        />
                        {getError('name') && (
                            <p className="text-sm text-red-500">{getError('name')}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="price">
                            {t('service.form.price')} <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="price"
                            type="number"
                            step="0.01"
                            min="0"
                            value={data.price || ''}
                            onChange={(e) => setData('price', e.target.value ? parseFloat(e.target.value) : 0)}
                            placeholder={t('service.form.pricePlaceholder')}
                            className={getError('price') ? 'border-red-500' : ''}
                        />
                        {getError('price') && (
                            <p className="text-sm text-red-500">{getError('price')}</p>
                        )}
                    </div>
                </div>

                <div className="space-y-2">
                    <Label htmlFor="description">
                        {t('service.form.description')}
                    </Label>
                    <Textarea
                        id="description"
                        value={data.description || ''}
                        onChange={(e) => setData('description', e.target.value)}
                        placeholder={t('service.form.descriptionPlaceholder')}
                        rows={3}
                    />
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label htmlFor="duration">
                            {t('service.form.duration')}
                        </Label>
                        <Input
                            id="duration"
                            type="number"
                            min="1"
                            value={data.duration || ''}
                            onChange={(e) => setData('duration', e.target.value ? parseInt(e.target.value) : undefined)}
                            placeholder={t('service.form.durationPlaceholder')}
                            className={getError('duration') ? 'border-red-500' : ''}
                        />
                        {getError('duration') && (
                            <p className="text-sm text-red-500">{getError('duration')}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="order">
                            {t('service.form.order')}
                        </Label>
                        <Input
                            id="order"
                            type="number"
                            min="0"
                            value={data.order || ''}
                            onChange={(e) => setData('order', e.target.value ? parseInt(e.target.value) : undefined)}
                            placeholder={t('service.form.orderPlaceholder')}
                            className={getError('order') ? 'border-red-500' : ''}
                        />
                        {getError('order') && (
                            <p className="text-sm text-red-500">{getError('order')}</p>
                        )}
                    </div>
                </div>

                {employees.length > 0 && (
                    <div className="space-y-2">
                        <Label>{t('service.form.employees')}</Label>
                        <Button
                            type="button"
                            variant="outline"
                            className="w-full justify-start text-left font-normal"
                            onClick={() => setIsEmployeesModalOpen(true)}
                        >
                            {(data.employee_ids?.length ?? 0) > 0
                                ? `${data.employee_ids!.length} ${t('common.selected')}`
                                : t('service.form.employees')}
                        </Button>
                        {(data.employee_ids?.length ?? 0) > 0 && (
                            <div className="flex flex-wrap gap-1.5">
                                {employees
                                    .filter((e) => data.employee_ids?.includes(e.id))
                                    .map((e) => (
                                        <span
                                            key={e.id}
                                            className="inline-flex items-center rounded-md bg-muted px-2 py-1 text-xs font-medium text-muted-foreground"
                                        >
                                            {e.name}
                                        </span>
                                    ))}
                            </div>
                        )}
                        <MultiSelectionModal
                            isOpen={isEmployeesModalOpen}
                            onOpenChange={setIsEmployeesModalOpen}
                            title={t('service.form.employees')}
                            options={employees}
                            selectedIds={data.employee_ids || []}
                            onConfirm={(ids) => setData('employee_ids', ids)}
                            searchPlaceholder={t('common.search')}
                        />
                    </div>
                )}

                <div className="space-y-2">
                    <Label htmlFor="image">
                        {t('service.form.image')}
                    </Label>
                    <Input
                        id="image"
                        type="file"
                        accept="image/*"
                        onChange={(e) => {
                            const file = e.target.files?.[0] || null;
                            setData('image', file);
                            if (file) {
                                const reader = new FileReader();
                                reader.onloadend = () => {
                                    setImagePreview(reader.result as string);
                                };
                                reader.readAsDataURL(file);
                            } else {
                                setImagePreview(service?.image_url || null);
                            }
                        }}
                        className={getError('image') ? 'border-red-500' : ''}
                    />
                    {getError('image') && (
                        <p className="text-sm text-red-500">{getError('image')}</p>
                    )}
                    {imagePreview && (
                        <div className="mt-2">
                            <img
                                src={imagePreview}
                                alt="Preview"
                                className="h-32 w-32 rounded-md object-cover"
                            />
                        </div>
                    )}
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
                    {service ? t('common.update') : t('common.create')} {t('service.form.service')}
                </Button>
            </DialogFooter>
        </form>
    );
}

