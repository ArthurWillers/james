---
title: "Relatórios"
weight: 6
---

## Visão Geral

O Módulo de **Relatórios** (`ReportsService`) fornece visualizações profundas do passado financeiro para auditoria, e do futuro para planejamento. Ao contrário do Dashboard, que consolida apenas os 30 dias presentes, os relatórios são desenhados para suportar queries de longo prazo (anos) e aplicar achatamentos (`flattening`) avançados para gerar gráficos compreensivos (Sankey, Evolução de Saldo em Regime de Caixa, Análise de Tags com Drill-down).

## Arquitetura de Coleta (`getUnifiedTransactions`)

O núcleo motor dos relatórios é o método unificador. Para evitar problemas de formatação múltipla e inconsistências, o sistema condensa o passado e o futuro em um único tubo de dados, mesclando:
1. **Transações Reais**: O histórico concreto do banco de dados (respeitando filtros de contas, período e tags).
2. **Transações Virtuais**: Instancia e "rebobina" a lógica das Recorrências ativas até a data de início do filtro, projetando datas futuras com a periodicidade adequada (`weekly`, `monthly`, `yearly`).

Essa coleção híbrida abastece todos os métodos subsequentes (gráficos), eliminando a necessidade de recalcular projeções de banco de dados para cada gráfico.

## Relatórios e Gráficos Gerados

### 1. Diagrama de Sankey (Fluxo de Dinheiro)
Ilustra de forma orgânica de onde veio e para onde foi o dinheiro no período especificado:
- **Achatamento de Itens (`flattenTransactionsForTags`)**: Para que uma única despesa em mercado possa refletir, por exemplo, R$50 de "Açougue", R$30 de "Limpeza" e R$20 de "Bebidas" (quebrando a transação através da tabela `financial_transaction_items`), o sistema expande cada item em uma pseudo-transação própria conectada ao *node* central. O resíduo fica associado à tag principal.
- **Lógica de Construção**: Agrupa despesas e receitas pela Tag Primária e conecta tudo a um Nodo central ("Fluxo de Caixa"). Também integra o Saldo Anterior (antes do período) e resulta em um Nodo Final ("Saldo Líquido" ou "Uso de Reservas/Déficit").

### 2. Evolução de Saldo e Caixa (Regime de Caixa)
Constrói gráficos de linha/área cumulativos alinhados estritamente ao **Regime de Caixa** (quando o dinheiro efetivamente entrou ou saiu das contas):
- **Interpolação de Datas**: Independentemente de não haverem registros em um dia/mês, o sistema gera o array cobrindo todos os espaços do intervalo (`interval = daily, weekly, monthly, yearly`) para manter a continuidade do eixo X.
- **Tratamento de Cartão de Crédito**: Para faturas quitadas, a dedução de saldo ocorre na data exata do pagamento (`paid_at`). Para faturas futuras ou abertas, o impacto é projetado na data de vencimento (`due_date`).

### 3. Análise de Tags e Drill-down Dinâmico
Agrega a coleção de transações unificadas e fatiadas (`flattened`), fornecendo:
- O total geral de Gastos vs Receitas no período selecionado.
- Gastos concentrados agrupados pela Tag Primária (gráfico de pizza / donut e barras).
- **Drill-down Interativo**: Ao selecionar tags específicas no filtro superior, tanto a tabela analítica de transações quanto os gráficos convergem instantaneamente para o conjunto fatiado, permitindo auditar o fluxo de micro-categorias.
- **Net Tags (Tags Líquidas)**: Consolida o fluxo líquido de tags que possuem entradas e saídas simultâneas (ex: "Freelance", "Viagem", "Reembolso").

## Filtros e Escopos Inteligentes

### Isolamento de Investimentos
O usuário pode optar por incluir ou excluir contas do tipo "Investimento". Ao ativar a exclusão:
- Transações diretas em contas de investimento são removidas.
- Transferências enviadas para corretoras são tratadas como alocação de capital e filtradas sem distorcer o fluxo de despesas de consumo do dia a dia.

### Pagamentos Parciais e Transferências Internas
Transações associadas a `PAGAMENTO_PARCIAL_ID` ou `TRANSFERENCIA_ID` são expurgadas dos cálculos de Evolução de Saldo para evitar contagem dupla de patrimônio (já que uma transferência entre contas correntes não gera riqueza nem custo real).

