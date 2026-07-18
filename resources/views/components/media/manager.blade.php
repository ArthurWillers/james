@props([
    'model',
    'editable' => true,
    'deleteInputName' => 'delete_attachments[]',
    'uploadInputName' => 'attachments[]',
    'uploadLabel' => 'Adicionar Anexos',
    'uploadSublabel' => 'Arraste arquivos JPG, PNG ou PDF (Max 10MB)',
    'uploadAccept' => '.jpeg,.jpg,.png,.pdf',
    'title' => 'Anexos'
])

@php
    $attachments = $model ? $model->getMedia('attachments') : collect();
    $hasAttachments = $attachments->isNotEmpty();
@endphp

<x-card {{ $attributes }}>
    @if($title)
        <h3 class="text-sm font-bold text-neutral-800 mb-4">{{ $title }}</h3>
    @endif

    @if($editable)
        <div class="{{ $hasAttachments ? 'mb-4 pb-4 border-b border-neutral-100' : '' }}">
            <x-dropzone 
                :name="$uploadInputName" 
                :multiple="true" 
                :label="$uploadLabel" 
                :sublabel="$uploadSublabel" 
                :accept="$uploadAccept" 
            />
            @error('attachments')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @error('attachments.*')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <x-media.attachments-list 
        :attachments="$attachments" 
        :deletable="$editable"
        :deleteInputName="$deleteInputName"
    />
</x-card>
