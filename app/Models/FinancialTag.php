<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class FinancialTag extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'icon',
        'color_hex',
    ];

    use HasFactory, Searchable;

    public const REEMBOLSO_ID = 1;

    public const JUROS_ID = 2;

    public const SALDO_INICIAL_ID = 3;

    public const TRANSFERENCIA_ID = 4;

    public const AJUSTE_SALDO_ID = 5;

    public const PAGAMENTO_PARCIAL_ID = 6;

    /**
     * Get the transactions associated with the tag.
     *
     * @return MorphToMany<FinancialTransaction, $this>
     */
    public function transactions(): MorphToMany
    {
        return $this->morphedByMany(
            FinancialTransaction::class,
            'financial_taggable',
            'financial_taggables',
            'financial_tag_id',
            'financial_taggable_id'
        );
    }

    /**
     * Get the transaction items associated with the tag.
     *
     * @return MorphToMany<FinancialTransactionItem, $this>
     */
    public function transactionItems(): MorphToMany
    {
        return $this->morphedByMany(
            FinancialTransactionItem::class,
            'financial_taggable',
            'financial_taggables',
            'financial_tag_id',
            'financial_taggable_id'
        );
    }

    protected static array $recordEvents = ['created', 'updated', 'deleted'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('financial_tag');
    }
}
