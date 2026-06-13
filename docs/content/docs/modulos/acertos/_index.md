---
title: "Acertos"
weight: 4
---

### Visão Geral

O Módulo de Acertos foi idealizado para substituir o uso de planilhas e aplicativos como o Splitwise.

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
