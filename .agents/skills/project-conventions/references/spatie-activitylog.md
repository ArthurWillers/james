---
name: spatie-activitylog
description: Skill para implementação do Spatie Activitylog v5.0+ no projeto. Ative sempre que solicitar auditoria, logs de Models ou histórico.
---

# Spatie Activitylog Conventions (v5.0+)

Utilizamos o pacote `spatie/laravel-activitylog` para **Auditoria de Negócio** e rastreabilidade completa e vitalícia de mutações no banco de dados.

## 1. O que Logar (Rastreabilidade Ampla)
- **Logue Todas as Models de Negócio:** O pacote deve ser utilizado em praticamente todas as Models do domínio (ex: Transações, Faturas, Acertos, Contatos, Contas Bancárias, Tags, Categorias, etc.) para rastrear mutações (`created`, `updated`, `deleted`).
- **NÃO logue tabelas e execuções técnicas:** Nunca crie logs para registrar que um Job, Queue ou Scheduler rodou. Modelos de infraestrutura (como `sessions`, `jobs`, `personal_access_tokens`) estão estritamente fora do escopo de auditoria.

## 2. Configuração nas Models (Regras Inegociáveis)
Sempre que adicionar logs a uma Model, você DEVE seguir as restrições abaixo obrigatoriamente para evitar inchaço do banco de dados com logs vazios e prevenir loops infinitos:

1. **Mass-assignment EXCLUSIVO:** A proteção de mass-assignment deve ser feita EXCLUSIVAMENTE com a propriedade clássica `protected $fillable = [...]` nas classes de Model, e NÃO com o atributo PHP 8 `#[Fillable]`.
2. **LogsActivity e LogOptions:** Utilize a trait `LogsActivity` e o método `getActivitylogOptions()`. Dentro deste método, a configuração DEVE usar `->logFillable()` para registrar apenas os campos permitidos, `->logOnlyDirty()` e `->dontSubmitEmptyLogs()` (também conhecido como `dontLogEmptyChanges()`). NUNCA use `logAll()`.
3. **Eventos Explícitos:** Os models devem especificar explicitamente os eventos a serem registrados através da propriedade `protected static array $recordEvents`.
4. **REGRA CRÍTICA DE SOFTDELETES:** A propriedade `$recordEvents` só pode conter `'restored'` e `'forceDeleted'` se o model utilizar explicitamente a trait `Illuminate\Database\Eloquent\SoftDeletes`. Models sem SoftDeletes devem usar apenas `['created', 'updated', 'deleted']`. (O desrespeito a isso causa um loop infinito no boot do Laravel).

```php
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExemploModel extends Model
{
    use LogsActivity, SoftDeletes; // Exemplo COM SoftDeletes

    // REGRA 1: array clássico
    protected $fillable = ['nome', 'valor']; 
    
    // REGRA 3 e 4: especificar os eventos. Se não tivesse SoftDeletes, remova 'restored' e 'forceDeleted'.
    protected static array $recordEvents = ['created', 'updated', 'deleted', 'restored', 'forceDeleted']; 

    // REGRA 2: LogOptions
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
```

## 3. Frontend e Exibição do Histórico

* **Ações de Sistema e Causer_id:** Ações na interface web registrarão automaticamente o `Auth::user()` como autor (`causer_id`). Mutações geradas por processos em background (Filas, Schedulers) criarão o log com `causer_id` nulo. Ao exibir o histórico, trate os logs sem autor. Se `causer_id` for null, exiba uma label como "Sistema" ou "Rotina Automática".
* **Atributos Antigos e Novos (v4+):** Lembre-se que a partir do Spatie Activitylog v4, as diferenças de atributos automáticos (`old` e `attributes`) ficam armazenadas na propriedade `$activity->attribute_changes`, e NÃO na propriedade `$activity->properties`. Considere isso ao exibir o detalhe do log no front-end.

## 4. Retenção Vitalícia (Full Audit Trail)

Os registros de auditoria são **permanentes**. NÃO agende o comando `activitylog:clean` e NÃO delete registros da tabela `activity_log`. O histórico completo deve ser mantido indefinidamente para fins de rastreabilidade.
