<?php

namespace App\Models;

use App\Traits\HasInitials;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Override;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable([
    'name',
    'relationship_category',
    'birthdate',
    'phones',
    'emails',
    'notes',
])]
class Contact extends Model implements HasMedia
{
    use HasFactory, HasInitials, InteractsWithMedia, Searchable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'phones' => 'array',
            'emails' => 'array',
        ];
    }

    /**
     * Get the contact's avatar URL.
     */
    protected function avatar(): Attribute
    {
        return Attribute::get(function (): ?string {
            $media = $this->getFirstMedia('avatar');

            return $media ? route('contacts.avatar', $this).'?v='.$media->updated_at->timestamp : null;
        });
    }

    /**
     * Register the media collections for the model.
     */
    #[Override]
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif']);
    }

    /**
     * Convert and save the avatar.
     */
    public function saveAvatar(UploadedFile $file): void
    {
        Image::load($file->getPathname())
            ->format('webp')
            ->fit(Fit::Crop, 200, 200)
            ->save();

        $this->addMedia($file)
            ->usingFileName(Str::random(40).'.webp')
            ->toMediaCollection('avatar');
    }

    /**
     * Get the groups the contact belongs to.
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(ContactGroup::class);
    }

    /**
     * Get the settlements for the contact.
     */
    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class);
    }

    /**
     * Get the distinct relationship categories in use.
     *
     * @param  Builder  $query
     * @return Collection<int, string>
     */
    public function scopeRelationshipCategories($query): Collection
    {
        return $query->whereNotNull('relationship_category')
            ->distinct()
            ->orderBy('relationship_category', 'asc')
            ->pluck('relationship_category');
    }
}
