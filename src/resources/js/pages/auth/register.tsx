import { login } from '@/routes';
import { store } from '@/routes/register';
import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { Eye, EyeOff } from 'lucide-react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { useTranslation } from '@/hooks/use-translation';
import { registerSchema } from '@/lib/validations';
import { validateWithYup } from '@/hooks/use-yup-validation';

export default function Register() {
    const { t } = useTranslation();
    const [showPassword, setShowPassword] = useState(false);
    const [showPasswordConfirmation, setShowPasswordConfirmation] = useState(false);
    
    const { data, setData, post, processing, errors, reset, setError, clearErrors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        accept_terms: '',
    });

    const submit = async (e: React.FormEvent) => {
        e.preventDefault();
        
        // Validação client-side com Yup
        const yupErrors = await validateWithYup(registerSchema, data);
        if (yupErrors) {
            Object.keys(yupErrors).forEach((key) => {
                setError(key as keyof typeof data, yupErrors[key]);
            });
            return;
        }
        
        clearErrors();
        
        post(store.url(), {
            onSuccess: () => {
                reset('password', 'password_confirmation');
            },
        });
    };
    
    return (
        <AuthLayout
            title={t('auth.register.title')}
            description={t('auth.register.description')}
        >
            <Head title={t('auth.register.pageTitle')} />
            <form onSubmit={submit} className="flex flex-col gap-6">
                <div className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="name">{t('auth.register.nameLabel')}</Label>
                        <Input
                            id="name"
                            type="text"
                            required
                            autoFocus
                            tabIndex={1}
                            autoComplete="name"
                            name="name"
                            value={data.name}
                            onChange={(e) => {
                                setData('name', e.target.value);
                                if (errors.name) {
                                    clearErrors('name');
                                }
                            }}
                            placeholder={t('auth.register.namePlaceholder')}
                        />
                        <InputError
                            message={errors.name}
                            className="mt-2"
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="email">{t('auth.register.emailLabel')}</Label>
                        <Input
                            id="email"
                            type="email"
                            required
                            tabIndex={2}
                            autoComplete="email"
                            name="email"
                            value={data.email}
                            onChange={(e) => {
                                setData('email', e.target.value);
                                if (errors.email) {
                                    clearErrors('email');
                                }
                            }}
                            placeholder={t('auth.register.emailPlaceholder')}
                        />
                        <InputError message={errors.email} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password">{t('auth.register.passwordLabel')}</Label>
                        <div className="relative">
                            <Input
                                id="password"
                                type={showPassword ? 'text' : 'password'}
                                required
                                tabIndex={3}
                                autoComplete="new-password"
                                name="password"
                                value={data.password}
                                onChange={(e) => {
                                    setData('password', e.target.value);
                                    if (errors.password) {
                                        clearErrors('password');
                                    }
                                }}
                                placeholder={t('auth.register.passwordPlaceholder')}
                                className="pr-10"
                            />
                            <button
                                type="button"
                                onClick={() => setShowPassword(!showPassword)}
                                className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                                tabIndex={-1}
                                aria-label={showPassword ? t('auth.register.hidePassword') : t('auth.register.showPassword')}
                            >
                                {showPassword ? (
                                    <EyeOff className="h-4 w-4" />
                                ) : (
                                    <Eye className="h-4 w-4" />
                                )}
                            </button>
                        </div>
                        <InputError message={errors.password} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password_confirmation">
                            {t('auth.register.confirmPasswordLabel')}
                        </Label>
                        <div className="relative">
                            <Input
                                id="password_confirmation"
                                type={showPasswordConfirmation ? 'text' : 'password'}
                                required
                                tabIndex={4}
                                autoComplete="new-password"
                                name="password_confirmation"
                                value={data.password_confirmation}
                                onChange={(e) => {
                                    setData('password_confirmation', e.target.value);
                                    if (errors.password_confirmation) {
                                        clearErrors('password_confirmation');
                                    }
                                }}
                                placeholder={t('auth.register.confirmPasswordPlaceholder')}
                                className="pr-10"
                            />
                            <button
                                type="button"
                                onClick={() => setShowPasswordConfirmation(!showPasswordConfirmation)}
                                className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                                tabIndex={-1}
                                aria-label={showPasswordConfirmation ? t('auth.register.hidePassword') : t('auth.register.showPassword')}
                            >
                                {showPasswordConfirmation ? (
                                    <EyeOff className="h-4 w-4" />
                                ) : (
                                    <Eye className="h-4 w-4" />
                                )}
                            </button>
                        </div>
                        <InputError
                            message={errors.password_confirmation}
                        />
                    </div>

                            <div className="flex items-center space-x-3">
                        <Checkbox
                            id="accept_terms"
                            name="accept_terms"
                            checked={data.accept_terms === '1'}
                            onCheckedChange={(checked) => {
                                setData('accept_terms', checked ? '1' : '');
                                if (checked) {
                                    clearErrors('accept_terms');
                                }
                            }}
                            tabIndex={5}
                        />
                        <Label htmlFor="accept_terms" className="text-sm">
                            {t('auth.register.acceptTermsLabel')}
                        </Label>
                    </div>
                    <InputError message={errors.accept_terms} />

                    <Button
                        type="submit"
                        className="mt-2 w-full"
                        tabIndex={6}
                        disabled={processing}
                        data-test="register-user-button"
                    >
                        {processing && <Spinner />}
                        {t('auth.register.submit')}
                    </Button>
                </div>

                <div className="text-center text-sm text-muted-foreground">
                    {t('auth.register.hasAccount')}{' '}
                    <TextLink href={login()} tabIndex={7}>
                        {t('auth.register.logIn')}
                    </TextLink>
                </div>
            </form>
        </AuthLayout>
    );
}
