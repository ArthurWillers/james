---
title: "Notificações"
weight: 5
date: 2026-08-16T12:00:00-03:00
draft: false
---

### Visão Geral

O módulo de **Notificações** do James fornece uma infraestrutura unificada e multi-canal para alertar o usuário sobre eventos críticos, lembretes de rotinas financeiras, vencimentos de faturas, atualizações de acertos e relatórios do sistema.

Todas as notificações são **sempre registradas internamente no banco de dados** (alimentando a central `/notifications` e o badge numérico na sidebar) e podem ser espelhadas de forma transparente para canais externos como **Telegram** e **E-mail**.

```mermaid
flowchart TD
    A[Evento no Sistema / Scheduler / Job] -->|Dispara Notificação| B(GeneralNotification)
    B --> C{Pipeline de Canais}
    C -->|Obrigatório / Sempre Ativo| D[(Database)]
    C -->|Se TELEGRAM configurado| E[Bot do Telegram]
    C -->|Se NOTIFICATIONS_MAIL_ENABLED| F[E-mail Transacional]
    
    D --> G[Sidebar Badge & Painel /notifications]
    E --> H[Notificação Push no Celular]
    F --> I[Caixa de Entrada]
```

---

## 1. Níveis de Notificação (`NotificationLevel`)

Namespace: `App\Enums\NotificationLevel`

Cada notificação possui uma severidade semântica que controla as cores de destaque e os ícones exibidos na interface e nas mensagens:

| Nível | Case | Cor | Ícone Heroicon | Finalidade |
| --- | --- | --- | --- | --- |
| **Informativo** | `NotificationLevel::Info` | Azul | `heroicon-o-information-circle` | Conclusão de rotinas, backups, relatórios gerados. |
| **Sucesso** | `NotificationLevel::Success` | Verde | `heroicon-o-check-circle` | Acerto liquidado, fatura quitada, sincronização com êxito. |
| **Alerta** | `NotificationLevel::Warning` | Amarelo | `heroicon-o-exclamation-triangle` | Fatura fechada aguardando pagamento, dívida próxima do vencimento. |
| **Atenção / Erro** | `NotificationLevel::Danger` | Vermelho | `heroicon-o-exclamation-circle` | Falha em conciliação, erro em automação, saldo em risco. |

---

## 2. A Classe `GeneralNotification`

Namespace: `App\Notifications\GeneralNotification`

A classe `GeneralNotification` centraliza todo o despacho de notificações da aplicação através de um construtor flexível e fortemente tipado:

```php
public function __construct(
    public readonly string $title,
    public readonly string $message,
    public readonly ?string $actionUrl = null,
    NotificationLevel|string $level = NotificationLevel::Info,
    public readonly array $details = [],
    public readonly array $channels = ['database', 'telegram', 'mail'],
)
```

### Exemplos Práticos de Uso

#### Notificação Simples (Apenas no Banco de Dados)
```php
use App\Enums\NotificationLevel;
use App\Notifications\GeneralNotification;

$user->notify(new GeneralNotification(
    title: 'Backup Realizado',
    message: 'A rotina periódica de backup foi finalizada com êxito.',
    level: NotificationLevel::Info,
    channels: ['database']
));
```

#### Alerta Acionável com Metadados Estruturados (Multi-canal)
```php
use App\Enums\NotificationLevel;
use App\Notifications\GeneralNotification;

$user->notify(new GeneralNotification(
    title: 'Fatura de Cartão Fechada',
    message: 'A fatura do seu cartão fechou e já está disponível para conferência.',
    actionUrl: route('financial.cards.index'),
    level: NotificationLevel::Warning,
    details: [
        'Cartão' => 'Nubank Ultravioleta',
        'Valor Total' => 'R$ 4.850,20',
        'Vencimento' => '25/08/2026',
        'Status' => 'Aguardando Pagamento',
    ],
    channels: ['database', 'telegram', 'mail']
));
```

#### Notificação Crítica / Erro
```php
use App\Enums\NotificationLevel;
use App\Notifications\GeneralNotification;

$user->notify(new GeneralNotification(
    title: 'Falha na Rotina Automática',
    message: 'Não foi possível processar a recorrência mensal de aluguel.',
    actionUrl: route('financial.recurrences.index'),
    level: NotificationLevel::Danger,
    details: [
        'Recorrência' => 'Aluguel do Apartamento',
        'Motivo' => 'Conta de origem inexistente ou inativa',
    ]
));
```

---

## 3. Entrega por Canal e Regras de Segurança (*Fail-Gracefully*)

1. **Database (`database`)**:
   - Sempre ativo quando incluído em `$channels`.
   - Persiste os dados na tabela nativa `notifications` do Laravel em formato JSON.
   - Alimenta o contador de não lidas no menu lateral e o painel `/notifications`.

2. **Telegram (`telegram`)**:
   - Ativo apenas se as variáveis `TELEGRAM_BOT_TOKEN` e `TELEGRAM_CHAT_ID` estiverem preenchidas no `.env`.
   - As mensagens enviadas ao Bot contêm cabeçalho com o nível da notificação em caixa alta, título em negrito, corpo da mensagem, lista formatada de metadados (`details`) e botão de ação interativo.
   - **Proteção Localhost**: Caso o `actionUrl` aponte para um domínio local (`http://localhost` ou `127.0.0.1`), a API do Telegram recusaria o botão inline com erro HTTP 400. O James intercepta isso automaticamente e inclui a URL em formato de texto seguro dentro do corpo da mensagem.

3. **E-mail (`mail`)**:
   - Ativo apenas se `NOTIFICATIONS_MAIL_ENABLED=true` e o usuário destinatário possuir endereço de e-mail válido.
   - Renderiza um e-mail transacional limpo e responsivo baseado no `MailMessage` do Laravel com suporte a botão de ação direta.

---

## 4. Painel Web de Notificações (`/notifications`)

A interface do James oferece uma central completa para gerenciamento de alertas:
- **Badge Numérico em Tempo Real:** Mostra a quantidade de notificações não lidas diretamente no link "Notificações" da sidebar (com limitação visual `99+` caso acumule muitas).
- **Lista Cronológica Paginada:** Exibe o histórico de notificações com badges coloridos por nível (`Info`, `Success`, `Warning`, `Danger`).
- **Leitura Rápida:** Clicar em uma notificação não lida exibe os detalhes completos (incluindo metadados estruturados) e a marca automaticamente como lida.
- **Ações em Massa:** Botão "Marcar todas como lidas" para zerar o contador com um único clique.
- **Exclusão:** Possibilidade de excluir notificações do histórico individualmente.

---

## 5. Testes Automatizados com Pest

Ao escrever testes para qualquer funcionalidade que envie notificações, utilize a fachada `Notification::fake()`:

```php
use App\Enums\NotificationLevel;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Notification;

test('notifica o usuario quando um acerto e finalizado', function () {
    Notification::fake();

    // Executa a ação do teste...

    Notification::assertSentTo($user, GeneralNotification::class, function ($notification) {
        return $notification->title === 'Acerto Finalizado'
            && $notification->level === NotificationLevel::Success;
    });
});
```
