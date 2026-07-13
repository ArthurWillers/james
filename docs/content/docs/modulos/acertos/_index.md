---
title: "Acertos"
weight: 4
---

### Visão Geral

O Módulo de Acertos foi idealizado para substituir o uso de planilhas e aplicativos como o Splitwise. Este módulo é fortemente inspirado no projeto [BalanceFlow](https://github.com/ArthurWillers/BalanceFlow).

Sua principal finalidade é gerenciar a relação de débitos e créditos informais com outras pessoas ("Eu Devo" e "Me Devem") de maneira rápida, centralizada e performática.

## Principais Funcionalidades

- **Gestão de Dívidas**: Controle claro de quem deve a quem.
- **Rateios e Divisões**: Suporte a rateios exatos (valores definidos) e percentuais (ex: 50/50, 70/30).
- **Ações em Massa**: Possibilidade de interagir e liquidar múltiplas dívidas ao mesmo tempo.
- **Zerar Dívida**: Botão rápido para liquidar rapidamente um saldo com um contato específico.
- **Interface Centrada no Contato**: A interface é primariamente focada nas pessoas com quem você interage, facilitando o entendimento de saldos globais.
- **Grupos Frequentes**: Possibilidade de salvar grupos de divisão recorrentes (ex: "Mãe e Irmão").

## Regras de Negócio e Princípios Arquiteturais

1. **Dependência do CRM (Single Source of Truth)**
   O Módulo de Acertos não cria entidades de "Pessoas". Ele possui uma integração obrigatória com o Módulo CRM. Toda pessoa adicionada em um acerto deve ser um contato previamente (ou no momento) cadastrado no CRM. Os participantes da dívida são sempre referências (Foreign Keys) para os contatos.

2. **Separação de Regimes (Competência vs. Caixa)**
   Este módulo lida exclusivamente com o **Regime de Competência**. Ou seja, ele registra a "promessa de pagamento" ou o fato de que a despesa ocorreu, mas **não** é o extrato bancário. O registro aqui diz "Eu devo R$ 50 para o João", e não que "R$ 50 saíram da conta corrente".

3. **Liquidação e Integração Financeira**
   A separação de regimes garante que não tenhamos "God Tables". As tabelas de acertos e despesas compartilhadas são totalmente isoladas das transações reais do fluxo de caixa. Quando uma dívida é marcada como "Paga" neste módulo, o sistema realiza a liquidação lógica do acerto e, se necessário, prepara um gatilho para gerar opcionalmente a transação correspondente (de entrada ou saída) no Módulo Financeiro (Regime de Caixa).

### Tabelas e Modelagem (`settlements`)

A modelagem de dados do módulo de Acertos foi projetada para garantir flexibilidade e, ao mesmo tempo, organização entre acertos simples e divisões em grupo.

```mermaid
erDiagram
    CONTACTS ||--o{ SETTLEMENTS : "tem (contact_id)"
    CONTACTS ||--o{ CONTACT_SETTLEMENT_ARCHIVES : "arquiva (contact_id)"
    SETTLEMENT_GROUPS ||--o{ SETTLEMENTS : "agrupa (settlement_group_id)"
    FINANCIAL_TRANSACTIONS |o--o{ SETTLEMENTS : "gera (financial_transaction_id)"
    FINANCIAL_TRANSACTIONS |o--o| SETTLEMENT_GROUPS : "gera (financial_transaction_id)"

    SETTLEMENT_GROUPS {
        bigint id PK
        string description
        decimal total_amount
        date date
        string mode
        decimal my_amount
        bigint financial_transaction_id FK
    }

    SETTLEMENTS {
        bigint id PK
        bigint contact_id FK
        bigint settlement_group_id FK
        bigint financial_transaction_id FK
        string type "TheyOwe, TheyPaid, IOwe, IPaid"
        decimal amount
        string description
        date date
    }

    CONTACT_SETTLEMENT_ARCHIVES {
        bigint id PK
        bigint contact_id FK
        timestamp archived_at
    }
```

- `settlements` (Acertos Individuais): É a entidade base. Cada registro representa uma movimentação unidirecional entre o usuário e um Contato. Contém o valor, descrição, data e o `type` (seja *TheyOwe*, *TheyPaid*, *IOwe*, *IPaid*).
- `settlement_groups` (Despesas em Grupo): Entidade pai que agrupa múltiplos `settlements`. Ela guarda o valor total da despesa (`total_amount`), a descrição geral e a data. Utilizado em rachadinhas (ex: Pizza, Viagens) para permitir edição em massa e rateio simplificado.
- `contact_settlement_archives` (Arquivamento de Saldo): Utilizado para a funcionalidade de "Zerar Saldo". Em vez de apagar os registros antigos, eles são arquivados para manter o histórico, mas retirados do cálculo do saldo atual do contato.

### Acertos Simples vs. Despesas em Grupo

O módulo suporta dois fluxos de trabalho distintos:
- **Acertos Individuais:** Você e uma outra pessoa (ex: "Emprestei 50 pro meu irmão"). Salvo diretamente na tabela `settlements`.
- **Despesas em Grupo:** Quando você paga uma conta que envolve múltiplas pessoas (ex: "Paguei o jantar, João me deve 30 e Maria me deve 40"). Cria-se um `settlement_group` pai e múltiplos filhos (`settlements`), facilitando a gestão do valor global.

### Integração com o Módulo Financeiro

Apesar de funcionarem em regime de competência (promessas de pagamento), a integração com o Regime de Caixa (Módulo Financeiro) é transparente e opcional:
- Ao criar um acerto (ou grupo de acertos), o usuário tem um *toggle* na interface (`Criar transação no módulo financeiro`).
- Se ativo, o sistema criará simultaneamente uma transação financeira (`financial_transactions`) atrelada à conta bancária ou cartão de crédito especificado.
- A Foreign Key `financial_transaction_id` nas tabelas de Acertos vincula permanentemente essas entidades.
- Quando o Acerto ou Grupo é apagado, as transações financeiras correspondentes são varridas do banco permanentemente via `forceDelete`, mantendo as finanças blindadas contra lixo residual de dívidas canceladas.

### Arquivamento (Zerar Dívidas)

Quando você atinge o ponto de equilíbrio de débitos com um contato, em vez de excluir todo o histórico, você clica em "Zerar". 
Isso cria um registro na tabela `contact_settlement_archives` com a data/hora atual. 
Nas listagens (dashboards e ledger do contato), o Laravel usa um Local Scope para filtrar apenas os acertos criados *depois* do último arquivamento. 
Ainda é possível visualizar o histórico completo através do toggle "Visualizar Arquivados".

### Referências
- [Roadmap — Módulo de Acertos](/james/docs/roadmap/#fase-4-módulo-de-acertos)
- [Inspiração Original — BalanceFlow](https://github.com/ArthurWillers/BalanceFlow)
