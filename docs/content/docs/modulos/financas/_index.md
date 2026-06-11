---
title: "Finanças"
weight: 10
date: 2026-06-09T12:41:14-03:00
draft: false
---

## Visão Geral

O módulo de Finanças tem como objetivo principal o controle e planejamento da vida financeira do usuário, consolidando diferentes tipos de contas e monitorando entradas, saídas e movimentações. Ele proporciona uma visão clara do patrimônio e dos gastos, facilitando o acompanhamento mensal e a tomada de decisão através de funcionalidades ricas como tags, controle de cartões de crédito e automações.

## Contas

Este submódulo permite o gerenciamento de múltiplos tipos de contas, sendo elas dinheiro físico, conta corrente e contas de investimentos. O sistema suporta a correção manual de saldo mensal, que realiza automaticamente o registro de variação patrimonial, garantindo que o saldo no sistema reflita a realidade sem perder o histórico das atualizações.

## Cartão de Crédito

A gestão de cartões de crédito é focada no ciclo de faturamento. Cada cartão possui uma fatura consolidada, com suas respectivas datas de fechamento e vencimento. O sistema diferencia o momento em que a compra ocorre do momento em que o dinheiro efetivamente sai do saldo do usuário, ocorrendo apenas no pagamento da fatura, permitindo um planejamento financeiro mais preciso para os meses seguintes.

## Transações

As transações englobam todas as entradas e saídas de valores do sistema. Em vez de utilizar um sistema rígido de categorias, as transações utilizam um sistema polimórfico de tags, permitindo que múltiplas tags sejam associadas a uma mesma transação.
Além disso, o sistema suporta compras parceladas, gerando automaticamente as parcelas futuras e integrando-as nos meses correspondentes. Também é possível configurar transações recorrentes utilizando o Laravel Scheduler, sendo ideal para automatizar lançamentos de salário, aluguel, assinaturas de serviços, entre outros.

## Transferências entre Contas

Transferências são tratadas como duas transações vinculadas: uma saída na conta de origem e uma entrada na conta de destino. Essa lógica garante o equilíbrio contábil do sistema, evitando a criação de "dinheiro invisível" ou perdas injustificadas no saldo total.

## Nota Fiscal

Para facilitar o registro de despesas, o módulo possui uma funcionalidade de leitura de QR Code de cupom fiscal (NFC-e), realizando a consulta automática à SEFAZ-RS. Ao ler uma nota, os itens da nota fiscal são registrados como subtransações, onde cada item pode ter suas próprias tags individuais. Isso permite uma granularidade muito maior no controle de despesas de supermercado, farmácia, etc.

## Integrações

O módulo de Finanças foi projetado para integrar-se ativamente com outras áreas do sistema:
- **Despesas Compartilhadas**: Lançamentos financeiros podem ser integrados diretamente no módulo de divisão de custos com outros participantes.
- **Assistente IA (James JARVIS)**: Futuramente, a IA realizará a classificação automática das tags com base no histórico do usuário e nos padrões de gastos.
