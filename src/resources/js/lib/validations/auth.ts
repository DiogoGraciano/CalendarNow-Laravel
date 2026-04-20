import * as yup from 'yup';

export const registerSchema = yup.object({
    name: yup
        .string()
        .required('O nome é obrigatório')
        .min(3, 'O nome deve ter pelo menos 3 caracteres')
        .max(255, 'O nome não pode ter mais de 255 caracteres'),
    email: yup
        .string()
        .required('O email é obrigatório')
        .email('Email inválido')
        .max(255, 'O email não pode ter mais de 255 caracteres'),
    password: yup
        .string()
        .required('A senha é obrigatória')
        .min(8, 'A senha deve ter pelo menos 8 caracteres')
        .matches(
            /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/,
            'A senha deve conter pelo menos uma letra maiúscula, uma minúscula e um número'
        ),
    password_confirmation: yup
        .string()
        .required('A confirmação de senha é obrigatória')
        .oneOf([yup.ref('password')], 'As senhas não coincidem'),
    accept_terms: yup
        .string()
        .required('Você deve aceitar os termos de uso')
        .oneOf(['1'], 'Você deve aceitar os termos de uso'),
});

export const enterEmailSchema = yup.object({
    email: yup
        .string()
        .required('O email é obrigatório')
        .email('Email inválido')
        .max(255, 'O email não pode ter mais de 255 caracteres'),
});

export const loginSchema = yup.object({
    email: yup
        .string()
        .required('O email é obrigatório')
        .email('Email inválido'),
    password: yup
        .string()
        .required('A senha é obrigatória'),
    remember: yup
        .boolean()
        .optional(),
});

export const forgotPasswordSchema = yup.object({
    email: yup
        .string()
        .required('O email é obrigatório')
        .email('Email inválido'),
});

export const resetPasswordSchema = yup.object({
    email: yup
        .string()
        .required('O email é obrigatório')
        .email('Email inválido'),
    password: yup
        .string()
        .required('A senha é obrigatória')
        .min(8, 'A senha deve ter pelo menos 8 caracteres')
        .matches(
            /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/,
            'A senha deve conter pelo menos uma letra maiúscula, uma minúscula e um número'
        ),
    password_confirmation: yup
        .string()
        .required('A confirmação de senha é obrigatória')
        .oneOf([yup.ref('password')], 'As senhas não coincidem'),
    token: yup
        .string()
        .required('Token é obrigatório'),
});

export const confirmPasswordSchema = yup.object({
    password: yup
        .string()
        .required('A senha é obrigatória'),
});

export const twoFactorChallengeSchema = yup.object({
    code: yup.string().optional(),
    recovery_code: yup.string().optional(),
}).test('code-or-recovery', 'Informe o código de autenticação ou o código de recuperação', function(values) {
    const { code, recovery_code } = values || {};
    return !!(code || recovery_code);
});

