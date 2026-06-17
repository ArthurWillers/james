---
title: "Traits e Helpers"
weight: 11
date: 2026-06-09T12:41:14-03:00
draft: false
---

No desenvolvimento do James, adotamos o uso extensivo de **Traits** e **Helpers** para manter os modelos e controllers limpos, reaproveitando lógicas e padronizando formatações.

## Traits

As Traits injetam comportamentos padronizados em modelos Eloquent que as implementam.

### `Searchable`
**Local:** `app/Traits/Searchable.php`

Esta trait adiciona a escopo estática `search($term)` aos modelos. Ela busca em tempo real por um termo específico cruzando colunas normais (via `ILIKE`) e também colunas dinâmicas `JSONB`, extraindo valores aninhados usando `jsonpath`.
Isso permite que, por exemplo, o módulo de Contatos encontre facilmente um registro digitando apenas parte do número de telefone armazenado num array dinâmico.

### `HasInitials`
**Local:** `app/Traits/HasInitials.php`

Adiciona o método `getInitials()` ao modelo que extrai e formata as iniciais baseadas na propriedade `name`. Muito útil para renderizar avatares genéricos (fallback) quando o usuário ou contato não possui uma foto (ex: "Arthur Willers" -> "AW", "João" -> "J").

## Helpers

Classes com métodos estáticos utilitários chamados globalmente pela aplicação (principalmente no frontend via Blade).

### `DateHelper`
**Local:** `app/Helpers/DateHelper.php`

Centraliza toda a formatação de datas usando a biblioteca `Carbon`. Em vez de espalhar `format('d/m/Y')` por todas as views, chamamos o Helper para garantir consistência visual no sistema.

* `format($date)`: Formato longo por extenso (Ex: `17 de Junho de 2026`).
* `formatShort($date)`: Formato curto numérico (Ex: `17/06/2026`).
* `formatDateTime($date)`: Formato com data e hora exata (Ex: `17/06/2026 às 15:42`).
* `formatRelative($date)`: Formato amigável e relativo ao momento atual (Ex: `há 2 horas`, `em 3 dias`).
