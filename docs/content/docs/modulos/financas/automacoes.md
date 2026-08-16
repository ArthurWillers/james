---
title: "Rotinas Automáticas (Schedulers)"
weight: 8
date: 2026-07-05T23:14:00-03:00
draft: false
---

### Visão Geral

Para que o módulo financeiro funcione de forma fluida — garantindo que faturas de cartão avancem os meses, recorrências se transformem em transações reais e pendências atrasadas continuem aparecendo até serem pagas — o sistema utiliza o **Laravel Scheduler** configurado em `routes/console.php`.

Em produção, o agendador é mantido em execução contínua pelo **Supervisor** através do comando `php artisan schedule:work` (conforme detalhado no [Guia de Deploy](/docs/deploy/#7-schedulers-e-processos-em-background-supervisor)).

### Comandos Agendados Diariamente

Atualmente o sistema registra 3 comandos diários (`->daily()`):

#### 1. Processamento de Recorrências
**Comando:** `php artisan finance:process-recurrences`

Este comando percorre a tabela `financial_recurrences` e procura por registros onde a data do próximo processamento (`next_processing_date`) chegou ou já passou.
Ao identificar uma recorrência válida, ele gera a transação correspondente (conta, cartão, valor, tags) na tabela `financial_transactions` com o status apropriado (`Posted` para contas correntes ou `Pending` vinculada à fatura de cartão) e recalcula a próxima data com base na frequência da recorrência (Mensal, Anual, Semanal).

#### 2. Rollover de Faturas de Cartão de Crédito
**Comando:** `php artisan finance:rollover-invoices`

Garante que os cartões de crédito continuem o seu ciclo temporal. Ele verifica todos os cartões ativos e força a existência/abertura de uma fatura para o período atual (`resolveForDate`). É este comando que garante que, virando o mês, uma nova fatura seja exibida visualmente para o usuário, independentemente de haver novas compras ou não.

#### 3. Rolagem de Transações Pendentes (Rollover Due)
**Comando:** `php artisan finance:rollover-transactions`

Despesas ou receitas cadastradas que não foram marcadas como pagas (status `pending`) correm o risco de ficarem perdidas no passado se o usuário não abrir o sistema na data correta. Este comando identifica transações que venceram e continuam pendentes, atualizando a data (`date`) delas para o dia atual. Assim, quando o usuário acessar o sistema, a obrigação continuará cobrando-o (aparecendo no painel como pendente de hoje) até que ele a pague de fato ou a exclua.

### Rastreabilidade no Módulo de Auditoria

Todas as mutações de banco de dados disparadas por estes comandos são registradas automaticamente no [Módulo de Auditoria](/docs/modulos/auditoria/). Como essas operações não partem de uma sessão HTTP autenticada, o autor é identificado como **"Sistema / Rotina Automática"** (`causer_id = null`).

