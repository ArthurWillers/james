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
Tabela única para todas as carteiras — dinheiro físico, conta corrente 
e investimentos. Sem tabela de bancos separada.
Colunas visuais para identificação rápida na UI: `logo_path` e `color_hex`.
Correção manual de saldo mensal com registro automático da variação 
(útil para renda variável).

### Transações — Estrutura Pai e Filho
- `financial_transactions` (Pai) — o registro do pagamento na totalidade: 
  conta, valor, data, cartão, status de efetivação.
- `financial_transaction_items` (Filhos) — detalhamento dos itens, 
  especialmente útil para itens de nota fiscal. Garante relatórios 
  precisos por item.

### Cartão de Crédito
Controle de fatura com data de fechamento e vencimento. Gastos no cartão 
não afetam o saldo imediatamente — a saída ocorre quando a fatura é paga.

### Parcelamentos e Recorrências
Compras parceladas geram parcelas futuras automaticamente vinculadas à 
transação original. Transações recorrentes (salário, aluguel, assinaturas) 
são processadas via Laravel Scheduler diariamente.

### Transferências entre Contas
Gera duas transações vinculadas — saída na conta A e entrada na conta B. 
O saldo total não é afetado.

### Sistema de Tags
Substitui o sistema de categorias. Tabela `financial_tags` com apenas 
`name` e `icon` (componentes Blade via Heroicons). Sem cores para evitar 
poluição visual.
Tabela pivot polimórfica `taggables` — permite vincular uma tag tanto 
na transação pai quanto em um item filho específico da nota fiscal.

### Nota Fiscal (NFC-e)
Leitura de QR Code do cupom fiscal com consulta à SEFAZ-RS.
Cada item da nota vira um `financial_transaction_item` com suas próprias tags. 
Suporte futuro a outros estados.
IA para classificação automática de tags prevista para o James JARVIS.

### Integrações
- Módulo Acertos — pagamentos do grupo refletem como transação efetivada
- James JARVIS (futuro) — classificação automática de tags via IA

### Referências
- [Roadmap — Módulo Finanças](/james/docs/roadmap/#fase-3-módulo-financeiro)
- [Decisão 007 — Padronização de Data e Moeda](/james/docs/decisoes/#007--padronização-de-data-moeda-e-locale)
