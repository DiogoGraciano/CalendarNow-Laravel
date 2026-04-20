/**
 * Funções de máscara reutilizáveis para formatação de dados
 */

/**
 * Remove todos os caracteres não numéricos de uma string
 */
export function removeMask(value: string): string {
    return value.replace(/\D/g, '');
}

/**
 * Aplica máscara de CEP (00000-000)
 */
export function maskCep(value: string): string {
    const numbers = removeMask(value);
    if (numbers.length <= 5) {
        return numbers;
    }
    return `${numbers.slice(0, 5)}-${numbers.slice(5, 8)}`;
}

/**
 * Aplica máscara de telefone brasileiro
 * Formato: (00) 00000-0000 ou (00) 0000-0000
 */
export function maskPhone(value: string): string {
    const numbers = removeMask(value);
    
    if (numbers.length <= 2) {
        return numbers;
    }
    
    if (numbers.length <= 6) {
        return `(${numbers.slice(0, 2)}) ${numbers.slice(2)}`;
    }
    
    if (numbers.length <= 10) {
        return `(${numbers.slice(0, 2)}) ${numbers.slice(2, 6)}-${numbers.slice(6)}`;
    }
    
    // Telefone celular (11 dígitos)
    return `(${numbers.slice(0, 2)}) ${numbers.slice(2, 7)}-${numbers.slice(7, 11)}`;
}

/**
 * Aplica máscara de CPF (000.000.000-00)
 */
export function maskCpf(value: string): string {
    const numbers = removeMask(value);
    
    if (numbers.length <= 3) {
        return numbers;
    }
    
    if (numbers.length <= 6) {
        return `${numbers.slice(0, 3)}.${numbers.slice(3)}`;
    }
    
    if (numbers.length <= 9) {
        return `${numbers.slice(0, 3)}.${numbers.slice(3, 6)}.${numbers.slice(6)}`;
    }
    
    return `${numbers.slice(0, 3)}.${numbers.slice(3, 6)}.${numbers.slice(6, 9)}-${numbers.slice(9, 11)}`;
}

/**
 * Aplica máscara de CNPJ (00.000.000/0000-00)
 */
export function maskCnpj(value: string): string {
    const numbers = removeMask(value);
    
    if (numbers.length <= 2) {
        return numbers;
    }
    
    if (numbers.length <= 5) {
        return `${numbers.slice(0, 2)}.${numbers.slice(2)}`;
    }
    
    if (numbers.length <= 8) {
        return `${numbers.slice(0, 2)}.${numbers.slice(2, 5)}.${numbers.slice(5)}`;
    }
    
    if (numbers.length <= 12) {
        return `${numbers.slice(0, 2)}.${numbers.slice(2, 5)}.${numbers.slice(5, 8)}/${numbers.slice(8)}`;
    }
    
    return `${numbers.slice(0, 2)}.${numbers.slice(2, 5)}.${numbers.slice(5, 8)}/${numbers.slice(8, 12)}-${numbers.slice(12, 14)}`;
}

/**
 * Aplica máscara de data (00/00/0000)
 */
export function maskDate(value: string): string {
    const numbers = removeMask(value);
    
    if (numbers.length <= 2) {
        return numbers;
    }
    
    if (numbers.length <= 4) {
        return `${numbers.slice(0, 2)}/${numbers.slice(2)}`;
    }
    
    return `${numbers.slice(0, 2)}/${numbers.slice(2, 4)}/${numbers.slice(4, 8)}`;
}

/**
 * Aplica máscara de hora (00:00)
 */
export function maskTime(value: string): string {
    const numbers = removeMask(value);
    
    if (numbers.length <= 2) {
        return numbers;
    }
    
    return `${numbers.slice(0, 2)}:${numbers.slice(2, 4)}`;
}

/**
 * Aplica máscara de dinheiro brasileiro (R$ 0,00)
 */
export function maskCurrency(value: string): string {
    const numbers = removeMask(value);
    
    if (!numbers) {
        return '';
    }
    
    // Converte para número e divide por 100 para ter centavos
    const amount = Number(numbers) / 100;
    
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(amount);
}

/**
 * Aplica máscara de número inteiro (apenas números)
 */
export function maskNumber(value: string): string {
    return removeMask(value);
}

/**
 * Aplica máscara de porcentagem (0,00%)
 */
export function maskPercentage(value: string): string {
    const numbers = removeMask(value);
    
    if (!numbers) {
        return '';
    }
    
    const amount = Number(numbers) / 100;
    
    return new Intl.NumberFormat('pt-BR', {
        style: 'percent',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
}

/**
 * Handler genérico para aplicar máscara em eventos onChange
 */
export function createMaskHandler(
    maskFunction: (value: string) => string,
    setValue: (value: string) => void,
    clearError?: () => void
) {
    return (e: React.ChangeEvent<HTMLInputElement>) => {
        const maskedValue = maskFunction(e.target.value);
        setValue(maskedValue);
        if (clearError) {
            clearError();
        }
    };
}

