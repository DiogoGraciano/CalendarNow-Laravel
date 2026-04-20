import { useForm } from '@inertiajs/react';
import * as yup from 'yup';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { DialogFooter } from '@/components/ui/dialog';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslation } from '@/hooks/use-translation';
import { useRef, useState } from 'react';
import { MultiSelectionModal } from '@/components/selection-modals/multi-selection-modal';

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
    user?: {
        id: number;
        name: string;
        email: string;
    };
    services?: ServiceOption[];
    calendars?: Array<{ id: number; name: string; pivot?: { is_public: boolean } }>;
    cpf_cnpj?: string;
    rg?: string;
    email?: string;
    phone?: string;
    status?: string;
    gender?: string;
    birth_date?: string;
    admission_date?: string;
    work_start_date?: string;
    work_start_time?: string;
    work_end_time?: string;
    launch_start_time?: string;
    launch_end_time?: string;
    work_days?: number[];
    work_end_date?: string;
    fired_date?: string;
    salary?: number;
    pay_day?: number;
    notes?: string;
    photo_url?: string;
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface EmployeeFormData {
    create_user?: boolean;
    user_id?: number;
    name?: string;
    email?: string;
    password?: string;
    password_confirmation?: string;
    cpf_cnpj?: string;
    rg?: string;
    phone?: string;
    photo?: File | null;
    status?: string;
    gender?: string;
    birth_date?: string;
    admission_date?: string;
    work_start_date?: string;
    work_start_time?: string;
    work_end_time?: string;
    launch_start_time?: string;
    launch_end_time?: string;
    work_days?: number[];
    work_end_date?: string;
    fired_date?: string;
    salary?: number;
    pay_day?: number;
    notes?: string;
    service_ids?: number[];
    calendar_ids?: number[];
    public_calendar_id?: number | null;
}

interface EmployeeFormProps {
    employee?: Employee | null;
    services?: ServiceOption[];
    calendars?: CalendarOption[];
    onSubmit: (formData: EmployeeFormData) => void;
    onCancel: () => void;
}

const createEmployeeSchema = (createUser: boolean) => {
    const baseSchema = {
        create_user: yup.boolean().nullable(),
        cpf_cnpj: yup.string().nullable(),
        rg: yup.string().nullable(),
        phone: yup.string().nullable(),
        status: yup.string().oneOf(['working', 'vacation', 'sick_leave', 'fired', 'resigned']).nullable(),
        gender: yup.string().oneOf(['male', 'female']).nullable(),
        birth_date: yup.date().nullable(),
        admission_date: yup.date().nullable(),
        work_start_date: yup.date().nullable(),
        work_start_time: yup.string().nullable(),
        work_end_time: yup.string().nullable(),
        launch_start_time: yup.string().nullable(),
        launch_end_time: yup.string().nullable(),
        work_days: yup.array().of(yup.number().min(0).max(6)).nullable(),
        work_end_date: yup.date().nullable(),
        fired_date: yup.date().nullable(),
        salary: yup.number().min(0).nullable(),
        pay_day: yup.number().min(1).max(31).nullable(),
        notes: yup.string().nullable(),
    };

    if (createUser) {
        return yup.object().shape({
            ...baseSchema,
            name: yup.string().required('Nome é obrigatório'),
            email: yup.string().email('Email inválido').required('Email é obrigatório'),
            password: yup.string().required('Senha é obrigatória').min(8, 'Senha deve ter no mínimo 8 caracteres'),
            password_confirmation: yup.string()
                .required('Confirmação de senha é obrigatória')
                .oneOf([yup.ref('password')], 'As senhas não coincidem'),
        });
    } else {
        return yup.object().shape({
            ...baseSchema,
            email: yup.string().email('Email inválido').nullable(),
        });
    }
};

export function EmployeeForm({ employee, services = [], calendars = [], onSubmit, onCancel }: EmployeeFormProps) {
    const { t } = useTranslation();
    const [createUser, setCreateUser] = useState(false);
    const [isServicesModalOpen, setIsServicesModalOpen] = useState(false);
    const [isCalendarsModalOpen, setIsCalendarsModalOpen] = useState(false);
    const [photoFile, setPhotoFile] = useState<File | null>(null);
    const [photoPreview, setPhotoPreview] = useState<string | null>(employee?.photo_url || null);
    const photoInputRef = useRef<HTMLInputElement>(null);

    const initialServiceIds = employee?.services?.map((s) => s.id) ?? [];
    const initialCalendarIds = employee?.calendars?.map((c) => c.id) ?? [];
    const initialPublicCalendarId = employee?.calendars?.find((c) => c.pivot?.is_public)?.id ?? null;

    const { data, setData, errors, processing, clearErrors } = useForm<EmployeeFormData>({
        create_user: createUser,
        name: employee?.user?.name || '',
        email: employee?.email || employee?.user?.email || '',
        password: '',
        password_confirmation: '',
        cpf_cnpj: employee?.cpf_cnpj || '',
        rg: employee?.rg || '',
        phone: employee?.phone || '',
        status: employee?.status || 'working',
        gender: employee?.gender || '',
        birth_date: employee?.birth_date || '',
        admission_date: employee?.admission_date || '',
        work_start_date: employee?.work_start_date || '',
        work_start_time: '',
        work_end_time: employee?.work_end_time || '',
        launch_start_time: employee?.launch_start_time || '',
        launch_end_time: employee?.launch_end_time || '',
        work_days: employee?.work_days || [],
        work_end_date: employee?.work_end_date || '',
        fired_date: employee?.fired_date || '',
        salary: employee?.salary || undefined,
        pay_day: employee?.pay_day || undefined,
        notes: employee?.notes || '',
        service_ids: initialServiceIds,
        calendar_ids: initialCalendarIds,
        public_calendar_id: initialPublicCalendarId,
    });

    const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        clearErrors();
        setValidationErrors({});

        try {
            const formData: EmployeeFormData = {
                create_user: createUser,
                cpf_cnpj: data.cpf_cnpj || undefined,
                rg: data.rg || undefined,
                phone: data.phone || undefined,
                status: data.status || 'working',
                gender: data.gender || undefined,
                birth_date: data.birth_date || undefined,
                admission_date: data.admission_date || undefined,
                work_start_date: data.work_start_date || undefined,
                work_start_time: data.work_start_time || undefined,
                work_end_time: data.work_end_time || undefined,
                launch_start_time: data.launch_start_time || undefined,
                launch_end_time: data.launch_end_time || undefined,
                work_days: data.work_days && data.work_days.length > 0 ? data.work_days : undefined,
                work_end_date: data.work_end_date || undefined,
                fired_date: data.fired_date || undefined,
                salary: data.salary || undefined,
                pay_day: data.pay_day || undefined,
                notes: data.notes || undefined,
                service_ids: data.service_ids && data.service_ids.length > 0 ? data.service_ids : undefined,
                calendar_ids: data.calendar_ids && data.calendar_ids.length > 0 ? data.calendar_ids : undefined,
                public_calendar_id: data.public_calendar_id ?? undefined,
            };

            if (createUser) {
                formData.name = data.name;
                formData.email = data.email;
                formData.password = data.password;
                formData.password_confirmation = data.password_confirmation;
            } else {
                formData.email = data.email || undefined;
            }

            formData.photo = photoFile;

            const schema = createEmployeeSchema(createUser);
            await schema.validate(formData, { abortEarly: false });
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

    const selectedServiceNames = services
        .filter((s) => data.service_ids?.includes(s.id))
        .map((s) => s.name);

    const selectedCalendarNames = calendars
        .filter((c) => data.calendar_ids?.includes(c.id))
        .map((c) => c.name);

    return (
        <form onSubmit={handleSubmit}>
            <div className="grid gap-4 py-4">
                {!employee && (
                    <div className="flex items-center space-x-2">
                        <Checkbox
                            id="create_user"
                            checked={createUser}
                            onCheckedChange={(checked) => {
                                setCreateUser(checked as boolean);
                                setData('create_user', checked as boolean);
                            }}
                        />
                        <Label htmlFor="create_user" className="cursor-pointer">
                            {t('employee.form.createUser')}
                        </Label>
                    </div>
                )}

                {createUser && !employee ? (
                    <>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="name">
                                    {t('employee.form.name')} <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="name"
                                    value={data.name || ''}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder={t('employee.form.namePlaceholder')}
                                    className={getError('name') ? 'border-red-500' : ''}
                                />
                                {getError('name') && (
                                    <p className="text-sm text-red-500">{getError('name')}</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="email">
                                    {t('employee.form.email')} <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    value={data.email || ''}
                                    onChange={(e) => setData('email', e.target.value)}
                                    placeholder={t('employee.form.emailPlaceholder')}
                                    className={getError('email') ? 'border-red-500' : ''}
                                />
                                {getError('email') && (
                                    <p className="text-sm text-red-500">{getError('email')}</p>
                                )}
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="password">
                                    {t('employee.form.password')} <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="password"
                                    type="password"
                                    value={data.password || ''}
                                    onChange={(e) => setData('password', e.target.value)}
                                    placeholder={t('employee.form.passwordPlaceholder')}
                                    className={getError('password') ? 'border-red-500' : ''}
                                />
                                {getError('password') && (
                                    <p className="text-sm text-red-500">{getError('password')}</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="password_confirmation">
                                    {t('employee.form.passwordConfirmation')} <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    value={data.password_confirmation || ''}
                                    onChange={(e) => setData('password_confirmation', e.target.value)}
                                    placeholder={t('employee.form.passwordConfirmationPlaceholder')}
                                    className={getError('password_confirmation') ? 'border-red-500' : ''}
                                />
                                {getError('password_confirmation') && (
                                    <p className="text-sm text-red-500">{getError('password_confirmation')}</p>
                                )}
                            </div>
                        </div>
                    </>
                ) : !employee ? (
                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="email">
                                {t('employee.form.email')}
                            </Label>
                            <Input
                                id="email"
                                type="email"
                                value={data.email || ''}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder={t('employee.form.emailPlaceholder')}
                                className={getError('email') ? 'border-red-500' : ''}
                            />
                            {getError('email') && (
                                <p className="text-sm text-red-500">{getError('email')}</p>
                            )}
                        </div>
                    </div>
                ) : null}

                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label htmlFor="status">
                            {t('employee.form.status')}
                        </Label>
                        <Select
                            value={data.status || 'working'}
                            onValueChange={(value) => setData('status', value)}
                        >
                            <SelectTrigger id="status">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="working">{t('employee.status.working')}</SelectItem>
                                <SelectItem value="vacation">{t('employee.status.vacation')}</SelectItem>
                                <SelectItem value="sick_leave">{t('employee.status.sickLeave')}</SelectItem>
                                <SelectItem value="fired">{t('employee.status.fired')}</SelectItem>
                                <SelectItem value="resigned">{t('employee.status.resigned')}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div className="space-y-2">
                    <Label>{t('employee.form.photo')}</Label>
                    <div className="flex items-center gap-4">
                        {photoPreview ? (
                            <img
                                src={photoPreview}
                                alt="Preview"
                                className="h-20 w-20 rounded-full object-cover border border-border"
                            />
                        ) : (
                            <div className="h-20 w-20 rounded-full bg-muted flex items-center justify-center text-muted-foreground text-xs">
                                {t('employee.form.noPhoto')}
                            </div>
                        )}
                        <div className="flex flex-col gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={() => photoInputRef.current?.click()}
                            >
                                {photoPreview ? t('employee.form.changePhoto') : t('employee.form.uploadPhoto')}
                            </Button>
                            {photoPreview && (
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => {
                                        setPhotoFile(null);
                                        setPhotoPreview(null);
                                        if (photoInputRef.current) {
                                            photoInputRef.current.value = '';
                                        }
                                    }}
                                >
                                    {t('employee.form.removePhoto')}
                                </Button>
                            )}
                        </div>
                        <input
                            ref={photoInputRef}
                            type="file"
                            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                            className="hidden"
                            onChange={(e) => {
                                const file = e.target.files?.[0] || null;
                                setPhotoFile(file);
                                if (file) {
                                    const reader = new FileReader();
                                    reader.onload = (ev) => setPhotoPreview(ev.target?.result as string);
                                    reader.readAsDataURL(file);
                                }
                            }}
                        />
                    </div>
                    {getError('photo') && (
                        <p className="text-sm text-red-500">{getError('photo')}</p>
                    )}
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label htmlFor="cpf_cnpj">
                            {t('employee.form.cpfCnpj')}
                        </Label>
                        <Input
                            id="cpf_cnpj"
                            value={data.cpf_cnpj || ''}
                            onChange={(e) => setData('cpf_cnpj', e.target.value)}
                            placeholder={t('employee.form.cpfCnpjPlaceholder')}
                        />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="rg">
                            {t('employee.form.rg')}
                        </Label>
                        <Input
                            id="rg"
                            value={data.rg || ''}
                            onChange={(e) => setData('rg', e.target.value)}
                            placeholder={t('employee.form.rgPlaceholder')}
                        />
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label htmlFor="phone">
                            {t('employee.form.phone')}
                        </Label>
                        <Input
                            id="phone"
                            value={data.phone || ''}
                            onChange={(e) => setData('phone', e.target.value)}
                            placeholder={t('employee.form.phonePlaceholder')}
                        />
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label htmlFor="gender">
                            {t('employee.form.gender')}
                        </Label>
                        <Select
                            value={data.gender || ''}
                            onValueChange={(value) => setData('gender', value)}
                        >
                            <SelectTrigger id="gender">
                                <SelectValue placeholder={t('employee.form.genderPlaceholder')} />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="male">{t('employee.gender.male')}</SelectItem>
                                <SelectItem value="female">{t('employee.gender.female')}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="birth_date">
                            {t('employee.form.birthDate')}
                        </Label>
                        <Input
                            id="birth_date"
                            type="date"
                            value={data.birth_date || ''}
                            onChange={(e) => setData('birth_date', e.target.value)}
                        />
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label htmlFor="admission_date">
                            {t('employee.form.admissionDate')}
                        </Label>
                        <Input
                            id="admission_date"
                            type="date"
                            value={data.admission_date || ''}
                            onChange={(e) => setData('admission_date', e.target.value)}
                        />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="work_start_date">
                            {t('employee.form.workStartDate')}
                        </Label>
                        <Input
                            id="work_start_date"
                            type="date"
                            value={data.work_start_date || ''}
                            onChange={(e) => setData('work_start_date', e.target.value)}
                        />
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label htmlFor="work_start_time">
                            {t('employee.form.workStartTime')}
                        </Label>
                        <Input
                            id="work_start_time"
                            type="time"
                            value={data.work_start_time || ''}
                            onChange={(e) => setData('work_start_time', e.target.value)}
                        />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="work_end_time">
                            {t('employee.form.workEndTime')}
                        </Label>
                        <Input
                            id="work_end_time"
                            type="time"
                            value={data.work_end_time || ''}
                            onChange={(e) => setData('work_end_time', e.target.value)}
                        />
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label htmlFor="launch_start_time">
                            {t('employee.form.launchStartTime')}
                        </Label>
                        <Input
                            id="launch_start_time"
                            type="time"
                            value={data.launch_start_time || ''}
                            onChange={(e) => setData('launch_start_time', e.target.value)}
                        />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="launch_end_time">
                            {t('employee.form.launchEndTime')}
                        </Label>
                        <Input
                            id="launch_end_time"
                            type="time"
                            value={data.launch_end_time || ''}
                            onChange={(e) => setData('launch_end_time', e.target.value)}
                        />
                    </div>
                </div>

                {services.length > 0 && (
                    <div className="space-y-2">
                        <Label>{t('employee.form.services')}</Label>
                        <Button
                            type="button"
                            variant="outline"
                            className="w-full justify-start text-left font-normal"
                            onClick={() => setIsServicesModalOpen(true)}
                        >
                            {selectedServiceNames.length > 0
                                ? `${selectedServiceNames.length} ${t('common.selected')}`
                                : t('employee.form.services')}
                        </Button>
                        {selectedServiceNames.length > 0 && (
                            <div className="flex flex-wrap gap-1.5">
                                {selectedServiceNames.map((name) => (
                                    <span
                                        key={name}
                                        className="inline-flex items-center rounded-md bg-muted px-2 py-1 text-xs font-medium text-muted-foreground"
                                    >
                                        {name}
                                    </span>
                                ))}
                            </div>
                        )}
                        <MultiSelectionModal
                            isOpen={isServicesModalOpen}
                            onOpenChange={setIsServicesModalOpen}
                            title={t('employee.form.services')}
                            options={services}
                            selectedIds={data.service_ids || []}
                            onConfirm={(ids) => setData('service_ids', ids)}
                            searchPlaceholder={t('common.search')}
                        />
                    </div>
                )}

                {calendars.length > 0 && (
                    <>
                        <div className="space-y-2">
                            <Label>{t('employee.form.calendars')}</Label>
                            <p className="text-sm text-muted-foreground">{t('employee.form.calendarsHint')}</p>
                            <Button
                                type="button"
                                variant="outline"
                                className="w-full justify-start text-left font-normal"
                                onClick={() => setIsCalendarsModalOpen(true)}
                            >
                                {selectedCalendarNames.length > 0
                                    ? `${selectedCalendarNames.length} ${t('common.selected')}`
                                    : t('employee.form.calendars')}
                            </Button>
                            {selectedCalendarNames.length > 0 && (
                                <div className="flex flex-wrap gap-1.5">
                                    {selectedCalendarNames.map((name) => (
                                        <span
                                            key={name}
                                            className="inline-flex items-center rounded-md bg-muted px-2 py-1 text-xs font-medium text-muted-foreground"
                                        >
                                            {name}
                                        </span>
                                    ))}
                                </div>
                            )}
                            <MultiSelectionModal
                                isOpen={isCalendarsModalOpen}
                                onOpenChange={setIsCalendarsModalOpen}
                                title={t('employee.form.calendars')}
                                options={calendars}
                                selectedIds={data.calendar_ids || []}
                                onConfirm={(ids) => {
                                    setData('calendar_ids', ids);
                                    if (data.public_calendar_id && !ids.includes(data.public_calendar_id)) {
                                        setData('public_calendar_id', ids[0] ?? null);
                                    }
                                }}
                                searchPlaceholder={t('common.search')}
                            />
                        </div>
                        {data.calendar_ids && data.calendar_ids.length > 0 && (
                            <div className="space-y-2">
                                <Label>{t('employee.form.publicCalendar')}</Label>
                                <p className="text-sm text-muted-foreground">{t('employee.form.publicCalendarHint')}</p>
                                <Select
                                    value={data.public_calendar_id?.toString() ?? ''}
                                    onValueChange={(value) => setData('public_calendar_id', value ? parseInt(value, 10) : null)}
                                >
                                    <SelectTrigger className="w-full max-w-xs">
                                        <SelectValue placeholder={t('employee.form.publicCalendarPlaceholder')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {data.calendar_ids.map((id) => {
                                            const cal = calendars.find((c) => c.id === id);
                                            return cal ? (
                                                <SelectItem key={cal.id} value={cal.id.toString()}>
                                                    {cal.name}
                                                </SelectItem>
                                            ) : null;
                                        })}
                                    </SelectContent>
                                </Select>
                            </div>
                        )}
                    </>
                )}

                <div className="space-y-2">
                    <Label>{t('employee.form.workDays')}</Label>
                    <div className="grid grid-cols-4 gap-2">
                        {[
                            { value: 0, label: t('employee.workDays.sunday') },
                            { value: 1, label: t('employee.workDays.monday') },
                            { value: 2, label: t('employee.workDays.tuesday') },
                            { value: 3, label: t('employee.workDays.wednesday') },
                            { value: 4, label: t('employee.workDays.thursday') },
                            { value: 5, label: t('employee.workDays.friday') },
                            { value: 6, label: t('employee.workDays.saturday') },
                        ].map((day) => (
                            <div key={day.value} className="flex items-center space-x-2">
                                <Checkbox
                                    id={`work_day_${day.value}`}
                                    checked={data.work_days?.includes(day.value) || false}
                                    onCheckedChange={(checked) => {
                                        const currentDays = data.work_days || [];
                                        if (checked) {
                                            setData('work_days', [...currentDays, day.value]);
                                        } else {
                                            setData('work_days', currentDays.filter((d) => d !== day.value));
                                        }
                                    }}
                                />
                                <Label htmlFor={`work_day_${day.value}`} className="cursor-pointer text-sm">
                                    {day.label}
                                </Label>
                            </div>
                        ))}
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label htmlFor="work_end_date">
                            {t('employee.form.workEndDate')}
                        </Label>
                        <Input
                            id="work_end_date"
                            type="date"
                            value={data.work_end_date || ''}
                            onChange={(e) => setData('work_end_date', e.target.value)}
                        />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="fired_date">
                            {t('employee.form.firedDate')}
                        </Label>
                        <Input
                            id="fired_date"
                            type="date"
                            value={data.fired_date || ''}
                            onChange={(e) => setData('fired_date', e.target.value)}
                        />
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                        <Label htmlFor="salary">
                            {t('employee.form.salary')}
                        </Label>
                        <Input
                            id="salary"
                            type="number"
                            step="0.01"
                            min="0"
                            value={data.salary || ''}
                            onChange={(e) => setData('salary', e.target.value ? parseFloat(e.target.value) : undefined)}
                            placeholder={t('employee.form.salaryPlaceholder')}
                        />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="pay_day">
                            {t('employee.form.payDay')}
                        </Label>
                        <Input
                            id="pay_day"
                            type="number"
                            min="1"
                            max="31"
                            value={data.pay_day || ''}
                            onChange={(e) => setData('pay_day', e.target.value ? parseInt(e.target.value) : undefined)}
                            placeholder={t('employee.form.payDayPlaceholder')}
                        />
                    </div>
                </div>

                <div className="space-y-2">
                    <Label htmlFor="notes">
                        {t('employee.form.notes')}
                    </Label>
                    <textarea
                        id="notes"
                        value={data.notes || ''}
                        onChange={(e) => setData('notes', e.target.value)}
                        placeholder={t('employee.form.notesPlaceholder')}
                        className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
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
                    {employee ? t('common.update') : t('common.create')} {t('employee.form.employee')}
                </Button>
            </DialogFooter>
        </form>
    );
}
