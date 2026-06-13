---
title: "Arquitetura"
date: 2026-06-09T12:41:14-03:00
draft: false
---

### Stack Tecnológica
- **Back-end**: Laravel (PHP)
- **Banco de Dados**: PostgreSQL
- **Front-end**: Blade Components, TailwindCSS, Alpine.js
- **Infraestrutura**: Docker (via Laravel Sail)

### Padrões e Extensões
- **PostgreSQL Extensions**:
  - `unaccent`: Utilizado para buscas textuais ignorando acentuação.
  - `pg_trgm`: Utilizado para buscas rápidas por similaridade.
- **Soft Deletes**: Deleção lógica aplicada às principais entidades para prevenir perda acidental de dados.
- **Dados Dinâmicos em JSONB**: Utilização do tipo `JSONB` nativo do PostgreSQL para armazenar dados variáveis (telefones, chaves Pix, endereços), evitando complexidade excessiva com tabelas relacionais em casos simples.
- **Gestão de Mídia**: Integração com Spatie Media Library em discos privados (`MEDIA_DISK=private`) para garantir total privacidade de arquivos sensíveis (avatares, cupons fiscais), sendo expostos apenas via rotas protegidas pelo middleware de autenticação.
