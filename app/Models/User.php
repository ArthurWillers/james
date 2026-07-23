<?php

namespace App\Models;

use App\Traits\HasInitials;
use Illuminate\Database\Eloquent\Attributes\Hidden;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use LogsActivity;

    protected $fillable = ['name', 'email', 'password'];

    use HasFactory, HasInitials, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function contactGroups(): HasMany
    {
        return $this->hasMany(ContactGroup::class);
    }

    public function financialAccounts(): HasMany
    {
        return $this->hasMany(FinancialAccount::class);
    }

    public function financialCreditCards(): HasMany
    {
        return $this->hasMany(FinancialCreditCard::class);
    }

    public function financialCreditCardInvoices(): HasMany
    {
        return $this->hasMany(FinancialCreditCardInvoice::class);
    }

    public function financialRecurrences(): HasMany
    {
        return $this->hasMany(FinancialRecurrence::class);
    }

    public function financialTags(): HasMany
    {
        return $this->hasMany(FinancialTag::class);
    }

    public function financialTransactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    public function financialTransactionItems(): HasMany
    {
        return $this->hasMany(FinancialTransactionItem::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class);
    }

    public function settlementGroups(): HasMany
    {
        return $this->hasMany(SettlementGroup::class);
    }

    protected static array $recordEvents = ['created', 'updated', 'deleted'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('user');
    }
}
