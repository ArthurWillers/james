---
title: "Decisões Arquiteturais"
date: 2026-06-09T12:41:14-03:00
draft: false
---

Registro de decisões arquiteturais do projeto.

## 001 — Laravel Puro sem Livewire

Decisão de usar Laravel puro com Blade, sem Livewire.

## 002 — PostgreSQL em Dev e Prod

Decisão de usar PostgreSQL tanto no ambiente de desenvolvimento quanto em produção.

## 003 — Identidade Visual

Cor principal #DAA520, design minimalista e sidebar fixa.

## 004 — Pipeline Automatizado de Ícones (Heroicons)

**Data:** 2026-06-11

Ícones SVG gerados automaticamente a partir do repositório oficial [tailwindlabs/heroicons](https://github.com/tailwindlabs/heroicons) via script Python (`scripts/sync-heroicons.py`).

**Motivação:** O componente monolítico `icon.blade.php` com SVGs hardcoded era difícil de manter e limitava a quantidade de ícones disponíveis. Com o pipeline automatizado, todos os 300+ ícones do Heroicons ficam disponíveis e atualizáveis com um único comando.

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

**Atualização:** Para atualizar os ícones para a versão mais recente do Heroicons:
```bash
python3 scripts/sync-heroicons.py
```

**Observações:**
- Os ícones gerados ficam em `resources/views/components/icons/` e são versionados no Git
- Ao atualizar os ícones, basta rodar o script e commitar o resultado
- O script suporta `--dry-run` e `--verbose` para depuração

## 005 — Ambiente de Desenvolvimento com Laravel Sail e Docker

**Data:** 2026-06-11

**Decisão:** Utilizar o [Laravel Sail](https://laravel.com/docs/sail) como ambiente de desenvolvimento Docker principal contendo os serviços: PostgreSQL e Mailpit.

**Motivação:** Manter o ambiente padronizado, simplificando a inicialização de serviços pesados localmente, sem a necessidade de instalar instâncias separadas na máquina hospedeira.

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

### Contexto
Com o desenvolvimento do módulo CRM e planeamento para os módulos de Finanças, surgiu a necessidade de gerir ficheiros anexos, como fotos de perfil (avatares) de contactos, recibos e documentos financeiros. Historicamente, isto envolveria a criação de colunas específicas de caminhos (paths) em múltiplas tabelas, levando a duplicação de lógica de *upload* e inconsistência na gestão de ficheiros do sistema. Era imperativo encontrar uma solução unificada e robusta para anexar ficheiros a qualquer modelo do Eloquent.

### Decisão
Foi decidido adotar o pacote `spatie/laravel-medialibrary`. Esta biblioteca permite associar ficheiros a modelos do Eloquent de forma elegante através de relacionamentos polimórficos, implementando a interface `HasMedia` e o *trait* `InteractsWithMedia`. Isto centraliza a gestão de *media* numa única estrutura de base de dados polimórfica, facilitando a expansão futura.

### Segurança e Privacidade (Crucial)
Sendo o James um "Life OS" focado na privacidade rigorosa e num ambiente "Single User", **é estritamente proibido guardar dados pessoais ou anexos de contactos no disco público (`public`)**. O vazamento de dados do círculo social do utilizador ou de documentos financeiros é inaceitável.

Para garantir 100% de privacidade:
- O pacote foi configurado globalmente através da variável de ambiente no ficheiro `.env` (`MEDIA_DISK=private`), forçando a utilização do disco privado do servidor.
- Nenhum ficheiro será acessível diretamente através de um URL estático da web.
- As imagens, avatares e documentos serão servidos de forma dinâmica e exclusiva através de rotas dedicadas no Laravel, as quais estão estritamente protegidas pelo *middleware* de autenticação (`auth`). Apenas o próprio utilizador logado no sistema poderá visualizar estes recursos.
