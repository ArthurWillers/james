---
title: "Finanças"
weight: 3
date: 2026-06-09T12:41:14-03:00
draft: false
---

### Visão Geral
O módulo Financeiro é o núcleo de controle patrimonial do James.
A regra de ouro é estruturação relacional — dados financeiros precisam
ser somados, agrupados e cruzados em relatórios. O uso de JSONB foi
descartado para garantir integridade e performance nas consultas.

### Contas (`financial_accounts`)
Tabela única para todas as carteiras — dinheiro físico (`wallet`), conta corrente (`checking`)
e investimentos (`investment`). Sem tabelas separadas de bancos.
Ícones dinâmicos via `FinancialAccountType` e suporte a chaves Pix.

### Transações — Estrutura Pai e Filho
- `financial_transactions` (Pai) — o registro do pagamento na totalidade:
  conta, valor, data, cartão, status (`TransactionStatus`: Draft, Pending, Posted) e anexos.
- `financial_transaction_items` (Filhos) — detalhamento dos itens,
  especialmente útil para itens de nota fiscal. Garante relatórios
  precisos por item.

### Cartão de Crédito
Controle de faturas (`financial_credit_card_invoices`) com data de fechamento e vencimento (ajustadas por feriados via BrasilAPI).
Status gerenciado via `InvoiceStatus` (Paid, PartiallyPaid, Open, Overdue, Closed).
Gastos no cartão não afetam o saldo imediatamente — a saída ocorre quando a fatura é paga.

### Parcelamentos e Recorrências
Compras parceladas geram parcelas futuras automaticamente vinculadas à
transação original. Transações recorrentes (salário, aluguel, assinaturas)
são processadas via Laravel Scheduler diariamente.

### Transferências entre Contas
Gera duas transações vinculadas (`transfer_pair_id`) — saída na conta A e entrada na conta B.
O saldo total não é afetado.

### Sistema de Tags
Substitui o sistema rígido de categorias. Tabela `financial_tags` com `name`, `icon` (Heroicons, Tabler e Phosphor) e `color_hex`.
Tabela pivot polimórfica `financial_taggables` com `is_primary` — permite vincular tags tanto
na transação pai quanto em itens filhos específicos.

### Relatórios e Dashboards (Apache ECharts)
Visualizações analíticas em Regime de Caixa (Evolução de Saldo, Diagrama de Sankey do fluxo de dinheiro, drill-down dinâmico por tags e isolamento de investimentos).

### Importação de NFC-e
Importação assíncrona de notas fiscais de consumidor a partir de URLs públicas ou da leitura do QR Code pela câmera, inicialmente pelo portal SVRS. A nota é criada como rascunho, com emitente, documento, itens, descontos e metadados fiscais, sem afetar os cálculos financeiros até a revisão. Falhas podem ser reenviadas pela notificação recebida pelo usuário.

Consulte a [documentação da Importação de NFC-e](./nfce/).

### Integrações
- **Módulo Acertos** — pagamentos e liquidações refletem como transações financeiras.
- **Módulo Notificações** — avisos de faturas fechadas, lembretes de vencimento e alertas via Telegram / E-mail.
- **Módulo de Auditoria** — rastreabilidade de todas as alterações em transações, contas e cartões.

### Referências
- [Roadmap — Módulo Finanças](/james/docs/roadmap/#fase-3-módulo-financeiro)
- [Decisão 007 — Padronização de Data e Moeda](/james/docs/decisoes/#007--padronização-de-data-moeda-e-locale)
- [Decisão 014 — Biblioteca Gráfica (Apache ECharts)](/james/docs/decisoes/#014--biblioteca-gráfica-apache-echarts)
