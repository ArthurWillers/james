---
title: "Tags Financeiras"
weight: 2
---

## Visão Geral

As **Tags Financeiras** substituem o conceito clássico e engessado de "Categorias". Elas oferecem liberdade para classificar transações ou itens de transação sob múltiplas perspectivas sem a necessidade de árvores complexas de "Categoria > Subcategoria".

## Tabela: `financial_tags`

| Coluna | Tipo | Descrição |
| --- | --- | --- |
| `id` | bigint | Chave primária. |
| `name` | string | Nome identificador único da tag. |
| `icon` | string | Nome do componente de ícone (ex: `heroicon-o-shopping-cart`, `tabler-basket`, `phosphor-coffee`). |
| `color_hex` | string | Código de cor hexadecimal associado à tag (ex: `#10b981`). |
| `is_protected` | boolean | Flag que impede edição ou exclusão das tags obrigatórias do sistema. |
| `created_at` | timestamp | Data de criação. |
| `updated_at` | timestamp | Data da última atualização. |

## Tabela Auxiliar: `financial_taggables` (Polimórfica)

Para garantir flexibilidade, a relação é polimórfica através da tabela `financial_taggables`:
- Diretamente em uma **Transação Completa** (`financial_transactions`).
- Apenas em um **Item da Transação** (`financial_transaction_items`), ideal para separar impostos, juros e produtos de uma única Nota Fiscal.
- Possui a coluna booleana `is_primary` para definir qual tag resume a transação nos gráficos de Sankey e Fluxo de Caixa.

## Diagrama Relacional (ER)

```mermaid
erDiagram
    FINANCIAL_TAGS {
        bigint id PK
        string name
        string icon
        string color_hex
        boolean is_protected
    }
    FINANCIAL_TAGGABLES {
        bigint financial_tag_id FK
        bigint financial_taggable_id FK
        string financial_taggable_type
        boolean is_primary
    }
    FINANCIAL_TRANSACTIONS {
        bigint id PK
    }
    FINANCIAL_TRANSACTION_ITEMS {
        bigint id PK
    }

    FINANCIAL_TAGS ||--o{ FINANCIAL_TAGGABLES : "possui"
    FINANCIAL_TRANSACTIONS ||--o{ FINANCIAL_TAGGABLES : "morphToMany"
    FINANCIAL_TRANSACTION_ITEMS ||--o{ FINANCIAL_TAGGABLES : "morphToMany"
```

## Regras de Negócio e Comportamento

- **Exclusão Definitiva (Hard Deletes):** Tags não possuem Lixeira (soft deletes). A exclusão é estritamente bloqueada caso a tag já esteja associada a alguma transação ou item. Se ela estiver livre, é deletada definitivamente.
- **Tags de Sistema Protegidas:** As tags estruturais (ex: *Transferência*, *Reembolso*, *Juros*, *Saldo Inicial*, *Pagamento Parcial*) são semeadas pelo sistema com `is_protected = true`. O usuário não pode deletá-las nem renomeá-las.
- **Ecossistema Expandido de Ícones (Blade Icons):** O sistema suporta ícones das bibliotecas Heroicons (`heroicon-o-*`), Tabler Icons (`tabler-*`) e Phosphor Icons (`phosphor-*`), validados dinamicamente através da regra `ValidIcon`.
- **Seeder Flexível:** Ao configurar o ambiente inicial, tags comuns (como *Alimentação*, *Mercado*, *Transporte*, *Moradia*) são criadas desprotegidas via `FinancialTagSeeder`, permitindo personalização livre.
- **Seleção Avançada na Interface:** A criação de tags disponibiliza um seletor visual de cores e uma busca rápida de ícones com autofoco e renderização instantânea via Ajax.

