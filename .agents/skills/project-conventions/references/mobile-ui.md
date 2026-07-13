# Convenções de Interface Mobile

Este documento detalha as regras para o desenvolvimento de interfaces na versão mobile (telas pequenas), conforme a Decisão 017 (Escopo da Interface Mobile).

O foco no mobile é a **agilidade na entrada de dados** e a **leitura rápida de informações essenciais**.

## 1. Botões de Ação (Ações Primárias, Voltar / Cancelar / Salvar)
- **Desktop (`md` em diante):** Botões de ações primárias (ex: "Nova Transação") e botões de formulário devem ficar na parte superior (geralmente no cabeçalho da página ou do card).
- **Mobile:** *Todos* esses botões (inclusive o de criação em listagens) devem ser movidos para a parte inferior da tela, ao final do fluxo de leitura ou formulário.
- Os botões devem rolar normalmente junto com a página (não utilize FABs, `position: fixed` ou `sticky bottom`).
- **Apenas Ícones no Mobile (Icon-Only):** Para economizar espaço, em botões secundários ou de ações em listagens que possuem ícone e texto, o texto deve ser ocultado no celular, mantendo apenas o ícone visível (ex: envolva o texto em um `<span class="hidden md:inline">Texto</span>`).
- *Dica:* Utilize `hidden md:flex` para os botões do topo, e `flex md:hidden mt-4` para exibir os botões equivalentes no rodapé.

## 2. Tipografia Fluida e Prevenção de Quebras (Overflow)
- Valores financeiros ou textos longos podem quebrar o layout no celular.
- **Tipografia:** Utilize classes responsivas para KPIs numéricos e títulos (ex: `text-2xl md:text-4xl`).
- **Truncate:** Em listagens e cards pequenos, descrições longas devem, obrigatoriamente, utilizar a classe `truncate` para evitar quebras de linha que deformem o design.

## 3. Disposição de Cards de Indicadores (KPIs)
Para evitar que múltiplos cards de KPI ocupem toda a rolagem vertical da tela no mobile:
- **Até 3 Cards:** Exiba-os lado a lado no mobile usando `grid-cols-3`. Para evitar que o layout fique espremido, reduza drasticamente os paddings internos (ex: `p-2`) e os tamanhos de fonte (`text-xs` ou `text-sm`) especificamente para o mobile.
- **4 Cards (ex: Dashboard):** Utilize um grid de 2x2 no mobile (`grid-cols-2 gap-3`), distribuindo os itens em duas colunas e duas linhas.
- **Desktop:** Retorne ao grid original de linha única (ex: `md:grid-cols-3` ou `md:grid-cols-4`).

## 4. Ocultação Proativa de Elementos Densos
- Painéis complexos, gráficos pesados ou tabelas com muitas colunas prejudicam a usabilidade no celular e devem ser omitidos.
- Oculte esses elementos proativamente utilizando `hidden md:block` ou `hidden md:table`.
- **Aviso de Fallback:** Não polua a tela com blocos de "Acesse pelo computador" no lugar de cada elemento. Oculte os gráficos silenciosamente. Se necessário, inclua apenas uma única menção genérica e discreta no final da página inteira informando que a versão desktop possui relatórios adicionais.

## 5. Área Segura de Toque (Touch Targets)
- Elementos clicáveis isolados (como ícones de lixeira, botões de editar e checkboxes) precisam ter um tamanho mínimo amigável para o toque (idealmente 44x44px).
- Não utilize valores arbitrários no Tailwind (como `w-[44px]`). Em vez disso, alcance a área de clique de 44px (que corresponde a `2.75rem` no Tailwind) utilizando:
  - Classes padrão de dimensão: `size-11` ou `min-h-11 min-w-11`.
  - Ou aumentando o padding ao redor do ícone (ex: `p-3`), tornando a área interativa maior sem aumentar o ícone visualmente.

## 6. Redução Geral de Espaçamentos (Paddings)
- Para aproveitar o espaço limitado da tela, os paddings de containers e cards principais devem ser reduzidos.
- **Padrão Mobile:** Utilize `p-3` ou `p-4` (ex: `px-4 py-3`).
- **Padrão Desktop:** Aumente o respiro utilizando `md:p-6` ou superior.

## 7. Filtros Complexos
- Filtros de pesquisa que contêm múltiplos campos devem ser **ocultados por padrão** no mobile.
- **Mobile:** Utilize um botão "Filtros" que exiba os controles dentro de um Modal, Drawer (gaveta lateral/inferior) ou expanda uma seção.
- **Desktop:** Podem ficar visíveis em linha ou em um painel fixo, de acordo com o design.
