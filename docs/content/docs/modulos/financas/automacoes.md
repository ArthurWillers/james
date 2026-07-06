---
title: "Rotinas Automáticas (Cron)"
weight: 8
date: 2026-07-05T23:14:00-03:00
draft: false
---

### Visão Geral

Para que o módulo financeiro funcione de forma fluida — garantindo que faturas de cartão avancem os meses, recorrências se transformem em transações reais e pendências atrasadas continuem aparecendo até serem pagas — o sistema utiliza o **Laravel Scheduler** configurado via arquivo `routes/console.php`. 

Para que estas automações operem em ambiente de produção, é indispensável que o servidor esteja com a entrada de cron padrão do Laravel configurada:

```bash
* * * * * cd /caminho-para-o-projeto && php artisan schedule:run >> /dev/null 2>&1
```

### Comandos Agendados Diariamente

Atualmente o sistema registra 3 comandos diários (`->daily()`).

#### 1. Processamento de Recorrências
**Comando:** `php artisan finance:process-recurrences`

Este comando percorre a tabela `financial_recurrences` e procura por registros onde a data do próximo processamento (`next_processing_date`) chegou ou já passou.
Ao identificar uma recorrência válida, ele gera a transação correspondente (conta, cartão, valor, tags) na tabela `financial_transactions` e recalcula a próxima data com base na frequência da recorrência (Mensal, Anual, Semanal, etc).

#### 2. Rollover de Faturas de Cartão de Crédito
**Comando:** `php artisan finance:rollover-invoices`

Garante que os cartões de crédito continuem o seu ciclo temporal. Ele verifica todos os cartões ativos e força a existência/abertura de uma fatura para o período atual. É este comando que garante que, virando o mês, uma nova fatura seja exibida visualmente para o usuário, independentemente de haver novas compras ou não.

#### 3. Rolagem de Transações Pendentes (Rollover Due)
**Comando:** `php artisan finance:rollover-transactions`

Despesas ou receitas cadastradas que não foram marcadas como pagas (efetivadas) correm o risco de ficarem perdidas no passado se o usuário não abrir o sistema na data correta. Este comando identifica transações que venceram e não foram pagas, atualizando a data (`date`) delas para o dia atual. Assim, quando o usuário acessar o sistema, a conta continuará cobrando-o (aparecendo no painel como pendente de hoje) até que ele a pague de fato ou a exclua.
