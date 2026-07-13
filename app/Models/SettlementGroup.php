<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable([
    'description',
    'total_amount',
    'date',
    'mode',
    'financial_transaction_id',
])]
class SettlementGroup extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_amount' => 'float',
        ];
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class);
    }

    public function financialTransaction(): BelongsTo
    {
        return $this->belongsTo(FinancialTransaction::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->useDisk('attachments')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'application/pdf']);
    }
}
