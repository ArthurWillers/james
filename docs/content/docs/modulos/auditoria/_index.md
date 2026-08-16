---
title: "Auditoria e Logs"
weight: 6
date: 2026-08-16T12:00:00-03:00
draft: false
---

### Visão Geral

O módulo de **Auditoria e Logs do Sistema** (`/audit`) oferece rastreabilidade completa e vitalícia de todas as mutações que ocorrem nas entidades de negócio do James. Construído sobre o pacote `spatie/laravel-activitylog` (v5+), o sistema registra automaticamente quem alterou, quando alterou e exatamente quais atributos foram modificados (antes vs. depois).

```mermaid
flowchart LR
    A[Usuário / Ação no Sistema] -->|Mutação Eloquent| B(Model de Negócio)
    B -->|LogsActivity Trait| C[(Tabela activity_log)]
    C --> D[Painel /audit]
    D --> E[Visualizador de Diff /audit/{id}]
```

---

## 1. Eventos Auditados

Os modelos monitorados registram os seguintes ciclos de vida:

| Evento | Descrição | Estilização no Painel |
| --- | --- | --- |
| `created` | Registro recém-criado no banco de dados. | Badge Verde (`Criado`) |
| `updated` | Atualização em um ou mais campos. | Badge Azul (`Atualizado`) |
| `deleted` | Exclusão lógica do registro (Soft Delete). | Badge Amarelo / Laranja (`Enviado para a lixeira`) |
| `restored` | Restauração de um item que estava na lixeira. | Badge Roxo (`Restaurado`) |
| `forceDeleted` | Exclusão física permanente e definitiva do banco. | Badge Vermelho (`Excluído permanentemente`) |

---

## 2. Rastreamento de Autores (*Causers*)

O sistema diferencia automaticamente se a alteração partiu do usuário autenticado ou de uma rotina automática em background:

- **Usuário Logado:** Exibe o nome e o avatar do usuário responsável pela ação.
- **Sistema / Rotina Automática:** Quando o `causer_id` é nulo (como na execução dos comandos do Scheduler, `ProcessFinancialRecurrences` ou `RolloverCreditCardInvoices`), a interface exibe o autor como **"Sistema / Rotina Automática"** com ícone representativo de engrenagem.

---

## 3. Visualização de Diferenças (Diff de Alterações)

Ao acessar a tela de detalhes de um registro de auditoria (`/audit/{activity}`), o controller (`AuditController`) descompacta e formata as propriedades alteradas:

- **Valores Anteriores (`old`):** Exibidos com destaque avermelhado e tachado quando modificados.
- **Novos Valores (`attributes`):** Exibidos com destaque verde.
- **Formatação Inteligente de Valores:**
  - Valores monetários são automaticamente formatados via `CurrencyHelper`.
  - Datas e timestamps são exibidos no timezone da aplicação via `DateHelper`.
  - Booleans e Enums são traduzidos para rótulos legíveis em português (ex: `true` $\rightarrow$ `Sim`, `pending` $\rightarrow$ `Pendente`).

---

## 4. Convenções e Implementação nos Models

Para que uma nova Model de negócio participe do sistema de auditoria, siga o padrão estabelecido no projeto:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class ExemploModel extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'amount',
        'status',
    ];

    // Inclua restored e forceDeleted apenas se o model usar SoftDeletes
    protected static array $recordEvents = ['created', 'updated', 'deleted', 'restored', 'forceDeleted'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('exemplo_model');
    }
}
```

### Regras Obrigatórias:
1. **Apenas `$fillable`:** Utilize `logFillable()` para auditar estritamente os campos seguros, evitando logar tokens ou campos internos efêmeros.
2. **`logOnlyDirty()` e `dontLogEmptyChanges()`:** Evita poluir o banco de logs quando um `save()` é chamado sem alterações reais nos dados.
3. **Escopo de Negócio:** Auditoria destina-se a mutações de dados de negócio. Não utilize activity log para monitorar execuções técnicas puras de jobs ou requisições HTTP sem mutação.
