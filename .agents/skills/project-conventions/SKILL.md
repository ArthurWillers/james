---
name: project-conventions
description: Use this skill for general architectural decisions and coding conventions in this project. Activate when creating views, refactoring code, or registering components.
---

# Project Conventions

Sempre siga essas regras ao programar neste projeto, pois elas foram definidas pelo desenvolvedor principal. Se necessário, consulte arquivos adicionais na pasta `references/` ou `examples/`.

## Frontend e Blade

1. **Componentes Blade (Sem Alias):** O Laravel já resolve os componentes anonimamente baseando-se no nome e nas pastas. NÃO use aliases como `ui.` ou `form.`. (Incorreto: `<x-ui.avatar>`, Correto: `<x-avatar>`).
2. **Diretivas do AlpineJS:** Sempre coloque os atributos do Alpine (`x-data`, `x-show`, `@click`, etc.) no final das tags HTML, obrigatoriamente APÓS `class` e `style`, para não quebrar o syntax highlighting da IDE.
3. **Helpers Globais para Views:** NUNCA formate dados brutos manualmente nas views. Use as funções injetadas pelos Helpers (`formatShort()`, `formatDateTime()`, `formatMonthYear()`, `formatCurrency()`). Apenas em `<input type="date">` é permitido o `->format('Y-m-d')`.
4. **Textos e Traduções:** Textos nas views Blade devem ser injetados diretamente em português (Hardcoded). Não utilize o sistema de tradução (`__('texto')`), dado que o escopo é local.
5. **TailwindCSS (Sintaxe Canônica):** Utilize a sintaxe canônica do Tailwind v4 (`!`) no final da classe ao invés de prefixá-la (Incorreto: `!text-white`, Correto: `text-white!`).
6. **TailwindCSS (Sem Valores Arbitrários):** Utilize estritamente os utilitários de escala do Tailwind e cores semânticas (`text-neutral-500`, `bg-primary-600`). É terminantemente proibido o uso de valores arbitrários (ex: `w-[15px]`, `bg-[#333]`).
7. **Ícones:** Utilize exclusivamente a biblioteca Heroicons (`<x-heroicon-...>`). Não adicione SVGs manualmente nas views.

## UI e UX

8. **Consistência Visual:** Qualquer nova interface deve obrigatoriamente seguir a identidade visual do restante do sistema. Analise o design de telas similares antes de criar algo novo.
9. **Reutilização de Componentes:** Sempre priorize o uso de componentes Blade já existentes no projeto (`<x-card>`, `<x-button>`, `<x-page-header>`). Evite criar HTML estrutural do zero.
10. **Responsividade (Desktop-First):** O foco principal é **Desktop-First**. A versão mobile deve existir como uma adaptação simplificada e limpa para quebrar um galho (regras mais rígidas virão no futuro).
11. **Feedback Visual:** Interações que envolvam processos assíncronos DEVEM possuir *loading states* e desabilitar os botões temporariamente para evitar cliques duplos.

## Backend e Arquitetura

12. **Lógica de Negócio:** A lógica principal deve ser mantida nos Controllers. Não há necessidade de extrair para classes genéricas de Ação (Actions/Services), a menos que a complexidade seja excessivamente alta.
13. **Validação de Formulários:** Sempre utilize FormRequests dedicados para validações no backend em vez de usar `$request->validate()` diretamente nos Controllers.

## Workflow e Ferramentas

14. **Documentação:** Se for solicitada a criação ou atualização de documentação, os arquivos devem ser criados DENTRO do diretório `/docs` seguindo o padrão de escrita existente.
15. **Formatação de Código (Pint):** Sempre que arquivos PHP forem criados ou alterados, o agente DEVE rodar o formatador executando `./vendor/bin/pint` nativamente (sem usar Sail).
16. **Evolução Contínua (Meta-regra):** Sempre que o agente identificar um padrão repetitivo não documentado ou tomar uma nova decisão importante de design com o usuário, o agente DEVE sugerir proativamente a adição desse novo mandamento neste arquivo.
