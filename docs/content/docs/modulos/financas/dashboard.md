---
title: "Dashboard"
weight: 5
---

## Visão Geral

O **Dashboard** atua como a central nervosa diária do usuário (`FinanceDashboardService`). Ele foca no tempo presente, fornecendo uma visão instantânea da liquidez e do que está prestes a acontecer nos próximos dias. Ele centraliza informações complexas utilizando consultas otimizadas e lógica de projeção.

## Fluxo de Processamento (Motor)

O serviço do Dashboard utiliza uma estratégia de agregação que coleta dados de diversas origens (Tabelas) para fornecer uma resposta rápida à interface:

```mermaid
flowchart TD
    A[Dashboard Controller] -->|Solicita Dados| B(FinanceDashboardService)
    B --> C{Cache Inteligente}
    C -->|Coleta Contas| D[(FinancialAccounts)]
    C -->|Coleta Cartões| E[(FinancialCreditCards)]
    C -->|Coleta Faturas| F[(FinancialInvoices)]
    C -->|Coleta Transações| G[(FinancialTransactions)]
    
    C --> H[Cálculo de Projeção]
    H --> I[Indicadores de KPI]
    H --> J["Projeção de Saldo (Mês Atual e Próximo)"]
    H --> K[James Radar]
```

## Regras de Negócio e Componentes

### Indicadores KPI (Liquidez e Saldos)
O serviço consolida quatro indicadores principais baseados no "aqui e agora":
1. **Saldo Líquido (Net Balance)**: `Saldo de Contas - Dívidas Abertas (Faturas Cartão) - Despesas Pendentes de outras fontes`.
2. **Saldo Bruto**: Simples soma do patrimônio nas contas correntes/investimentos configurados.
3. **Receitas**: Total efetivado no mês.
4. **Despesas**: Total efetivado no mês.

*Nota:* O usuário possui a opção (`includeInvestments`) de somar ou remover contas do tipo "Investimento" deste cálculo, para isolar a liquidez do dia-a-dia do dinheiro imobilizado.

### Projeção de Fluxo de Caixa (Mês Vigente e Seguinte)
Esta funcionalidade consolida transações reais postadas, transações reais pendentes e **transações virtuais geradas a partir de recorrências ativas**.
O algoritmo de extrapolação (`getCashFlowProjections`):
1. Captura o saldo em conta corrente atual.
2. Soma as `incomes` pendentes e `incomes` oriundos de recorrências do mês atual.
3. Subtrai as `expenses` pendentes, o montante devido em Faturas de Cartão e `expenses` oriundos de recorrências do mês atual.
4. Retorna a previsão de Saldo Final para o Mês Atual.
5. Usa essa previsão como base e repete o processo 2-3 para o Mês Seguinte, retornando a Previsão do Próximo Mês.

### James Radar (O que vem a seguir)
O **James Radar** é o assistente inteligente que lista as próximas obrigações e receitas em ordem cronológica a partir de "Hoje" até os próximos 30 dias.
Para fazer isso, ele constrói uma coleção híbrida, instanciando classes em memória:
- **Transações Pendentes reais** (exclui transferências).
- **Recorrências Ativas** cujo `next_processing_date` está no intervalo.
- **Faturas de Cartão Abertas** cujos dias de vencimento estão no intervalo.

Todas essas entidades assumem um "formato de pato" (`duck typing`), atuando perante a UI como se fossem Transações normais pendentes, facilitando a renderização na tabela sem código duplicado na View.

### Controles Otimizados de Fatura
O cálculo de qual fatura o usuário deve ver no Dashboard (`resolveReferenceMonth`) não bate no banco de dados para buscar a melhor data; ele usa lógica pura computando dias de fechamento (`closing_day`) em relação a hoje e injeta dinamicamente o status e total diretamente no model retornado (`card->current_invoice_total`).
