<?php

namespace App\Models;

use App\Enums\FinancialAccountType;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'type',
    'pix_keys',
])]
class FinancialAccount extends Model
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
            'type' => FinancialAccountType::class,
            'pix_keys' => 'array',
        ];
    }

    /**
     * Get the transactions associated with the account.
     *
     * @return HasMany<FinancialTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    /**
     * Get the credit cards associated with the account.
     *
     * @return HasMany<FinancialCreditCard, $this>
     */
    public function creditCards(): HasMany
    {
        return $this->hasMany(FinancialCreditCard::class);
    }

    /**
     * Get the recurrences associated with the account.
     *
     * @return HasMany<FinancialRecurrence, $this>
     */
    public function recurrences(): HasMany
    {
        return $this->hasMany(FinancialRecurrence::class);
    }

    /**
     * Scope a query to include the account balance.
     */
    public function scopeWithBalance(Builder $query): void
    {
        $query->addSelect([
            'balance' => FinancialTransaction::selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount WHEN type = 'expense' THEN -amount ELSE 0 END), 0)")
                ->whereColumn('financial_account_id', 'financial_accounts.id')
                ->where('is_posted', true),
        ])->withCasts(['balance' => 'float']);
    }
}
