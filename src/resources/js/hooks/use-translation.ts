import { useTranslation as useI18nextTranslation } from 'react-i18next';

/**
 * Hook personalizado para tradução
 * Facilita o uso do react-i18next no projeto
 *
 * @example
 * ```tsx
 * const { t, i18n } = useTranslation();
 * 
 * return (
 *   <div>
 *     <h1>{t('welcome')}</h1>
 *     <button onClick={() => i18n.changeLanguage('pt')}>
 *       {t('changeLanguage')}
 *     </button>
 *   </div>
 * );
 * ```
 */
export function useTranslation() {
    const { t, i18n } = useI18nextTranslation('common');

    return {
        t,
        i18n,
        currentLanguage: i18n.language,
        changeLanguage: (lang: 'en' | 'pt' | 'es') => i18n.changeLanguage(lang),
        isReady: i18n.isInitialized,
    };
}

