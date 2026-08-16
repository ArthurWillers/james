---
title: "Traits e Helpers"
weight: 11
date: 2026-06-09T12:41:14-03:00
draft: false
---

No desenvolvimento do James, adotamos o uso extensivo de **Traits** e **Helpers** para manter os modelos e controllers limpos, reaproveitando lógicas e padronizando formatações.

## Traits

As Traits injetam comportamentos padronizados em modelos e controllers Eloquent que as implementam.

### `Searchable`
**Local:** `app/Traits/Searchable.php`

Esta trait adiciona o escopo estático `search($term)` aos modelos. Ela busca em tempo real por um termo específico cruzando colunas normais (via `ILIKE`) e também colunas dinâmicas `JSONB`, extraindo valores aninhados usando `jsonpath`.
Isso permite que, por exemplo, o módulo de Contatos encontre facilmente um registro digitando apenas parte do número de telefone armazenado num array dinâmico.

### `HasInitials`
**Local:** `app/Traits/HasInitials.php`

Adiciona o método `getInitials()` ao modelo que extrai e formata as iniciais baseadas na propriedade `name`. Muito útil para renderizar avatares genéricos (fallback) quando o usuário ou contato não possui uma foto (ex: "Arthur Willers" $\rightarrow$ "AW", "João" $\rightarrow$ "J").

### `HandlesAttachments`
**Local:** `app/Traits/HandlesAttachments.php`

Padroniza a sincronização de arquivos anexos (adicionando novos uploads e expurgando arquivos marcados para remoção) via Spatie MediaLibrary:
- `syncAttachments(Model $model, array $data, string $collection = 'attachments'): void`

---

## Helpers

Classes com métodos estáticos utilitários e funções globais associadas para uso direto no backend e nas views Blade.

### `DateHelper`
**Local:** `app/Helpers/DateHelper.php`

Centraliza a formatação de datas e horas usando a biblioteca `Carbon`, garantindo a aplicação do timezone configurado (`America/Sao_Paulo`):

* `formatDate($date)` / `DateHelper::format($date)`: Formato longo por extenso (Ex: `17 de Junho de 2026`).
* `formatShort($date)` / `DateHelper::formatShort($date)`: Formato numérico curto (Ex: `17/06/2026`).
* `formatDateTime($date)` / `DateHelper::formatDateTime($date)`: Formato com data e hora (Ex: `17/06/2026 às 15:42`).
* `formatRelative($date)` / `DateHelper::formatRelative($date)`: Formato amigável e relativo ao momento atual (Ex: `há 2 horas`, `em 3 dias`).
* `formatMonthYear($date)` / `DateHelper::formatMonthYear($date)`: Mês e ano abreviado (Ex: `06/2026`).
* `formatMonthYearFull($date)` / `DateHelper::formatMonthYearFull($date)`: Mês por extenso e ano em TitleCase (Ex: `Junho 2026`).

### `CurrencyHelper`
**Local:** `app/Helpers/CurrencyHelper.php`

Centraliza a formatação monetária utilizando a classe `Number::currency()` nativa do Laravel:

* `formatCurrency($value, $currency = '', $locale = null)` / `CurrencyHelper::format($value, ...)`: Converte valores numéricos em strings formatadas no padrão da moeda local (Ex: `formatCurrency(1250.50)` $\rightarrow$ `R$ 1.250,50`).

