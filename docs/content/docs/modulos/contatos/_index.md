---
title: "Contatos"
weight: 2
date: 2026-06-09T12:41:14-03:00
draft: false
---

### Visão Geral
O módulo Contatos é a fundação relacional do James — um registro pessoal 
de pessoas com quem você se relaciona, sem a carga corporativa de um CRM 
tradicional. Entidades puramente passivas, sem login ou vínculo com a 
tabela users.

### Estrutura de Dados

O cadastro armazena os dados básicos de identificação e categorização, além de um campo de anotações em Markdown. Para informações múltiplas (como telefones, endereços e chaves Pix), o sistema agrupa os dados de forma flexível (via JSON) diretamente no registro do contato, evitando a complexidade de diversas tabelas de relacionamento.

### Gestão de Avatar
Sem coluna física de `avatar_path`. As fotos de perfil são gerenciadas 
pela Spatie Media Library com `MEDIA_DISK=private`, blindadas no servidor 
e servidas exclusivamente via rotas protegidas pelo middleware auth. 
Conversão automática para WebP com geração de thumbnail.

### Referências
- [Roadmap — Módulo Contatos](/james/docs/roadmap/#fase-2-módulo-contatos)
- [Decisão 006 — Spatie Media Library](/james/docs/decisoes/#006--adoção-da-spatie-media-library-com-armazenamento-privado)
- [Decisão 008 — Nomenclatura Contatos vs CRM](/james/docs/decisoes/#008--nomenclatura-contatos-vs-crm)
- [Decisão 009 — Editor Visual Markdown](/james/docs/decisoes/#009--editor-visual-markdown)
