---
title: "Guia de Deploy (Produção)"
date: 2026-07-14T10:00:00-03:00
weight: 20
---

Este documento descreve os passos e requisitos essenciais para colocar o projeto James em produção em um servidor VPS (Linux/Ubuntu).

## 1. Requisitos do Servidor

O James foi construído utilizando tecnologias modernas. O servidor de produção **deve** ter:

- **PHP 8.5+** (extensões recomendadas: `pdo_pgsql`, `pgsql`, `intl`, `mbstring`, `xml`, `bcmath`, `curl`, `zip`, `gd`/`imagick`)
- **Node.js 20+** e **NPM** (Para compilar os assets do Vite)
- **PostgreSQL 15+ (Obrigatório)**: O sistema exige PostgreSQL devido ao uso intensivo de busca textual agnóstica a acentos (através das extensões `unaccent` e `pg_trgm`). Não utilize MySQL ou SQLite em produção.
- **Nginx**
- **Composer**
- **Supervisor** (Para manter o Scheduler em background)

## 2. Passo a Passo Inicial

1. Clone o repositório no servidor (geralmente em `/var/www/james`):
   ```bash
   git clone https://github.com/ArthurWillers/james.git /var/www/james
   cd /var/www/james
   ```
2. Instale as dependências do PHP sem pacotes de desenvolvimento:
   ```bash
   composer install --optimize-autoloader --no-dev
   ```
3. Configure as permissões dos diretórios:
   ```bash
   chown -R www-data:www-data /var/www/james
   chmod -R 775 /var/www/james/storage /var/www/james/bootstrap/cache
   ```

## 3. Configuração de Ambiente (.env)

Copie o `.env.example` para `.env` e ajuste as seguintes chaves de forma estrita para produção:

```ini
APP_NAME=James
APP_ENV=production
APP_DEBUG=false
APP_URL=https://james.seu-dominio.com
APP_TIMEZONE=America/Sao_Paulo
APP_LOCALE=pt_BR
APP_CURRENCY=BRL

# Banco de Dados (PostgreSQL Obrigatório)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=james
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha_segura

# Spatie Media Library (Privacidade Absoluta)
MEDIA_DISK=private
FORCE_MEDIA_LIBRARY_LAZY_LOADING=false

# Sistema de Notificações - Telegram Bot
TELEGRAM_BOT_TOKEN="123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ"
TELEGRAM_CHAT_ID="123456789"

# Sistema de Notificações - E-mail
NOTIFICATIONS_MAIL_ENABLED=false
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=seu_usuario
MAIL_PASSWORD=sua_senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="notificacoes@seu-dominio.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Gere a chave da aplicação:
```bash
php artisan key:generate
```

---

## 4. Configuração do Bot do Telegram

O James conta com um canal de entrega de notificações espelhado diretamente no **Telegram**. Para receber alertas no seu celular (ex: fechamento de faturas, avisos de acertos, rotinas financeiras):

1. Abra o Telegram e procure pelo bot oficial [@BotFather](https://t.me/BotFather).
2. Envie o comando `/newbot` e siga as instruções para definir o nome e o username do seu bot (ex: `MeuJamesBot`).
3. Ao finalizar, o BotFather fornecerá um **Token de Acesso HTTP** (ex: `7123456789:AAF...`). Preencha este valor na variável `TELEGRAM_BOT_TOKEN`.
4. Obtenha seu **Chat ID** pessoal:
   - Inicie uma conversa com seu novo bot enviando `/start`.
   - Em seguida, envie uma mensagem para o bot [@userinfobot](https://t.me/userinfobot) ou acesse no navegador a URL: `https://api.telegram.org/bot<SEU_TOKEN>/getUpdates` para descobrir o seu `id` numérico.
   - Preencha este número na variável `TELEGRAM_CHAT_ID`.
5. Se ambas as variáveis estiverem preenchidas no `.env`, o canal do Telegram é ativado automaticamente pelo sistema de notificações.

---

## 5. Otimizações de Produção e Deploy Automatizado

Para facilitar o processo de deploy contínuo, o James possui um comando Artisan inteligente que orquestra todo o fluxo de atualização:

```bash
php artisan app:update
```

O comando executa atomicamente os seguintes passos:
1. Ativa o modo de manutenção (`down`).
2. Executa `git pull` para buscar as atualizações remotas.
3. Executa `composer install --optimize-autoloader --no-dev`.
4. Instala dependências do front-end com `npm ci`.
5. Compila os assets otimizados de produção via `npm run build` (TailwindCSS v4 / Vite).
6. Roda migrações pendentes do banco de dados com `migrate --force`.
7. Garante o link simbólico do storage (`storage:link`).
8. Pergunta se deseja executar os Seeders iniciais (Admin e Tags padrão, recomendado no 1º deploy).
9. Limpa e recria os caches de configuração, rotas e views (`optimize`).
10. Reinicia os processos gerenciados pelo **Supervisor** (`sudo supervisorctl restart all`).
11. Desativa o modo de manutenção (`up`).

Sempre que atualizar o projeto no futuro, basta acessar a pasta e rodar `php artisan app:update`.

---

## 6. Configuração do Nginx

Aponte o Document Root do Nginx para a pasta `/var/www/james/public`. Exemplo de bloco seguro para o Nginx:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name james.seu-dominio.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name james.seu-dominio.com;
    root /var/www/james/public;

    # Certificados SSL (ex: Certbot / Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/james.seu-dominio.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/james.seu-dominio.com/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.5-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 7. Schedulers e Processos em Background (Supervisor)

O James depende de tarefas executadas periodicamente para manter a integridade temporal do módulo financeiro:
- `finance:rollover-invoices`: Abre e rola faturas de cartão de crédito.
- `finance:rollover-transactions`: Rola despesas pendentes do passado para a data presente.
- `finance:process-recurrences`: Materializa recorrências financeiras em transações reais.

Para manter o *Task Scheduler* rodando continuamente, utilize o **Supervisor**. Crie o arquivo `/etc/supervisor/conf.d/james-scheduler.conf`:

```ini
[program:james-scheduler]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/james/artisan schedule:work
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/james/storage/logs/scheduler.log
stopwaitsecs=3600
```

Carregue e inicie o serviço:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start james-scheduler:*
```

### Configuração do Sudoers (Restart Automático)

Para permitir que o comando `php artisan app:update` reinicie o Supervisor automaticamente sem pedir senha durante os deploys, adicione a seguinte regra ao `sudoers`:

1. Execute `sudo visudo`.
2. Adicione ao final do arquivo (substituindo `seu_usuario` pelo usuário SSH do servidor, ex: `avw`):
   ```text
   seu_usuario ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl restart all
   ```
3. Salve o arquivo. Agora as atualizações serão 100% automatizadas e sem atrito!
