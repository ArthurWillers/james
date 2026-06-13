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
- Sem pacote de tradução inicialmente
- Criação do Helper `DateHelper` para formatação de datas
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
