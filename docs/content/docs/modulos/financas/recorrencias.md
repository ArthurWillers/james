---
title: "Recorrências"
weight: 4
---

## Visão Geral

As **Recorrências** (`FinancialRecurrence`) permitem a automação do fluxo de caixa agendando despesas ou receitas recorrentes (ex: assinaturas, mensalidades, salários). Diferente de compras parceladas, onde as parcelas nascem limitadas e já instanciadas no banco de dados, as recorrências são um "molde" dinâmico que gera transações à medida que o tempo passa, permitindo projeções de longo prazo sem inchar o banco de dados.

## Tabelas

### `financial_recurrences`

| Coluna | Tipo | Descrição |
| --- | --- | --- |
| `id` | bigint | Chave primária. |
| `financial_account_id` | foreignId | (Opcional) Conta bancária vinculada. |
| `financial_credit_card_id` | foreignId | (Opcional) Cartão de Crédito vinculado. |
| `title` | string | Descrição/Título da recorrência. |
| `type` | enum | `income` (receita) ou `expense` (despesa). |
| `amount` | decimal | Valor base da transação a ser gerada. |
| `frequency` | string | `weekly`, `monthly`, ou `yearly`. |
| `start_date` | date | Data de início da recorrência. O dia desta data serve como base (Ex: dia 15). |
| `end_date` | date | (Opcional) Data em que a recorrência deixa de vigorar. |
| `next_processing_date` | date | Próxima data em que uma transação deverá ser materializada. |
| `is_active` | boolean | Liga/desliga o motor de geração para esta recorrência. |

## Diagrama Relacional (ER)

```mermaid
erDiagram
    FINANCIAL_ACCOUNTS {
        bigint id PK
    }
    FINANCIAL_CREDIT_CARDS {
        bigint id PK
    }
    FINANCIAL_RECURRENCES {
        bigint id PK
        bigint financial_account_id FK
        bigint financial_credit_card_id FK
        string frequency
        date start_date
        date next_processing_date
    }
    FINANCIAL_TRANSACTIONS {
        bigint id PK
        bigint financial_recurrence_id FK
    }

    FINANCIAL_ACCOUNTS ||--o{ FINANCIAL_RECURRENCES : "vinculada (XOR)"
    FINANCIAL_CREDIT_CARDS ||--o{ FINANCIAL_RECURRENCES : "vinculada (XOR)"
    FINANCIAL_RECURRENCES ||--o{ FINANCIAL_TRANSACTIONS : "materializa em (histórico)"
```

## Regras de Negócio e Comportamento

### Vínculo Exclusivo (Validação XOR)
Uma recorrência sempre movimenta fundos. Portanto, ela exige um vínculo de origem/destino.
A regra de negócio determina que deve haver um **Ou Exclusivo (XOR)** na validação e na estrutura de dados:
- Ou a recorrência está vinculada a uma Conta Bancária (`financial_account_id`).
- Ou a recorrência está vinculada a um Cartão de Crédito (`financial_credit_card_id`).
As duas colunas não podem estar nulas simultaneamente, nem preenchidas ao mesmo tempo.

### Frequência e Dia Base
O dia em que a transação acontece é definido pelo dia extraído da coluna `start_date` via o método `dayOfMonth()`.
O processamento irá somar +1 semana, +1 mês ou +1 ano a depender da `frequency`, gerando o `next_processing_date`.

### Transações Virtuais vs. Materialização
Para evitar criação massiva de registros no banco de dados para os próximos 10 anos, as recorrências operam sob o conceito de **Transações Virtuais**.
- Ao consultar projeções financeiras, o sistema "extrapola" as datas calculando instâncias virtuais em tempo real na memória.
- Uma recorrência só vira uma `FinancialTransaction` real (materialização) quando:
  1. A data da `next_processing_date` se aproxima de hoje e o motor de automação (Cron) roda para criar a despesa real.
  2. O usuário interage com o sistema para efetivar/pagar manualmente.
  3. Quando vinculada a cartão de crédito, ao chegar próximo ao fechamento da fatura.

Essa abordagem híbrida garante relatórios performáticos enquanto mantém os dados estritamente fiéis à realidade consolidada.
