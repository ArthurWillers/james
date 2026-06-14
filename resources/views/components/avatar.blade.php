@props(['model'])

@if($model->avatar_url ?? false)
    <img src="{{ $model->avatar_url }}" alt="{{ $model->name ?? 'Avatar' }}" {{ $attributes->merge(['class' => 'shrink-0 border rounded-md object-cover bg-neutral-200 border-neutral-300 w-8 h-8']) }}>
@else
    <div {{ $attributes->merge(['class' => 'shrink-0 flex items-center justify-center border rounded-md font-medium bg-neutral-200 border-neutral-300 w-8 h-8 text-sm']) }}>
        {{ method_exists($model, 'initials') ? $model->initials() : '?' }}
    </div>
@endif
