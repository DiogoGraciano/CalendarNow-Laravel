import i18n from 'i18next';
import LanguageDetector from 'i18next-browser-languagedetector';
import { initReactI18next } from 'react-i18next';

// Importar traduções
import enTranslations from '../locales/en/common.json';
import esTranslations from '../locales/es/common.json';
import ptTranslations from '../locales/pt/common.json';

i18n
    // Detectar idioma do navegador
    .use(LanguageDetector)
    // Passar instância do i18n para react-i18next
    .use(initReactI18next)
    // Inicializar i18next
    .init({
        // Idioma padrão
        fallbackLng: 'en',
        // Idiomas disponíveis
        supportedLngs: ['en', 'pt', 'es'],
        // Idioma padrão
        lng: 'en',
        // Namespace padrão
        defaultNS: 'common',
        // Namespaces disponíveis
        ns: ['common'],
        // Debug (desativar em produção)
        debug: false,
        // Interpolação
        interpolation: {
            escapeValue: false, // React já faz escape
        },
        // Recursos de tradução
        resources: {
            en: {
                common: enTranslations,
            },
            pt: {
                common: ptTranslations,
            },
            es: {
                common: esTranslations,
            },
        },
        // Opções de detecção de idioma
        detection: {
            // Ordem de detecção
            order: [
                'localStorage',
                'navigator',
                'htmlTag',
                'path',
                'subdomain',
            ],
            // Chave para armazenar no localStorage
            lookupLocalStorage: 'i18nextLng',
            // Cache do idioma
            caches: ['localStorage'],
            // Não usar detecção no servidor (SSR)
            ...(typeof window === 'undefined' && {
                caches: [],
            }),
        },
    });

export default i18n;

