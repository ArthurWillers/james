<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'financial_account_id',
    'financial_credit_card_invoice_id',
    'type',
    'amount',
    'description',
    'date',
    'is_posted',
    'transfer_pair_id',
    'installment_current',
    'installment_total',
    'financial_recurrence_id',
])]
class FinancialTransaction extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
            'is_posted' => 'boolean',
        ];
    }

    /**
     * Get the account associated with the transaction.
     *
     * @return BelongsTo<FinancialAccount, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class, 'financial_account_id');
    }

    /**
     * Get the invoice associated with the transaction.
     *
     * @return BelongsTo<FinancialCreditCardInvoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FinancialCreditCardInvoice::class, 'financial_credit_card_invoice_id');
    }

    /**
     * Get the items associated with the transaction.
     *
     * @return HasMany<FinancialTransactionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(FinancialTransactionItem::class);
    }

    /**
     * Get the recurrence associated with the transaction.
     *
     * @return BelongsTo<FinancialRecurrence, $this>
     */
    public function recurrence(): BelongsTo
    {
        return $this->belongsTo(FinancialRecurrence::class, 'financial_recurrence_id');
    }

    /**
     * Get the other half of the transfer transaction.
     *
     * @return BelongsTo<FinancialTransaction, $this>
     */
    public function transferPair(): BelongsTo
    {
        return $this->belongsTo(FinancialTransaction::class, 'transfer_pair_id');
    }

    /**
     * Get the tags associated with the transaction.
     *
     * @return MorphToMany<FinancialTag, $this>
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(
            FinancialTag::class,
            'financial_taggable',
            'financial_taggables',
            'financial_taggable_id',
            'financial_tag_id'
        );
    }

    /**
     * Scope a query to only include posted transactions.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePosted(Builder $query): Builder
    {
        return $query->where('is_posted', true);
    }

    /**
     * Scope a query to only include pending transactions.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('is_posted', false);
    }
}
