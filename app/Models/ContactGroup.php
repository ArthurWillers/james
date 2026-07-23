<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class ContactGroup extends Model
{
    use LogsActivity;

    protected $fillable = ['user_id', 'name', 'notes'];

    use HasFactory;

    /**
     * Get the contacts for the group.
     */
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class);
    }

    /**
     * Get the user that owns this record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static array $recordEvents = ['created', 'updated', 'deleted'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('contact_group');
    }
}
