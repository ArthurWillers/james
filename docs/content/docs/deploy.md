---
title: "Guia de Deploy (Produção)"
date: 2026-07-14T10:00:00-03:00
weight: 20
---

Este documento descreve os passos e requisitos essenciais para colocar o projeto James em produção em um servidor VPS (Linux/Ubuntu).

## 1. Requisitos do Servidor

O James foi construído utilizando tecnologias modernas. O servidor de produção **deve** ter:

- **PHP 8.3+**
- **Node.js 20+** (Para compilar os assets do Vite)
- **PostgreSQL 15+ (Obrigatório)**: O sistema exige PostgreSQL devido ao uso intensivo de busca textual agnóstica a acentos (através das extensões `unaccent` e `pg_trgm`). Não tente utilizar MySQL ou SQLite em produção.
- **Nginx**
- **Composer**

## 2. Passo a Passo Inicial

1. Clone o repositório no servidor (geralmente em `/var/www/james`).
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
APP_ENV=production
APP_DEBUG=false
APP_URL=https://james.seu-dominio.com

# Banco de dados
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=james
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha_segura

# Spatie Media Library
MEDIA_DISK=private
```
*Gere a chave com `php artisan key:generate`.*

## 4. Otimizações de Produção e Deploy do Laravel

Para compilar o front-end, rodar migrações, gerar os caches e popular o banco de dados inicial, criamos um comando interativo que orquestra tudo. Basta rodar:

```bash
php artisan app:update
```

Este comando fará o build do Vite, executará as migrações, perguntará se você quer rodar as seeds padrão (usuário Admin e tags, ideal no 1º deploy) e reiniciará o Supervisor. Sempre que você quiser atualizar a aplicação no futuro, basta entrar na pasta e rodar este mesmo comando!

## 5. Configuração do Nginx

A configuração do servidor web deve apontar para a pasta `public/` do projeto. Você pode encontrar o exemplo oficial e seguro de configuração do Nginx na documentação do Laravel:
👉 [https://laravel.com/docs/13.x/deployment#nginx](https://laravel.com/docs/13.x/deployment#nginx)

## 6. Schedulers (Tarefas Agendadas)

O James depende de tarefas executadas em background (como a verificação de recorrências financeiras).

Em vez de depender do Cron tradicional do Linux, podemos usar o próprio **Supervisor** para manter o processo do *Task Scheduler* rodando continuamente em background.

Crie um arquivo de configuração no Supervisor para o scheduler (ex: `/etc/supervisor/conf.d/james-scheduler.conf`):

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
Isso fará com que o processo fique ativo na memória, verificando a cada minuto se há alguma tarefa agendada a ser executada.

Em seguida, aplique as configurações no supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start james-scheduler:*
```

### Dica: Reiniciando o Supervisor automaticamente sem pedir senha

Durante o processo de deploy (`php artisan app:update`), o script tenta reiniciar o Supervisor em background rodando `sudo supervisorctl restart all`. Para que isso não falhe por causa da senha do Linux, você pode liberar esse comando específico no `sudoers`.

No seu servidor, rode:
```bash
sudo visudo
```
E adicione a seguinte linha no final do arquivo (troque `seu_usuario` pelo usuário do seu servidor, ex: `avw`):
```text
seu_usuario ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl restart all
```
Salve e saia. O `app:update` agora conseguirá reiniciar tudo 100% sozinho!