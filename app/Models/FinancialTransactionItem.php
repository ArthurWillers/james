<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FinancialTransactionItem extends Model
{
    use LogsActivity;

    protected $fillable = [
        'financial_transaction_id',
        'description',
        'quantity',
        'unit_price',
        'total',
    ];

    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /**
     * Get the financial transaction associated with the item.
     *
     * @return BelongsTo<FinancialTransaction, $this>
     */
    public function financialTransaction(): BelongsTo
    {
        return $this->belongsTo(FinancialTransaction::class);
    }

    /**
     * Get the tags associated with the transaction item.
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
        )->withPivot('is_primary');
    }

    protected static array $recordEvents = ['created', 'updated', 'deleted'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('financial_transaction_item');
    }
}
