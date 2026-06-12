---
title: "Visão Geral"
weight: 1
date: 2026-06-09T12:41:14-03:00
draft: false
---

# Módulo CRM (Personal CRM)

O Módulo CRM é a espinha dorsal de identificação do "James". Dentro do contexto de um "Life OS", ele atua como a **Fonte Única de Verdade (Single Source of Truth - SSOT)** para todas as pessoas com quem o usuário interage.

O objetivo principal é centralizar os dados de contatos, evitando duplicação em outros módulos e permitindo que qualquer parte do sistema que necessite identificar uma pessoa o faça referenciando os registros do CRM.

## Regras de Negócio e Decisões Arquiteturais

### 1. Entidades Passivas (Sem Autenticação)
Os contatos cadastrados no CRM são entidades puramente passivas. O James é um sistema *Single User*, o que significa que os contatos **não possuem login, senha ou acesso ao sistema**. Eles existem unicamente como referência interna para o próprio usuário gerenciar seus relacionamentos, dívidas, tarefas delegadas, etc.

### 2. Escopo Minimalista Inicial
Nesta primeira versão, o CRM adota uma abordagem extremamente minimalista e enxuta, contendo apenas o estritamente necessário para viabilizar as funcionalidades dos próximos módulos (como Finanças e Acertos). Não há campos complexos de endereço, múltiplos e-mails ou histórico corporativo neste momento inicial.

### 3. Extensibilidade e Chaves Estrangeiras
A estrutura foi desenhada pensando na integração. Outros módulos irão utilizar o ID do contato como Chave Estrangeira (Foreign Key). Isso garante que, se o nome ou a chave PIX de um contato for atualizada no CRM, essa alteração refletirá imediatamente em todos os acertos, transações ou atividades atreladas a ele.
