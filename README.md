# CalendarNow

Plataforma SaaS de agendamento online com multi-tenancy, marketplace de profissionais e painel de gestão completo.

## Stack

### Backend
- **PHP 8.2+** com **Laravel 13**
- **Laravel Fortify** — autenticação (2FA, verificação de e-mail, reset de senha)
- **Laravel Cashier** — faturamento via Stripe
- **Laravel Horizon** — monitoramento de filas
- **Laravel Octane** — performance e escalabilidade
- **Laravel Reverb** — servidor WebSocket
- **Stancl Tenancy v3** — multi-tenancy em banco único com roteamento por domínio
- **Spatie Laravel Permission** — controle de papéis e permissões
- **Spatie Media Library** — gerenciamento de arquivos e mídias
- **Maatwebsite Excel** — exportação de relatórios em Excel
- **DomPDF** — geração de PDFs
- **Clickbar Magellan** — suporte a dados geoespaciais (PostGIS)
- **Lorisleiva Laravel Actions** — arquitetura orientada a ações

### Frontend
- **React 19** com **TypeScript 5.7**
- **Inertia.js v2** — SPA server-driven sem API separada (com SSR)
- **Tailwind CSS 4** — estilização
- **FullCalendar** — componente de calendário interativo
- **Recharts** — gráficos e visualizações
- **Radix UI** — componentes acessíveis
- **i18next** — internacionalização
- **Laravel Echo + Pusher.js** — atualizações em tempo real
- **Vite 7** — bundler e dev server

### Dev & Qualidade
- **Laravel Pint** — formatação PHP (PSR-12)
- **ESLint 9 + Prettier 3** — linting e formatação TypeScript/React
- **PHPUnit 12** — testes automatizados (SQLite in-memory)
- **Laravel Sail** — ambiente Docker
- **Wayfinder** — rotas type-safe no frontend

## Funcionalidades

- **Multi-tenancy** por subdomínio com banco único
- **Marketplace** público para descoberta de profissionais
- **Agendamento online** com página de booking pública e temas customizáveis
- **Gestão de funcionários** com serviços, dias de folga e associação a calendários
- **Calendário interativo** com visualização de agendamentos por período
- **Módulo financeiro** — Contas, DRE (Demonstrativo de Resultado)
- **Relatórios exportáveis** — clientes, serviços, desempenho de funcionários, DRE
- **Assinaturas e planos** via Stripe
- **Autenticação completa** — 2FA, verificação de e-mail, reset de senha
- **Controle de acesso** com papéis e permissões por tenant
- **Tema dinâmico** (default / modern) por tenant
- **SEO configurável** por tenant
- **Geolocalização** para dados de endereço e marketplace

## Requisitos

- PHP >= 8.2
- Node.js >= 20
- PostgreSQL (recomendado para suporte geoespacial via PostGIS)
- Redis (filas, cache, sessões)
- Conta Stripe (para faturamento)

## Instalação

```bash
# Clonar repositório
git clone <repo-url>
cd calendarnow-laravel

# Instalar dependências PHP
composer install

# Instalar dependências JS
npm install

# Copiar e configurar variáveis de ambiente
cp .env.example .env
php artisan key:generate

# Executar migrations
php artisan migrate --seed

# Build do frontend
npm run build
```

## Desenvolvimento

```bash
# Iniciar todos os serviços em paralelo (server, queue, vite)
composer run dev

# Com SSR + log tailing
composer run dev:ssr

# Formatação e linting
npm run format
npm run lint
npm run types

# Testes PHP
php artisan test
```

## Estrutura

```
app/
├── Actions/        # Lógica de negócio orientada a ações
├── Models/         # Modelos Eloquent
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Exports/        # Exportações Excel
└── Providers/

resources/js/
├── pages/          # Páginas Inertia (React)
├── components/     # Componentes reutilizáveis
├── hooks/
├── layouts/
└── locales/        # Traduções i18next

routes/
├── web.php         # Domínio central (login, marketplace)
├── tenant.php      # Rotas autenticadas por tenant
└── settings.php
```

## Licença

MIT — veja [LICENSE](LICENSE).
