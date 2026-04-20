import { useTranslation } from '@/hooks/use-translation';
import { formatCurrency as formatCurrencyLib } from '@/lib/currency';

/**
 * Retorna uma função que formata valor em moeda conforme o idioma atual:
 * - Português: BRL (R$)
 * - Outros: USD ($)
 */
export function useFormatCurrency(): (
    value: number | undefined | null
) => string {
    const { currentLanguage } = useTranslation();
    return (value: number | undefined | null) =>
        formatCurrencyLib(value, currentLanguage);
}
