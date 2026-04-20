import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { Eye, EyeOff } from 'lucide-react';
import { useTranslation } from '@/hooks/use-translation';
import { loginSchema } from '@/lib/validations';
import { validateWithYup } from '@/hooks/use-yup-validation';

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
    email?: string;
}

export default function Login({
    status,
    canResetPassword,
    canRegister,
    email: initialEmail = '',
}: LoginProps) {
    const { t } = useTranslation();
    const [showPassword, setShowPassword] = useState(false);

    const { data, setData, post, processing, errors, clearErrors, setError } = useForm({
        email: initialEmail,
        password: '',
        remember: false,
    });

    const submit = async (e: React.FormEvent) => {
        e.preventDefault();
        
        // Validação client-side com Yup
        const yupErrors = await validateWithYup(loginSchema, data);
        if (yupErrors) {
            Object.keys(yupErrors).forEach((key) => {
                if (key !== 'remember') {
                    setError(key as keyof typeof data, yupErrors[key]);
                }
            });
            return;
        }
        
        clearErrors();
        
        post(store.url(), {
            onSuccess: () => {
                setData('password', '');
            },
        });
    };
    
    return (
        <AuthLayout
            title={t('auth.login.title')}
            description={t('auth.login.description')}
        >
            <Head title={t('auth.login.pageTitle')} />

            <form onSubmit={submit} className="flex flex-col gap-6">
                <div className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="email">{t('auth.login.emailLabel')}</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            required
                            autoFocus={!initialEmail}
                            tabIndex={1}
                            autoComplete="email"
                            value={data.email}
                            onChange={(e) => {
                                setData('email', e.target.value);
                                if (errors.email) {
                                    clearErrors('email');
                                }
                            }}
                            placeholder={t('auth.login.emailPlaceholder')}
                            readOnly={!!initialEmail}
                        />
                        <InputError
                            message={
                                errors.email === 'auth.failed'
                                    ? t('auth.errors.failed')
                                    : errors.email === 'auth.throttle'
                                      ? t('auth.errors.throttle', { seconds: 60 })
                                      : errors.email
                            }
                        />
                    </div>

                    <div className="grid gap-2">
                        <div className="flex items-center">
                            <Label htmlFor="password">{t('auth.login.passwordLabel')}</Label>
                            {canResetPassword && (
                                <TextLink
                                    href={request()}
                                    className="ml-auto text-sm"
                                    tabIndex={5}
                                >
                                    {t('auth.login.forgotPassword')}
                                </TextLink>
                            )}
                        </div>
                        <div className="relative">
                            <Input
                                id="password"
                                type={showPassword ? 'text' : 'password'}
                                name="password"
                                required
                                tabIndex={2}
                                autoComplete="current-password"
                                value={data.password}
                                onChange={(e) => {
                                    setData('password', e.target.value);
                                    if (errors.password) {
                                        clearErrors('password');
                                    }
                                }}
                                placeholder={t('auth.login.passwordPlaceholder')}
                                className="pr-10"
                            />
                            <button
                                type="button"
                                onClick={() => setShowPassword(!showPassword)}
                                className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                                tabIndex={-1}
                                aria-label={showPassword ? t('auth.login.hidePassword') : t('auth.login.showPassword')}
                            >
                                {showPassword ? (
                                    <EyeOff className="h-4 w-4" />
                                ) : (
                                    <Eye className="h-4 w-4" />
                                )}
                            </button>
                        </div>
                        <InputError
                            message={
                                errors.password === 'auth.password'
                                    ? t('auth.errors.password')
                                    : errors.password
                            }
                        />
                    </div>

                    <div className="flex items-center space-x-3">
                        <Checkbox
                            id="remember"
                            name="remember"
                            checked={data.remember}
                            onCheckedChange={(checked) => {
                                setData('remember', checked as boolean);
                            }}
                            tabIndex={3}
                        />
                        <Label htmlFor="remember">{t('auth.login.rememberMe')}</Label>
                    </div>

                    <Button
                        type="submit"
                        className="mt-4 w-full"
                        tabIndex={4}
                        disabled={processing}
                        data-test="login-button"
                    >
                        {processing && <Spinner />}
                        {t('auth.login.submit')}
                    </Button>
                </div>

                {canRegister && (
                    <div className="text-center text-sm text-muted-foreground">
                        {t('auth.login.noAccount')}{' '}
                        <TextLink href={register()} tabIndex={5}>
                            {t('auth.login.signUp')}
                        </TextLink>
                    </div>
                )}
            </form>

            {status && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {status}
                </div>
            )}
        </AuthLayout>
    );
}
