<?php

namespace App\Models;

use App\Traits\HasInitials;
use App\Traits\Searchable;
use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable([
    'name',
    'relationship_category',
    'birthdate',
    'phones',
    'pix_keys',
    'addresses',
    'notes',
])]
class Contact extends Model implements HasMedia
{
    /** @use HasFactory<ContactFactory> */
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
            'pix_keys' => 'array',
            'addresses' => 'array',
        ];
    }

    /**
     * Get the contact's avatar URL.
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            return $this->getFirstMediaUrl('avatar', 'avatar') ?: null;
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

    #[Override]
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('avatar')
            ->format('webp')
            ->fit(Fit::Crop, 200, 200)
            ->nonQueued();
    }
}
