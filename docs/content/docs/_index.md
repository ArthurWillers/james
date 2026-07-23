---
title: ""
date: 
layout: "docs"
---

<div class="hx:mt-6 hx:mb-6">
{{< hextra/hero-headline >}}
  James
{{< /hextra/hero-headline >}}
</div>

<div class="hx:mb-6">
{{< hextra/hero-subtitle >}}
  ERP pessoal Multi-Tenant — documentação técnica e roadmap do projeto
{{< /hextra/hero-subtitle >}}
</div>

<div class="hx:mt-6"></div>

{{< hextra/feature-grid >}}
  {{< hextra/feature-card
    title="Instalação"
    subtitle="Guia de configuração do ambiente de desenvolvimento local"
    icon="code"
    link="instalacao/"
    class="hx:min-h-[280px]"
  >}}
  {{< hextra/feature-card
    title="Módulos"
    subtitle="Documentação de cada módulo do sistema"
    icon="puzzle"
    link="modulos/"
    class="hx:min-h-[280px]"
  >}}
  {{< hextra/feature-card
    title="Roadmap"
    subtitle="Funcionalidades planejadas e status de desenvolvimento"
    icon="list-check"
    link="roadmap/"
    class="hx:min-h-[280px]"
  >}}
  {{< hextra/feature-card
    title="Decisões"
    subtitle="Registros de decisões arquiteturais (ADRs)"
    icon="git-branch"
    link="decisoes/"
    class="hx:min-h-[280px]"
  >}}
{{< /hextra/feature-grid >}}

<div class="hx:mt-16 hx:mb-6">
  <h2>Por que o James? A Origem do "Life OS"</h2>
</div>

A ideia do James nasceu de uma necessidade que venho tentando resolver há algum tempo através de projetos isolados. Anteriormente, desenvolvi o **[Aurum](https://arthurwillers.github.io/docs/aurum2.0/)** para gestão financeira e o **[BalanceFlow](https://github.com/ArthurWillers/BalanceFlow)** para divisão de despesas. 

Embora esses projetos funcionassem muito bem individualmente, a vida não acontece em caixas separadas. Quando eu fazia uma transação financeira, muitas vezes ela envolvia um contato, que por sua vez fazia parte de um acerto de despesas. Manter vários sistemas separados começou a se tornar um gargalo de produtividade e consistência.

O James surgiu como o **"Life OS"** — um único sistema centralizado (Single Source of Truth) capaz de englobar CRM (Contatos), Finanças, Acertos, e expansível para qualquer outra área da vida. A ideia não é apenas gerenciar dinheiro, mas gerenciar a rotina cotidiana em um ambiente altamente privado e sob o meu próprio controle.

<div class="hx:mt-12 hx:mb-6">
  <h2>A Escolha da Stack: Lições do Passado</h2>
</div>

Se você acompanhou a história do Aurum, sabe das dores de cabeça que tive com gráficos interativos e Livewire. No James, o lema principal é a **simplicidade robusta**. Não quero "mágica" que eu não consiga manter depois de 6 meses.

- **Backend:** Laravel 13 (com PHP 8.5) — Produtivo, estruturado em MVC e seguro. Não havia por que mudar.
- **Frontend:** Blade, Alpine.js e TailwindCSS v4. Retornei ao básico renderizado pelo servidor, o que é ótimo para a performance e simplicidade. O Alpine.js entra apenas para interações locais (modais, tooltips, dropdowns), sem o overhead e os ciclos de vida complexos que o Livewire trazia.
- **Gráficos e Dashboards:** Em vez de brigar com reatividade pesada, utilizamos **Apache ECharts** integrado via Vanilla JavaScript para renderizar visualizações complexas (como o Diagrama de Sankey do fluxo de caixa), garantindo controle total da renderização na view.
- **Armazenamento e Privacidade:** Utilizamos o `spatie/laravel-medialibrary` configurado no disco privado. Nenhum dado (como avatares de contatos ou recibos financeiros) é exposto publicamente na web.

<div class="hx:mt-12 hx:mb-6">
  <h2>A Era Multi-Usuário</h2>
</div>

Inicialmente, o James foi desenhado de forma muito estrita para um **único usuário**. O banco de dados não possuía a noção de "a quem pertence esse dado". Mas logo ficou claro que, para o sistema ser útil a longo prazo, outras pessoas poderiam querer usar a mesma plataforma.

Assim nasceu a versão 2.0 (v2). Reformulamos a arquitetura do banco de dados para um formato **Single Database Multi-Tenancy**. Agora, o sistema é capaz de isolar rigorosamente os dados (através da injeção de `user_id` em todas as models de negócio e de Policies restritas), permitindo que múltiplas pessoas usem o James no mesmo servidor, mas cada uma vivendo em seu próprio "universo particular".

<div class="hx:mt-12 hx:mb-6">
  <h2>A Filosofia "Omakase": Feito para Mim</h2>
</div>

Uma premissa fundamental do James é que ele é um software construído **por mim, para mim**. Isso significa que as decisões de design, as escolhas de funcionalidades e a priorização do que entra ou não no sistema seguem a filosofia *Omakase* (termo que, no software, descreve um menu fechado e altamente opinativo feito pelo "chef"). O James reflete estritamente as minhas próprias necessidades e o meu fluxo de trabalho. Se algo faz sentido e facilita a minha rotina, entra no projeto; caso contrário, fica de fora.

<div class="hx:mt-12 hx:mb-6">
  <h2>Sobre esta Documentação</h2>
</div>

O objetivo desta documentação é contar um pouco da história do projeto, compartilhando as motivações e as decisões arquiteturais que moldaram o James. É um diário de bordo do desenvolvimento.

Você vai encontrar explicações sobre as funcionalidades, o roadmap de módulos e um passo a passo para a instalação, mas sem necessariamente detalhar cada linha de código ou como as coisas funcionam "por debaixo dos panos". A ideia é compartilhar a jornada de construir uma ferramenta unificada para a vida real.