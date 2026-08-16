---
title: "Guia de Instalação (Desenvolvimento)"
date: 2026-06-11T12:00:00-03:00
weight: 10
---

Este guia descreve como configurar o ambiente de desenvolvimento local para o projeto James, utilizando o **Laravel Sail** (Docker).

## Pré-requisitos

Certifique-se de que sua máquina possui as seguintes ferramentas instaladas:
- [Docker](https://docs.docker.com/engine/install/) e Docker Compose
- [Composer](https://getcomposer.org/) (necessário apenas para a instalação inicial dos pacotes antes do Sail)
- [Node.js e NPM](https://nodejs.org/) (para o Vite)

---

## Passo a Passo de Instalação

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

Por padrão, o `.env.example` já está configurado para a rede do Docker:
- `DB_CONNECTION=pgsql`
- `DB_HOST=pgsql`
- `MAIL_MAILER=smtp`
- `MAIL_HOST=mailpit`
- `MAIL_PORT=1025`
- `MEDIA_DISK=private`
- `FORCE_MEDIA_LIBRARY_LAZY_LOADING=false` (força detecção de N+1)
- `TELEGRAM_BOT_TOKEN=` e `TELEGRAM_CHAT_ID=` (vazios por padrão em desenvolvimento)
- `NOTIFICATIONS_MAIL_ENABLED=false`

> **Nota:** Certifique-se de que a variável `APP_URL` esteja apontando para `http://localhost`.

### 4. Iniciar os Containers (Laravel Sail)

Inicie os containers do banco de dados (PostgreSQL), Mailpit e o próprio servidor PHP através do Laravel Sail:

```bash
./vendor/bin/sail up -d
```
*(A primeira execução pode demorar alguns minutos para o Docker baixar e compilar as imagens).*

### 5. Configuração do Laravel

Com os containers rodando, execute os seguintes comandos para preparar o framework:

**Gerar a chave da aplicação:**
```bash
./vendor/bin/sail artisan key:generate
```

**Criar o link simbólico do storage:**
```bash
./vendor/bin/sail artisan storage:link
```

**Rodar as migrations e popular o banco de dados (Seeds):**
```bash
./vendor/bin/sail artisan migrate --seed
```

### 6. Instalar dependências de Front-end (Vite)

Para processar o CSS (TailwindCSS v4) e JavaScript, execute o NPM:

```bash
# Instala os pacotes
./vendor/bin/sail npm install

# Inicia o servidor de desenvolvimento com Hot Module Replacement
./vendor/bin/sail npm run dev
```

---

## Testes Automatizados (Pest)

O James utiliza o framework **Pest** para testes de Feature e Unitários. Para executar a suíte de testes no ambiente local:

```bash
# Cria o banco de dados de testes dentro do container (apenas na primeira vez)
./vendor/bin/sail psql -c "CREATE DATABASE james_test;"

# Executa todos os testes
./vendor/bin/sail test

# Ou com saída compacta e filtros:
./vendor/bin/sail artisan test --compact
./vendor/bin/sail artisan test --compact --filter=Notification
```

> **Nota sobre Testes e Notificações:** O arquivo `phpunit.xml` desativa automaticamente o token do Telegram e os e-mails durante os testes, garantindo que nenhum teste tente disparar requisições para a API externa do Telegram.

---

## Executando Schedulers em Desenvolvimento

Para testar as rotinas automáticas de rollover de faturas, transações pendentes e materialização de recorrências localmente:

```bash
# Mantém o worker do scheduler rodando a cada minuto:
./vendor/bin/sail artisan schedule:work

# Ou execute os comandos manualmente:
./vendor/bin/sail artisan finance:process-recurrences
./vendor/bin/sail artisan finance:rollover-invoices
./vendor/bin/sail artisan finance:rollover-transactions
```

---

## Acessando os Serviços Locais

- **Aplicação Principal:** Acesse [http://localhost](http://localhost) no seu navegador.
- **Mailpit (Caixa de Entrada Local para E-mails):** Acesse [http://localhost:8025](http://localhost:8025).
- **Vite:** Porta `5173`.

---

## Comandos Úteis no Dia a Dia

Para facilitar o uso do Sail, você pode criar um "alias" na sua máquina (adicionando ao seu `~/.bashrc` ou `~/.zshrc`):
```bash
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
```

- **Parar os containers:** `sail down`
- **Acessar o terminal interativo do container:** `sail shell`
- **Formatar código PHP (Pint):** `sail bin pint` (ou `./vendor/bin/pint` nativo)
- **Visualizar logs em tempo real (Pail):** `sail artisan pail`

