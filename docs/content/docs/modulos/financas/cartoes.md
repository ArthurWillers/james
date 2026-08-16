---
title: "Cartões de Crédito"
weight: 2
---

## Visão Geral

O módulo de **Cartões de Crédito** gerencia limites, datas de vencimento/fechamento e o ciclo de vida das faturas (invoices). Um cartão está sempre vinculado a uma [Conta Financeira](/docs/modulos/financas/contas) de onde o dinheiro sairá quando a fatura for paga. O módulo possui inteligência de dias úteis para mover automaticamente datas que caiam em finais de semana ou feriados nacionais.

## Tabelas

### `financial_credit_cards`

| Coluna | Tipo | Descrição |
| --- | --- | --- |
| `id` | bigint | Chave primária. |
| `financial_account_id` | foreignId | Conta corrente vinculada para débito do pagamento. |
| `name` | string | Nome do cartão (Ex: Nubank Platinum). |
| `credit_limit` | decimal | Limite de crédito disponível. |
| `closing_day` | integer | Dia padrão de fechamento da fatura (Ex: 7). |
| `due_day` | integer | Dia padrão de vencimento da fatura (Ex: 2). |
| `deleted_at` | timestamp | Soft deletes (Lixeira). |

### `financial_credit_card_invoices`

| Coluna | Tipo | Descrição |
| --- | --- | --- |
| `id` | bigint | Chave primária. |
| `financial_credit_card_id` | foreignId | Vínculo com o cartão originador. |
| `reference_month` | date | Mês/Ano base da fatura (dia é sempre 01). |
| `closing_date` | date | Data *ajustada* real de fechamento (respeita personalizações e feriados). |
| `due_date` | date | Data *ajustada* real de vencimento (respeita dias úteis). |
| `amount_paid` | decimal | Valor já pago nesta fatura. |
| `paid_at` | date | Data em que a fatura foi totalmente quitada. |
| `interest_transaction_id`| foreignId | Vínculo com a despesa de juros, caso haja pagamento em atraso. |

## Diagrama Relacional (ER)

```mermaid
erDiagram
    FINANCIAL_ACCOUNTS {
        bigint id PK
    }
    FINANCIAL_CREDIT_CARDS {
        bigint id PK
        bigint financial_account_id FK
        integer closing_day
        integer due_day
    }
    FINANCIAL_CREDIT_CARD_INVOICES {
        bigint id PK
        bigint financial_credit_card_id FK
        date reference_month
        date closing_date
        date due_date
        decimal amount_paid
    }
    FINANCIAL_TRANSACTIONS {
        bigint id PK
        bigint financial_credit_card_invoice_id FK
        bigint financial_account_id FK
        enum status "draft, pending, posted"
    }

    FINANCIAL_ACCOUNTS ||--o{ FINANCIAL_CREDIT_CARDS : "paga a fatura"
    FINANCIAL_CREDIT_CARDS ||--o{ FINANCIAL_CREDIT_CARD_INVOICES : "gera"
    FINANCIAL_CREDIT_CARD_INVOICES ||--o{ FINANCIAL_TRANSACTIONS : "agrega"
```

## Regras de Negócio e Comportamento

### Ciclo de Vida e Status da Fatura (`InvoiceStatus`)

O estado da fatura é computado dinamicamente através do Enum `App\Enums\InvoiceStatus`:

| Status | Case | Cor | Descrição |
| --- | --- | --- | --- |
| **Paga** | `InvoiceStatus::Paid` | Verde | Fatura totalmente quitada (`amount_paid >= total`). |
| **Parcialmente Paga** | `InvoiceStatus::PartiallyPaid` | Amarelo | Houve pagamento parcial antes do fechamento/vencimento. |
| **Aberta** | `InvoiceStatus::Open` | Azul | Fatura corrente ainda recebendo novas compras (antes do fechamento). |
| **Fechada** | `InvoiceStatus::Closed` | Neutro / Cinza | Fatura fechada aguardando pagamento (após o fechamento e antes do vencimento). |
| **Atrasada** | `InvoiceStatus::Overdue` | Vermelho | Fatura fechada com vencimento ultrapassado e saldo em aberto. |

### Centralização da Fatura Corrente (`setCurrentInvoice`)

O modelo `FinancialCreditCard` centraliza a lógica de resolução da fatura ativa no método `setCurrentInvoice()`. Essa abstração calcula dinamicamente o total da fatura aberta, a quantidade de lançamentos e o status de liquidez sem onerar o banco de dados com queries redundantes em loops de listagem.

### Lógica de Feriados e Dias Úteis

O sistema integra com a `BrasilAPI` (via `BusinessDayHelper`) para buscar os feriados nacionais anuais, mantendo-os em cache:
- O **Fechamento** é antecipado para o *dia útil anterior* caso caia em feriado/final de semana.
- O **Vencimento** é postergado para o *próximo dia útil* caso caia em feriado/final de semana.
- **Fechamento Personalizado:** O sistema respeita fechamentos com data customizada (`closing_date`) definida pelo usuário ou ajustada no banco ao vincular novas transações.

### Rolagem e Vinculação de Compras

Ao lançar uma nova compra, o método `FinancialCreditCardInvoice::resolveForDate` compara a data da transação com a data real (ajustada) de fechamento daquele mês. Se a transação for feita no dia ou antes do fechamento, entra na fatura atual. Se for posterior, rola automaticamente para a fatura do mês subsequente.

### Automação (Cron)

O comando `php artisan finance:rollover-invoices` executa diariamente à meia-noite via Laravel Scheduler. Ele percorre todos os cartões ativos e garante que a fatura do mês de referência exista no banco de dados.

### Pagamento da Fatura

Quando o pagamento total é confirmado:
1. Uma despesa é gerada na Conta Bancária vinculada ao cartão.
2. Caso informado valor de **Juros**, uma transação extra de juros é gerada aplicando a tag protegida `Juros`.
3. Todas as transações atreladas à fatura têm seu status alterado para `TransactionStatus::Posted` (`posted`), consolidando o fluxo financeiro.

### Exclusão (Soft Deletes)

Cartões não podem ser excluídos permanentemente (`forceDelete`) se possuírem faturas ou transações ativas, garantindo a rastreabilidade contábil.

