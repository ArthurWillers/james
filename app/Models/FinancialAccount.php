<?php

namespace App\Models;

use App\Enums\FinancialAccountType;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class FinancialAccount extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'type',
        'pix_keys',
    ];

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
     * Get the payments associated with the account.
     *
     * @return HasMany<FinancialTransactionPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(FinancialTransactionPayment::class);
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
            'balance' => function ($subQuery) {
                $subQuery->selectRaw("COALESCE(SUM(CASE WHEN financial_transactions.type = 'income' THEN financial_transaction_payments.amount WHEN financial_transactions.type = 'expense' THEN -financial_transaction_payments.amount ELSE 0 END), 0)")
                    ->from('financial_transaction_payments')
                    ->join('financial_transactions', 'financial_transactions.id', '=', 'financial_transaction_payments.financial_transaction_id')
                    ->whereColumn('financial_transaction_payments.financial_account_id', 'financial_accounts.id')
                    ->where('financial_transaction_payments.is_posted', true);
            }
        ])->withCasts(['balance' => 'float']);
    }

    /**
     * Scope a query to exclude investment accounts.
     */
    public function scopeWithoutInvestments(Builder $query): void
    {
        $query->where('type', '!=', FinancialAccountType::Investment);
    }

    protected static array $recordEvents = ['created', 'updated', 'deleted', 'restored', 'forceDeleted'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('financial_account');
    }
}
