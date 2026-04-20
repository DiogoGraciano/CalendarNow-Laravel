# Traduções (i18n)

Este diretório contém os arquivos de tradução para o projeto usando react-i18next.

## Estrutura

```
locales/
├── en/
│   └── common.json    # Traduções em inglês
├── pt/
│   └── common.json    # Traduções em português
└── es/
    └── common.json    # Traduções em espanhol
```

## Como usar

### 1. Usando o hook `useTranslation`

```tsx
import { useTranslation } from '@/hooks/use-translation';

function MyComponent() {
    const { t, changeLanguage, currentLanguage } = useTranslation();

    return (
        <div>
            <h1>{t('welcome')}</h1>
            <p>{t('hello')}</p>
            <button onClick={() => changeLanguage('pt')}>
                Mudar para Português
            </button>
        </div>
    );
}
```

### 2. Usando o hook direto do react-i18next

```tsx
import { useTranslation } from 'react-i18next';

function MyComponent() {
    const { t } = useTranslation('common');

    return <div>{t('welcome')}</div>;
}
```

### 3. Usando o componente `Trans` para traduções com elementos HTML

```tsx
import { Trans } from 'react-i18next';

function MyComponent() {
    return (
        <Trans i18nKey="welcome">
            Welcome to our <strong>application</strong>
        </Trans>
    );
}
```

### 4. Interpolação de variáveis

```tsx
// No arquivo de tradução (common.json):
// "greeting": "Hello, {{name}}!"

function MyComponent() {
    const { t } = useTranslation();
    
    return <div>{t('greeting', { name: 'John' })}</div>;
    // Resultado: "Hello, John!"
}
```

### 5. Pluralização

```tsx
// No arquivo de tradução (common.json):
// "items": "{{count}} item",
// "items_plural": "{{count}} items"

function MyComponent() {
    const { t } = useTranslation();
    
    return (
        <div>
            {t('items', { count: 1 })}  // "1 item"
            {t('items', { count: 5 })}   // "5 items"
        </div>
    );
}
```

## Adicionar novas traduções

1. Adicione a chave nos arquivos JSON de cada idioma:

**en/common.json:**
```json
{
  "myNewKey": "My new text"
}
```

**pt/common.json:**
```json
{
  "myNewKey": "Meu novo texto"
}
```

**es/common.json:**
```json
{
  "myNewKey": "Mi nuevo texto"
}
```

2. Use no componente:

```tsx
const { t } = useTranslation();
return <div>{t('myNewKey')}</div>;
```

## Componente de Seletor de Idioma

Use o componente `LanguageSelector` para permitir que os usuários alterem o idioma:

```tsx
import { LanguageSelector } from '@/components/language-selector';

function Header() {
    return (
        <header>
            <LanguageSelector />
        </header>
    );
}
```

## Configuração

A configuração do i18next está em `src/resources/js/lib/i18n.ts`.

O idioma é detectado automaticamente através de:
1. localStorage (preferência do usuário)
2. Navegador
3. HTML tag
4. Path da URL
5. Subdomínio

O idioma selecionado é salvo no localStorage para persistir entre sessões.

