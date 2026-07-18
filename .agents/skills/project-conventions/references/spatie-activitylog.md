---
name: spatie-activitylog
description: Skill para implementação do Spatie Activitylog v5.0+ no projeto. Ative sempre que solicitar auditoria, logs de Models ou histórico.
---

# Spatie Activitylog Conventions (v5.0+)

Utilizamos o pacote `spatie/laravel-activitylog` para **Auditoria de Negócio** e rastreabilidade completa e vitalícia de mutações no banco de dados.

## 1. O que Logar (Rastreabilidade Ampla)
- **Logue Todas as Models de Negócio:** O pacote deve ser utilizado em praticamente todas as Models do domínio (ex: Transações, Faturas, Acertos, Contatos, Contas Bancárias, Tags, Categorias, etc.) para rastrear mutações (`created`, `updated`, `deleted`).
- **NÃO logue tabelas e execuções técnicas:** Nunca crie logs para registrar que um Job, Queue ou Scheduler rodou. Modelos de infraestrutura (como `sessions`, `jobs`, `personal_access_tokens`) estão estritamente fora do escopo de auditoria.

## 2. Configuração nas Models (Sintaxe v5+)
Sempre que adicionar logs a uma Model, utilize a trait `LogsActivity` e a classe `LogOptions`. Aplique as restrições abaixo obrigatoriamente para evitar inchaço do banco de dados com logs vazios:

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ExemploModel extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable() // Loga apenas os campos mutáveis
            ->logOnlyDirty() // Evita logar se o model foi salvo sem alterações reais
            ->dontSubmitEmptyLogs(); // Não cria registro se nenhum dado mudou
    }
}
```

## 3. Ações de Sistema e Causer_id

* Ações na interface web registrarão automaticamente o `Auth::user()` como autor (`causer_id`).
* Mutações geradas por processos em background (Filas, Schedulers) criarão o log com `causer_id` nulo.
* **Regra de Frontend:** Ao exibir o histórico, trate os logs sem autor. Se `causer_id` for null, exiba uma label como "Sistema" ou "Rotina Automática".

## 4. Retenção Vitalícia (Full Audit Trail)

Os registros de auditoria são **permanentes**. NÃO agende o comando `activitylog:clean` e NÃO delete registros da tabela `activity_log`. O histórico completo deve ser mantido indefinidamente para fins de rastreabilidade.
