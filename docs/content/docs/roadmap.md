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
- [ ] Cadastro minimalista de contatos (Nome, Telefone, PIX, Grau de Relacionamento e Foto)
- [ ] Entidades puramente passivas sem acesso ao sistema
- [ ] Servirá de Single Source of Truth (SSOT) para o restante do sistema
- [ ] Extensibilidade estruturada para uso de `contact_id` como chave estrangeira por outros módulos

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