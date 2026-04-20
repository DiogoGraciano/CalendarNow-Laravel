import { ObjectSchema, ValidationError } from 'yup';

/**
 * Valida dados usando um schema Yup e retorna erros formatados
 */
export async function validateWithYup<T extends Record<string, any>>(
    schema: ObjectSchema<T>,
    data: T
): Promise<Record<string, string> | null> {
    try {
        await schema.validate(data, { abortEarly: false });
        return null;
    } catch (error) {
        if (error instanceof ValidationError) {
            const errors: Record<string, string> = {};
            error.inner.forEach((err) => {
                if (err.path) {
                    errors[err.path] = err.message;
                }
            });
            return errors;
        }
        return null;
    }
}

/**
 * Valida um campo específico usando um schema Yup
 */
export async function validateFieldWithYup<T extends Record<string, any>>(
    schema: ObjectSchema<T>,
    field: string,
    data: T
): Promise<string | null> {
    try {
        await schema.validateAt(field, data);
        return null;
    } catch (error) {
        if (error instanceof ValidationError) {
            return error.message;
        }
        return null;
    }
}

