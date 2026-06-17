---
title: "Roadmap"
date: 2026-06-09T13:04:13-03:00
draft: false
---

Funcionalidades planejadas por projeto. Atualizado conforme o desenvolvimento avança.

## Fases Iniciais

### Fase 1: Fundação
- [x] Autenticação Fortify (usuário único)
- [x] Layout Base (Blade Components, TailwindCSS, Sidebar, Topbar)
- [x] Configuração Docker/Sail com PostgreSQL
- [x] Documentação Hugo (Hextra)

### Fase 2: Módulo Contatos
- [x] Cadastro dinâmico de contatos (Nome, Múltiplos Telefones e E-mails em JSON, Categoria, Foto)
- [x] Gestão e edição ricas (Soft Deletes, lixeira de contatos, anotações em Markdown com EasyMDE)
- [x] Gerenciamento automático de Avatar com conversão para WebP usando Spatie MediaLibrary
- [x] Entidades puramente passivas sem acesso ao sistema

### Fase 3: Módulo Financeiro
- [ ] Múltiplas contas (dinheiro físico, conta corrente, investimentos)
- [ ] Correção manual de saldo mensal com registro de variação
- [ ] Cartão de crédito com fatura, data de fechamento e vencimento
- [ ] Compras parceladas com geração automática de parcelas futuras
- [ ] Transações recorrentes via Laravel Scheduler (salário, aluguel, assinaturas)
- [ ] Transferências entre contas
- [ ] Sistema de tags (no lugar de categorias, polimórfico, múltiplas por transação)
- [ ] Leitura de QR Code de cupom fiscal (NFC-e)

### Fase 4: Módulo de Acertos
- [ ] Gestão de "Eu Devo" e "Me Devem" (Regime de Competência)
- [ ] Histórico de despesas compartilhadas
- [ ] Grupos de divisões frequentes
- [ ] Vinculação obrigatória com os contatos do CRM
- [ ] Integração de liquidação com o Módulo Financeiro

## Próximas Fases (Futuro)

### Outros Módulos Planejados
- Tarefas / Projetos
- Hábitos
- Saúde
- Conhecimento
- Despensa e Receitas Culinárias
- Manutenção e Patrimônio
- Viagens
- Imposto de Renda
- Dashboard Unificado
- Assistente IA (James JARVIS)