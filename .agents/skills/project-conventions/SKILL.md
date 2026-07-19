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
6. **TailwindCSS (Sem Valores Arbitrários):** Utilize estritamente os utilitários de escala do Tailwind e cores semânticas (`text-neutral-500`, `bg-primary-600`). É terminantemente proibido o uso de valores arbitrários (ex: `w-[15px]`, `bg-[#333]`). **Exceções e utilitários customizados**: É permitido o uso de valores arbitrários nas definições de grid (ex: `grid-cols-[1fr_auto_1fr]`). Para textos menores que `text-xs`, utilize a classe customizada `text-xxs` (10px) já configurada.
7. **Ícones:** Utilize a biblioteca Heroicons (`<x-heroicon-...>`) por padrão. Caso sinta falta de ícones específicos (ex: itens do cotidiano para tags), é permitido o uso de outras bibliotecas via pacotes do ecossistema Blade Icons (como Tabler Icons e Phosphor Icons). Não adicione SVGs manualmente nas views.

## UI e UX

8. **Consistência Visual:** Qualquer nova interface deve obrigatoriamente seguir a identidade visual do restante do sistema. Analise o design de telas similares antes de criar algo novo.
9. **Reutilização de Componentes:** Sempre priorize o uso de componentes Blade já existentes no projeto (`<x-card>`, `<x-button>`, `<x-page-header>`). Evite criar HTML estrutural do zero.
10. **Responsividade (Desktop-First):** O foco principal é **Desktop-First**. A versão mobile foca em cadastro e visualizações simples. **Obrigatório:** Consulte as regras detalhadas de mobile no arquivo `references/mobile-ui.md`.
11. **Feedback Visual:** Interações que envolvam processos assíncronos DEVEM possuir *loading states* e desabilitar os botões temporariamente para evitar cliques duplos.
12. **Botões no Cabeçalho (Page Header):** 
    - 1 botão: Deve ocupar 100% da largura no mobile (`w-full sm:w-auto`).
    - 2 botões: No mobile, cada um ocupa 50% (`flex-1 sm:flex-initial`).
    - 3+ botões: O mais à direita estica no mobile (`flex-1`) e os demais têm tamanho baseado no conteúdo. O botão Voltar sempre deve ter texto visível.
13. **Altura de Inputs (Princípio dos 44px):** Inputs grandes (texto, data, select, etc) devem ter obrigatoriamente a altura de 44px utilizando a classe `h-11` (ou `min-h-11`). Checkboxes, radios e switches estão isentos desta regra.
14. **Tabelas e Responsividade:** NUNCA esconda colunas ou esprema a tabela nativa em telas menores, e NÃO utilize scroll horizontal (`overflow-x-auto`). Toda tabela DEVE obrigatoriamente ter uma versão específica para celular usando o `<x-slot:mobile>`, transformando as linhas da tabela num formato de cards verticais.

## Backend e Arquitetura

15. **Lógica de Negócio:** A lógica principal deve ser mantida nos Controllers. Não há necessidade de extrair para classes genéricas de Ação (Actions/Services), a menos que a complexidade seja excessivamente alta.
16. **Validação de Formulários:** Sempre utilize FormRequests dedicados para validações no backend em vez de usar `$request->validate()` diretamente nos Controllers.
17. **Auditoria e Logs (Spatie v5+):** Utilize `spatie/laravel-activitylog` em todas as Models de negócio para rastreabilidade completa e vitalícia de mutações. Evite logar execuções técnicas de jobs/schedulers — apenas suas mutações via Eloquent. Trate `causer_id` nulo no frontend como "Sistema/Rotina Automática". **IMPORTANTE:** O uso da biblioteca possui regras inegociáveis (mass-assignment exclusivo via `$fillable`, `$recordEvents` dinâmico com SoftDeletes, logs do frontend, etc). Consulte obrigatoriamente `references/spatie-activitylog.md` para conhecer a sintaxe e as regras completas.
18. **Soft Deletes (Padrão Obrigatório):** Todos os Models DEVEM utilizar a trait `SoftDeletes` por padrão. Exceções são permitidas apenas para tabelas auxiliares puras (ex: pivôs simples, cache, tokens temporários) ou models específicos (ex: Tags), onde a exclusão definitiva não gera risco de perda de dados de negócio. Na dúvida, aplique `SoftDeletes`.

## Workflow e Ferramentas

18. **Documentação:** Se for solicitada a criação ou atualização de documentação, os arquivos devem ser criados DENTRO do diretório `/docs` seguindo o padrão de escrita existente.
19. **Formatação de Código (Pint):** Sempre que arquivos PHP forem criados ou alterados, o agente DEVE rodar o formatador executando `./vendor/bin/pint` nativamente (sem usar Sail).
20. **Controle de Versão (Git):** NUNCA execute comandos de commit (`git commit`) automaticamente. A permissão de commit **NÃO é cumulativa**: mesmo que o usuário tenha pedido para commitar na interação anterior, você NUNCA deve realizar novos commits sem uma nova autorização explícita a cada tarefa. Quando autorizado, siga rigorosamente o padrão de **Commits Semânticos** (ex: `feat:`, `fix:`, `chore:`, `refactor:`) garantindo mensagens curtas, claras e no idioma inglês para manter o padrão da indústria.
21. **Evolução Contínua (Meta-regra):** Sempre que o agente identificar um padrão repetitivo não documentado ou tomar uma nova decisão importante de design com o usuário, o agente DEVE sugerir proativamente a adição desse novo mandamento neste arquivo.
