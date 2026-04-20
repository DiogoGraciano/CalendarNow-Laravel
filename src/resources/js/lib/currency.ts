/**
 * Formata valor monetário conforme o idioma:
 * - Português (pt): Real brasileiro (BRL)
 * - Outros idiomas: Dólar (USD)
 */
export function formatCurrency(
    value: number | undefined | null,
    language: string
): string {
    const n = Number(value);
    if (Number.isNaN(n)) {
        return isPortuguese(language)
            ? 'R$ 0,00'
            : new Intl.NumberFormat('en-US', {
                  style: 'currency',
                  currency: 'USD',
              }).format(0);
    }
    if (isPortuguese(language)) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
        }).format(n);
    }
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(n);
}

function isPortuguese(lang: string): boolean {
    const normalized = (lang || '').toLowerCase();
    return normalized.startsWith('pt');
}
