---
title: "Stack e Tecnologias"
date: 2026-06-09T12:41:14-03:00
draft: false
---

O James é construído sobre uma fundação moderna, robusta e focada em simplicidade operacional. A escolha de cada ferramenta foi pensada para maximizar a produtividade e a consistência visual.

### Back-end e Infraestrutura
- **Linguagem / Framework**: PHP com Laravel.
- **Banco de Dados**: PostgreSQL.
- **Infraestrutura Local**: Docker gerenciado via [Laravel Sail](https://laravel.com/docs/sail).
- **Testes**: [Pest PHP](https://pestphp.com/) para testes unitários e de feature.

### Front-end e UI
- **Páginas e Estrutura**: Blade Components altamente modularizados (namespaces lógicos como `ui`, `form`, `layout`).
- **Estilização**: TailwindCSS (v4) focado em classes utilitárias para construir interfaces limpas e responsivas.
- **Interatividade Leve**: [Alpine.js](https://alpinejs.dev/) (v3) gerencia a reatividade no cliente (modais, tooltips, dropdowns, validações e manipulação interativa).
- **Ícones**: [Heroicons](https://heroicons.com/) integrados nativamente via componentes Blade (`<x-icons.*>`). Eles são sincronizados e atualizados localmente através de um script Python (`scripts/sync-heroicons.py`).

### Ferramentas e Bibliotecas Integradas
- **Gestão de Imagens e Arquivos**: 
  - **[Spatie Media Library](https://spatie.be/docs/laravel-medialibrary)**: Trata todo o armazenamento e associação polimórfica de arquivos anexos. Opera estritamente no disco `local` para garantir 100% de privacidade.
  - **[Cropper.js](https://fengyuanchen.github.io/cropperjs/)**: Integrado através de um componente customizado (`form-image-cropper`) e Alpine.js para recorte (crop) visual de imagens (como avatares) antes do upload.
- **Editor Rico (Rich Text)**:
  - **[EasyMDE](https://github.com/Ionaru/easy-markdown-editor)**: Usado para edição de textos ricos como notas e descrições (através do componente `form-markdown-editor`). Os dados são salvos como **Markdown puro** no banco, garantindo extrema segurança contra injeções de HTML e facilidade de exportação.

### Padrões Arquiteturais e Banco de Dados
- **PostgreSQL Extensions**:
  - `unaccent`: Habilitado para buscas textuais precisas ignorando acentuação.
  - `pg_trgm`: Utilizado para indexação otimizada e buscas por similaridade/relevância.
- **Soft Deletes**: Deleção lógica aplicada às entidades principais da aplicação, fornecendo uma "lixeira" para evitar perdas catastróficas de dados.
- **Dados Dinâmicos em JSONB**: Utilização agressiva do tipo `JSONB` nativo do PostgreSQL para armazenar múltiplas propriedades simples (como listas de telefones e e-mails) sem precisar poluir o banco com dezenas de tabelas de relacionamento. O componente `form-key-value-repeater` facilita toda a gestão visual desse JSON.
