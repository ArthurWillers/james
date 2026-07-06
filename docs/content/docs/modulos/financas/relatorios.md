---
title: "Relatórios"
weight: 6
---

## Visão Geral

O Módulo de **Relatórios** (`ReportsService`) fornece visualizações profundas do passado financeiro para auditoria, e do futuro para planejamento. Ao contrário do Dashboard, que consolida apenas os 30 dias presentes, os relatórios são desenhados para suportar queries de longo prazo (anos) e aplicar achatamentos (`flattening`) avançados para gerar gráficos compreensivos (Sankey, Evolução Patrimonial, Análise de Tags).

## Arquitetura de Coleta (`getUnifiedTransactions`)

O núcleo motor dos relatórios é o método unificador. Para evitar problemas de formatação múltipla e inconsistências, o sistema condensa o passado e o futuro em um único tubo de dados, mesclando:
1. **Transações Reais**: O histórico concreto do banco de dados (ignorando Transferências).
2. **Transações Virtuais**: Instancia e "rebobina" a lógica das Recorrências ativas até a data de início do filtro, projetando datas futuras com a periodicidade adequada (`weekly`, `monthly`, `yearly`).

Essa coleção híbrida abastece todos os métodos subsequentes (gráficos), eliminando a necessidade de recalcular projeções de banco de dados para cada gráfico.

## Relatórios e Gráficos Gerados

### 1. Diagrama de Sankey (Fluxo de Dinheiro)
Ilustra de forma orgânica de onde veio e para onde foi o dinheiro no período especificado.
- **Achatamento de Itens (`flattenTransactionsForTags`)**: Para que uma única despesa em mercado pudesse refletir, por exemplo, R$50 de "Açougue", R$30 de "Limpeza" e R$20 de "Cerveja" (quebrando a transação através da tabela `financial_transaction_items`), o sistema expande cada item em uma pseudo-transação própria conectada ao *node* central. O resíduo (ex: desconto não mapeado) fica associado à tag principal.
- **Lógica de Construção**: Agrupa despesas e receitas pela Tag Primária e conecta tudo a um Nodo central ("Fluxo de Caixa"). Também integra o Saldo Anterior (antes do período) e resulta em um Nodo Final ("Saldo Líquido" ou "Uso de Reservas/Déficit").

### 2. Evolução Patrimonial e Caixa (`buildEvolutionData`)
Constrói gráficos de linha/área cumulativos.
- **Interpolação de Datas**: Independentemente de não haverem registros em um dia/mês, o sistema gera o array cobrindo todos os espaços do intervalo (`interval = daily, weekly, monthly, yearly`) para não "quebrar" a renderização gráfica do eixo X no frontend.
- Diferença entre Evolução de Caixa (considera faturas abertas projetadas na data de vencimento e pagas na data `paid_at`) e Patrimônio Líquido (soma de bens imobilizados e caixas).

### 3. Análise de Categorias (Tags)
Agrega a mesma coleção de transações unificadas e fatiadas (`flattened`), fornecendo:
- O total geral de Gastos vs Receitas.
- Gastos concentrados agrupados pela Tag Primária (útil para gráficos de pizza / donut).
- **Net Tags** (Tags Líquidas): Uma análise que mescla as mesmas categorias de Despesa e Receita (Ex: "Freelance", onde houve entrada de dinheiro, mas também uma taxa bancária mapeada para a mesma Tag), exibindo o Lucro/Prejuízo real focado naquele nicho específico.

## Ocultamento Estratégico

### Pagamentos Parciais e Transferências
Transações associadas a `PAGAMENTO_PARCIAL_ID` ou `TRANSFERENCIA_ID` são expurgadas dos cálculos de Evolução de Patrimônio.
Isso ocorre porque uma transferência não altera a riqueza total (apenas tira de um bolso e coloca em outro). Da mesma forma, um pagamento parcial de fatura apenas move liquidez, e só é contabilizado de fato na efetivação consolidada dos encargos. 

### Ajuste de "Data Efetiva" de Faturas de Cartão
O motor é programado para utilizar a seguinte ordem de precedência de datas no gráfico financeiro para faturas:
1. `paid_at` (quando o dinheiro realmente saiu, para faturas liquidadas).
2. `due_date` (para faturas abertas que vencerão e comprometerão liquidez em determinado dia).
3. `date` da transação subjacente (fallback de segurança).
