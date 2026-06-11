---
title: "Decisões Arquiteturais"
date: 2026-06-09T12:41:14-03:00
draft: false
---

Registro de decisões arquiteturais do projeto.

## 001 — Laravel Puro sem Livewire

Decisão de usar Laravel puro com Blade, sem Livewire.

## 002 — PostgreSQL em Dev e Prod

Decisão de usar PostgreSQL tanto no ambiente de desenvolvimento quanto em produção.

## 003 — Identidade Visual

Cor principal #DAA520, design minimalista e sidebar fixa.

## 004 — Pipeline Automatizado de Ícones (Heroicons)

**Data:** 2026-06-11

Ícones SVG gerados automaticamente a partir do repositório oficial [tailwindlabs/heroicons](https://github.com/tailwindlabs/heroicons) via script Python (`scripts/sync-heroicons.py`).

**Motivação:** O componente monolítico `icon.blade.php` com SVGs hardcoded era difícil de manter e limitava a quantidade de ícones disponíveis. Com o pipeline automatizado, todos os 300+ ícones do Heroicons ficam disponíveis e atualizáveis com um único comando.

**Uso:**
```blade
{{-- Outline (24px, stroke) --}}
<x-icons.outline.home class="size-6" />

{{-- Solid (24px, fill) --}}
<x-icons.solid.check class="size-6" />

{{-- Mini (20px, fill) --}}
<x-icons.mini.chevron-down class="size-5" />

{{-- Micro (16px, fill) --}}
<x-icons.micro.star class="size-4" />

{{-- Dinâmico (via variável) --}}
<x-dynamic-component :component="'icons.outline.' . $icon" class="size-6" />
```

**Atualização:** Para atualizar os ícones para a versão mais recente do Heroicons:
```bash
python3 scripts/sync-heroicons.py
```

**Observações:**
- Os ícones gerados ficam em `resources/views/components/icons/` e são versionados no Git
- Ao atualizar os ícones, basta rodar o script e commitar o resultado
- O script suporta `--dry-run` e `--verbose` para depuração
