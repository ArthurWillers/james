<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

#[Fillable([
    'name',
    'icon',
    'color_hex',
])]
class FinancialTag extends Model
{
    use HasFactory, Searchable;

    public const REEMBOLSO_ID = 1;

    public const JUROS_ID = 2;

    public const SALDO_INICIAL_ID = 3;

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
}
