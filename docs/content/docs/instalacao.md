---
title: "Guia de Instalação (Desenvolvimento)"
date: 2026-06-11T12:00:00-03:00
weight: 10
---

Este guia descreve como configurar o ambiente de desenvolvimento local para o projeto James, utilizando o **Laravel Sail** (Docker).

## Pré-requisitos

Certifique-se de que sua máquina possui as seguintes ferramentas instaladas:
- [Docker](https://docs.docker.com/engine/install/)
- [Composer](https://getcomposer.org/) (necessário apenas para a instalação inicial dos pacotes)
- [Node.js e NPM](https://nodejs.org/) (para o Vite)

---

## Passo a passo de instalação

### 1. Clonar o repositório

Primeiro, clone o repositório para a sua máquina:

```bash
git clone https://github.com/ArthurWillers/james.git
cd james
```

### 2. Instalar dependências do PHP

Utilize o Composer para instalar as dependências do Laravel e do Sail:

```bash
composer install
```

### 3. Configurar variáveis de ambiente

Copie o arquivo de exemplo para criar o seu próprio `.env`:

```bash
cp .env.example .env
```

Por padrão, o `.env.example` já está configurado para utilizar a rede do Docker, incluindo:
- `DB_HOST=pgsql`
- `MAIL_HOST=mailpit`

> **Nota:** Certifique-se de que a variável `APP_URL` esteja apontando para `http://localhost`.

### 4. Iniciar os Containers (Laravel Sail)

Agora, inicie os containers do banco de dados, Mailpit e o próprio servidor PHP através do Laravel Sail:

```bash
./vendor/bin/sail up
```
*(A primeira vez pode demorar alguns minutos, pois o Docker fará o download e build das imagens).*

### 5. Configuração do Laravel

Com os containers rodando, execute os seguintes comandos para preparar o framework:

**Gerar a chave da aplicação:**
```bash
./vendor/bin/sail artisan key:generate
```

**Rodar as migrations (criar as tabelas do banco):**
```bash
./vendor/bin/sail artisan migrate
```

### 6. Instalar dependências de Front-end (Vite)

Para processar o CSS (Tailwind) e JavaScript, você precisa rodar o NPM:

```bash
# Instala os pacotes
npm install

# Inicia o servidor de desenvolvimento do Vite
npm run dev
```

> Mantenha a aba do `npm run dev` aberta no seu terminal enquanto estiver desenvolvendo.

---

## Acessando o projeto

Se tudo ocorreu bem, sua infraestrutura local estará distribuída da seguinte forma:

- **Aplicação Principal:** Acesse [http://localhost](http://localhost) no seu navegador.
- **Mailpit (Visualizador de E-mails locais):** Acesse [http://localhost:8025](http://localhost:8025).
- **Vite:** Rodando em background na porta `5173`.

---

## Comandos Úteis no Dia a Dia

Para facilitar o uso do Sail, você pode criar um "alias" na sua máquina (adicionando ao seu `~/.bashrc` ou `~/.zshrc`):
```bash
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
```
Isso permite que você digite apenas `sail up -d` ou `sail artisan migrate`.

- **Parar o ambiente:** `./vendor/bin/sail down`
- **Entrar no container PHP:** `./vendor/bin/sail shell`
- **Rodar Testes (Pest):** `./vendor/bin/sail test`
