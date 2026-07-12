<?php

namespace App\Models;

use App\Enums\SettlementType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'contact_id',
    'financial_transaction_id',
    'settlement_group_id',
    'type',
    'amount',
    'description',
    'date',
])]
class Settlement extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => SettlementType::class,
            'date' => 'date',
            'amount' => 'float',
        ];
    }

    public function financialTransaction(): BelongsTo
    {
        return $this->belongsTo(FinancialTransaction::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(SettlementGroup::class, 'settlement_group_id');
    }
}

