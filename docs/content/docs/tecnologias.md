---
title: "Stack e Tecnologias"
date: 2026-06-09T12:41:14-03:00
draft: false
---

O James é construído sobre uma fundação moderna, robusta e focada em simplicidade operacional. A escolha de cada ferramenta foi pensada para maximizar a produtividade, a privacidade e a consistência visual.

### Back-end e Infraestrutura
- **Linguagem / Framework**: PHP 8.5 com **Laravel 13**.
- **Autenticação Headless**: [Laravel Fortify](https://laravel.com/docs/fortify) gerencia todo o core de autenticação permitindo focar apenas nas views personalizadas.
- **Banco de Dados**: PostgreSQL 15+ (com extensões `unaccent` e `pg_trgm`).
- **Infraestrutura Local**: Docker gerenciado via [Laravel Sail](https://laravel.com/docs/sail).
- **Ferramentas de Desenvolvimento e Qualidade**:
  - [Pest PHP v4](https://pestphp.com/) (Testes Unitários, Feature e Arquiteturais)
  - [Laravel Pint](https://laravel.com/docs/pint) (Padronização e formatação automática de código)
  
### Front-end e UI
- **Páginas e Estrutura**: Blade Components altamente modularizados (registrados anonimamente via `Blade::anonymousComponentPath`, sem aliases como `ui.` ou `form.`).
- **Estilização**: **TailwindCSS (v4)** utilizando classes utilitárias semânticas e cores padronizadas da escala.
- **Interatividade Leve**: [Alpine.js](https://alpinejs.dev/) (v3) gerencia a reatividade no cliente (modais, tooltips, dropdowns, validações e manipulação interativa de listas).
- **Visualização Gráfica**: [Apache ECharts](https://echarts.apache.org/) integrado via Vanilla JavaScript para renderizar visualizações complexas (Diagrama de Sankey de fluxo de caixa, evolução cumulativa de saldo e donuts por categoria).
- **Ícones**: Ecossistema [Blade Icons](https://github.com/blade-ui-kit/blade-icons) com suporte nativo a:
  - Heroicons (`<x-heroicon-o-...>`) como padrão do sistema
  - Tabler Icons (`<x-tabler-...>`) e Phosphor Icons (`<x-phosphor-...>`) para seleção dinâmica de tags financeiras.

### Ferramentas e Bibliotecas Integradas
- **Auditoria e Logs de Mutações**:
  - **[Spatie Laravel Activitylog](https://spatie.be/docs/laravel-activitylog)** (v5+): Rastreamento automático e vitalício de alterações em todas as Models de negócio (`created`, `updated`, `deleted`, `restored`, `forceDeleted`).
- **Sistema de Notificações Multi-canal**:
  - **[Telegram Notification Channel](http://laravel-notification-channels.com/telegram/)**: Integração com a API de bots do Telegram para push notifications de alertas, faturas e relatórios.
- **Gestão de Imagens e Arquivos**:
  - **[Spatie Media Library](https://spatie.be/docs/laravel-medialibrary)**: Trata todo o armazenamento polimórfico de avatares e comprovantes no disco privado (`private`), garantindo 100% de sigilo.
  - **[Cropper.js](https://fengyuanchen.github.io/cropperjs/)**: Integrado via componente Blade (`form-image-cropper`) e Alpine.js para recorte visual de imagens antes do upload.
- **Editor Rico (Rich Text)**:
  - **[EasyMDE](https://github.com/Ionaru/easy-markdown-editor)**: Editor visual para anotações e descrições, persistindo os dados como **Markdown puro** no banco.

### Padrões Arquiteturais e Banco de Dados
- **PostgreSQL Extensions**:
  - `unaccent`: Habilitado para buscas textuais precisas ignorando acentuação.
  - `pg_trgm`: Utilizado para indexação otimizada e buscas por similaridade/relevância.
- **Soft Deletes**: Deleção lógica aplicada por padrão às entidades principais do sistema.
- **Dados Dinâmicos em JSONB**: Utilização do tipo `JSONB` nativo do PostgreSQL para armazenar listas flexíveis (telefones, e-mails, chaves Pix) sem poluir o banco com tabelas auxiliares desnecessárias.

