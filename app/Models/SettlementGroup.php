<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SettlementGroup extends Model implements HasMedia
{
    use LogsActivity;

    protected $fillable = [
        'description',
        'total_amount',
        'date',
        'mode',
        'financial_transaction_id',
    ];

    use HasFactory, InteractsWithMedia, SoftDeletes;

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

    /**
     * Soft-delete every settlement belonging to this group through the models.
     */
    public function deleteSettlements(): void
    {
        $this->settlements()->eachById(function (Settlement $settlement): void {
            $settlement->delete();
        });
    }

    /**
     * Permanently delete every settlement belonging to this group through the models.
     */
    public function forceDeleteSettlements(): void
    {
        $this->settlements()->withTrashed()->eachById(function (Settlement $settlement): void {
            $settlement->forceDelete();
        });
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

    protected static array $recordEvents = ['created', 'updated', 'deleted', 'restored', 'forceDeleted'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('settlement_group');
    }
}
