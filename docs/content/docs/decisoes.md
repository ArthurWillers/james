---
title: "Decisões"
date: 2026-06-09T12:41:14-03:00
draft: false
---

Registro de decisões do projeto.

## 001 — Laravel Puro sem Livewire

**Data:** 09 de Junho de 2026

**Contexto:**
Necessidade de manter o código organizado e previsível em um projeto de longo prazo com muitos módulos. Livewire adiciona uma camada de abstração que dificulta o controle fino do comportamento dos componentes.

**Decisão:**
Usar Laravel puro com Blade e Alpine.js para interações leves no frontend.

**Observações:**
Decisão revisável no futuro caso surja necessidade de reatividade mais complexa. Alpine.js cobre a maioria dos casos de UI sem overhead.

## 002 — PostgreSQL em Dev e Prod

**Data:** 09 de Junho de 2026

**Contexto:**
Necessidade de usar extensões específicas do PostgreSQL (unaccent, pg_trgm) para busca textual sem acentuação em módulos com grande volume de texto (contatos, notas, documentos, receitas).

**Decisão:**
Usar PostgreSQL tanto em desenvolvimento quanto em produção via Laravel Sail, eliminando divergências entre ambientes.

**Observações:**
SQLite foi descartado por não suportar as extensões necessárias. As extensões unaccent e pg_trgm são habilitadas via migration inicial.

## 003 — Identidade Visual

**Data:** 09 de Junho de 2026

**Contexto:**
Necessidade de definir uma identidade visual consistente antes de iniciar o desenvolvimento das views, aproveitando a identidade já validada no projeto Aurum.

**Decisão:**
Cor de acento <span style="display:inline-block; width:14px; height:14px; background-color:#DAA520; border-radius:20%; margin-bottom:-1px;"></span> **#DAA520** (dourado), fundo branco, tipografia limpa, sidebar fixa em tela grande e colapsável em tela pequena. Design minimalista sem elementos decorativos desnecessários.

**Observações:**
A mesma cor de acento é usada no Aurum, mantendo consistência visual entre os projetos pessoais. O dourado é usado pontualmente para não sobrecarregar a interface.

## 004 — Pipeline Automatizado de Ícones (Heroicons)

**Data:** 11 de Junho de 2026

**Contexto:**
O componente monolítico `icon.blade.php` com SVGs hardcoded era difícil de manter e limitava a quantidade de ícones disponíveis. Era necessário um pipeline automatizado para disponibilizar todos os 300+ ícones e mantê-los atualizáveis facilmente.

**Decisão:**
Foi decidido adotar a geração automática de ícones SVG a partir do repositório oficial [tailwindlabs/heroicons](https://github.com/tailwindlabs/heroicons) via script Python (`scripts/sync-heroicons.py`). Com isso, todos os ícones ficam disponíveis e atualizáveis com um único comando.

**Uso:**
```blade
{{-- Outline (24px, stroke) --}}
<x-icons.outline.home class="size-6" />

{{-- Solid (24px, fill) --}}
<x-icons.solid.check class="size-6" />

{{-- Mini (20px, fill) --}}
<x-icons.mini.chevron-down class="size-5" />

{{-- Micro (16px, fill) --}}
<x-icons.micro.star class="size-4" />

{{-- Dinâmico (via variável) --}}
<x-dynamic-component :component="'icons.outline.' . $icon" class="size-6" />
```

**Atualização:**
Para atualizar os ícones para a versão mais recente do Heroicons:
```bash
python3 scripts/sync-heroicons.py
```

**Observações:**
- Os ícones gerados ficam em `resources/views/components/icons/` e são versionados no Git.
- Ao atualizar os ícones, basta rodar o script e commitar o resultado.
- O script suporta `--dry-run` e `--verbose` para depuração.

## 005 — Ambiente de Desenvolvimento com Laravel Sail e Docker

**Data:** 11 de Junho de 2026

**Contexto:**
Havia a necessidade de manter o ambiente padronizado, simplificando a inicialização de serviços pesados localmente, sem a necessidade de instalar instâncias separadas (como banco de dados ou servidor de e-mails) na máquina hospedeira.

**Decisão:**
Foi decidido utilizar o [Laravel Sail](https://laravel.com/docs/sail) como ambiente de desenvolvimento Docker principal contendo os serviços essenciais para o projeto, especificamente: PostgreSQL e Mailpit.

**Uso:**
Para subir os containers:
```bash
./vendor/bin/sail up
```
Para acessar os recursos expostos:
- **Aplicação web:** `http://localhost`
- **Mailpit Dashboard:** `http://localhost:8025`

Para executar comandos Artisan, use o prefixo `sail`:
```bash
./vendor/bin/sail artisan migrate
```

## 006 — Adoção da Spatie Media Library com Armazenamento Privado

**Data:** 12 de Junho de 2026

**Contexto:**
Com o desenvolvimento do módulo CRM e planejamento para os módulos de Finanças, surgiu a necessidade de gerenciar arquivos anexos, como fotos de perfil (avatares) de contatos, recibos e documentos financeiros. Historicamente, isso envolveria a criação de colunas específicas de caminhos (paths) em múltiplas tabelas, levando à duplicação de lógica de *upload* e inconsistência na gestão de arquivos do sistema. Era imperativo encontrar uma solução unificada e robusta para anexar arquivos a qualquer modelo do Eloquent.

**Decisão:**
Foi decidido adotar o pacote `spatie/laravel-medialibrary`. Esta biblioteca permite associar arquivos a modelos do Eloquent de forma elegante através de relacionamentos polimórficos, implementando a interface `HasMedia` e o *trait* `InteractsWithMedia`. Isso centraliza a gestão de *media* em uma única estrutura de banco de dados polimórfica, facilitando a expansão futura.

**Segurança e Privacidade (Crucial):**
Sendo o James um "Life OS" focado na privacidade rigorosa e em um ambiente "Single User", **é estritamente proibido salvar dados pessoais ou anexos de contatos no disco público (`public`)**. O vazamento de dados do círculo social do usuário ou de documentos financeiros é inaceitável.

Para garantir 100% de privacidade:
- O pacote foi configurado globalmente através da variável de ambiente no arquivo `.env` (`MEDIA_DISK=private`), forçando a utilização do disco privado do servidor.
- Nenhum arquivo será acessível diretamente através de uma URL estática da web.
- As imagens, avatares e documentos serão servidos de forma dinâmica e exclusivamente através de rotas dedicadas no Laravel, as quais estão estritamente protegidas pelo *middleware* de autenticação (`auth`). Apenas o próprio usuário logado no sistema poderá visualizar estes recursos.

## 007 — Padronização de Data, Moeda e Locale

**Data:** 13 de Junho de 2026

**Decisão:**
- Locale e moeda configuráveis via `.env` (`APP_LOCALE` e `APP_CURRENCY`)
- Adição dos arquivos nativos de tradução do Laravel para Inglês (`en`) e Português do Brasil (`pt_BR`) para mensagens do sistema e validação
- Criação do Helper `DateHelper` e macros do Carbon (ex: `$date->formatDate()`) para formatação global padronizada de datas
- Uso nativo da classe `Number::currency()` para valores monetários

## 008 — Nomenclatura Contatos vs CRM

**Data:** 13 de Junho de 2026

**Contexto:**
O módulo originalmente chamado "CRM" passava uma ideia muito corporativa e complexa, que não condiz com a proposta de um ERP pessoal simplificado (Life OS). Além disso, contatos do dia a dia não se enquadram em um funil de clientes.

**Decisão:**
O módulo foi renomeado de "CRM" para "Contatos". A essência e arquitetura do módulo permanecem as mesmas: servir como a "Single Source of Truth" (SSOT) para pessoas e relacionamentos, usando entidades puramente passivas e sem acesso ao sistema.

## 009 — Editor Visual Markdown

**Data:** 13 de Junho de 2026

**Contexto:**
A necessidade de registrar anotações ricas para os contatos (e futuramente em outros módulos) demandava um editor de texto, porém armazenar HTML diretamente no banco de dados traz riscos de segurança (XSS) e dificulta a portabilidade dos dados entre diferentes plataformas ou exportações.

**Decisão:**
Adoção de Markdown puro no banco de dados para os campos de anotações (ex: campo `notes` em `contacts`). Na interface de usuário, será utilizado um editor visual que exporta em Markdown (como EasyMDE). O Markdown será renderizado em HTML de forma segura apenas no momento da exibição.

## 010 — Busca Textual com PostgreSQL (unaccent + pg_trgm)

**Data:** 13 de Junho de 2026

**Contexto:**
O James possui múltiplos módulos com campos de texto livre (nome, notas, descrições, etc.) que precisam de busca eficiente e sem acentuação.

**Decisão:**
- Extensões unaccent e pg_trgm habilitadas via migration inicial, garantindo que rodem antes de qualquer outra migration
- Trait Searchable criado em app/Traits/Searchable.php com escopo search() que aceita um termo e uma ou mais colunas
- Busca via unaccent() + ILIKE para matching sem acento
- Ordenação por relevância via similarity() do pg_trgm
- Para múltiplas colunas, usa GREATEST() para rankear pelo melhor match
- Índice GIN criado por model nas colunas que serão buscadas

**Observações:**
- O trait é aplicado individualmente por model, apenas onde há busca textual
- Colunas numéricas e de relacionamento não usam o trait
- A busca global do Dashboard agregará resultados de múltiplos models usando o mesmo escopo

## 011 — Prevenção Estrita de Lazy Loading (Spatie Media Library)

**Data:** 20 de Junho de 2026

**Contexto:**
O Laravel possui a proteção `Model::preventLazyLoading(! app()->isProduction())` ativada por padrão no ambiente local para evitar problemas de N+1 queries. No entanto, a biblioteca `spatie/laravel-medialibrary` possui um comportamento padrão que burla essa verificação utilizando carregamento explícito (via `loadMissing`) caso o relacionamento não esteja carregado. Isso esconde potenciais problemas de performance (N+1) durante o desenvolvimento.

**Decisão:**
A configuração `FORCE_MEDIA_LIBRARY_LAZY_LOADING` foi definida explicitamente como `false` nos arquivos de ambiente (`.env` e `.env.example`).
Isso força a biblioteca a usar lazy loading implícito natural, permitindo que o Laravel intercepte e lance a exceção `LazyLoadingViolationException` caso o relacionamento `media` não seja carregado previamente via Eager Loading.

**Observações:**
- Esta configuração garante que qualquer lista ou loop envolvendo avatares/documentos alerte o desenvolvedor sobre o N+1.
- Deve-se sempre usar `->with('media')` nas queries do Eloquent quando iterar sobre múltiplos models com arquivos vinculados.

## 012 — Reorganização e Namespaces de Componentes Blade

**Data:** 20 de Junho de 2026

**Contexto:**
Com o crescimento da aplicação, o diretório `resources/views/components/` começou a ficar poluído. Componentes estruturais (layouts), elementos genéricos de interface (UI), inputs (form) e itens de navegação (nav) estavam todos misturados na raiz, dificultando a manutenção.

**Decisão:**
Os componentes foram fisicamente separados em subdiretórios lógicos (`ui/`, `form/`, `layout/`, `nav/`). 
Para evitar verbosidade no uso (ex: ter que digitar `<x-ui.button>`) e para manter retrocompatibilidade com views já escritas, esses diretórios foram registrados diretamente no `boot()` do `AppServiceProvider` usando o `Blade::anonymousComponentPath()`. Assim, continua sendo possível chamar `<x-button>` de forma limpa, mas o projeto fica organizado nos bastidores.

## 013 — Abstração de Componentes Complexos de Formulário

**Data:** 20 de Junho de 2026

**Contexto:**
As views de criação e edição (ex: de Contatos) estavam se tornando excessivamente grandes e complexas, misturando HTML estrutural com lógicas extensas em Alpine.js para funcionalidades avançadas como crop de avatar, campos dinâmicos e editor Markdown.

**Decisão:**
Foi decidido extrair essas lógicas para componentes Blade altamente reutilizáveis e isolados:
- `form-image-cropper`: Encapsula toda a interface e scripts do Alpine para seleção, preview e recorte (crop) de imagens em formulários.
- `form-markdown-editor`: Isola a integração e inicialização do editor visual (EasyMDE) para campos de texto rico.
- `form-key-value-repeater`: Componente para gerenciamento dinâmico (adicionar/remover/editar) de pares de chave e valor (ex: múltiplos e-mails e telefones salvos em JSON).

Essa abstração limpou massivamente as views (reduzindo centenas de linhas na criação de contatos), melhorou a legibilidade e pavimentou a reutilização imediata para os futuros módulos do sistema.

## 014 — Biblioteca Gráfica (Apache ECharts)

**Data:** 22 de Junho de 2026

**Contexto:**
Para a visualização de dados financeiros (Evolução Patrimonial, Fluxo de Caixa, Despesas por Categoria), precisávamos de uma biblioteca capaz de renderizar gráficos complexos, como o Diagrama de Sankey (cashflow) com alta qualidade visual, mantendo a performance com milhares de dados e sem depender de frameworks JavaScript pesados (como React).

**Decisão:**
Adoção do **Apache ECharts** como biblioteca padrão para visualização de dados no James.

**Observações:**
O ECharts suporta nativamente o Diagrama de Sankey (crucial para o fluxo de caixa), possui renderização ultrarrápida via Canvas e SVG, e sua integração é feita via JavaScript Vanilla (o que se alinha perfeitamente com a stack do projeto em Laravel + Alpine.js). Ele será utilizado para todos os gráficos analíticos complexos do painel de finanças.
