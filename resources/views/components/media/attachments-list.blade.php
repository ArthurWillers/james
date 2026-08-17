@props([
    'attachments',
    'deletable' => false,
    'deleteInputName' => 'delete_attachments[]',
])

@if($attachments && $attachments->isNotEmpty())
    <x-ui.lightbox class="mt-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($attachments as $media)
                @php
                    $isImage = in_array($media->mime_type, ['image/jpeg', 'image/png', 'image/jpg', 'image/webp']);
                    $fileUrl = Illuminate\Support\Facades\URL::signedRoute(
                        'attachments.download',
                        [$media->id, $media->file_name]
                    );
                @endphp
                <div class="flex items-center justify-between p-3 border border-neutral-200 rounded-lg bg-neutral-50 group transition-colors">
                    <div class="flex items-center gap-3 overflow-hidden flex-1 min-w-0">
                        @if($isImage)
                            <button type="button" class="flex items-center text-left gap-3 w-full cursor-pointer" @click="openLightbox({{ Js::from($fileUrl) }}, {{ Js::from($media->file_name) }})">
                                <x-avatar :image="$fileUrl" class="w-10! h-10! shrink-0" radius="md" />
                                <div class="truncate text-sm text-neutral-700">
                                    <div class="truncate font-medium hover:text-accent transition-colors" title="{{ $media->file_name }}">{{ $media->file_name }}</div>
                                    <div class="text-xs text-neutral-500">{{ $media->human_readable_size }}</div>
                                </div>
                            </button>
                        @else
                            <a href="{{ $fileUrl }}" target="_blank" class="flex items-center gap-3 w-full">
                                <x-avatar icon="heroicon-o-document" class="w-10! h-10! shrink-0 group-hover:text-neutral-700 transition-colors" radius="md" variant="white" />
                                <div class="truncate text-sm text-neutral-700">
                                    <div class="truncate font-medium hover:text-accent transition-colors" title="{{ $media->file_name }}">{{ $media->file_name }}</div>
                                    <div class="text-xs text-neutral-500">{{ $media->human_readable_size }}</div>
                                </div>
                            </a>
                        @endif
                    </div>
                    @if($deletable)
                        <div class="ml-3 shrink-0">
                            <x-form.form-checkbox 
                                name="{{ $deleteInputName }}" 
                                value="{{ $media->id }}" 
                                label="Excluir" 
                                class="text-red-600! hover:text-red-700!"
                            />
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </x-ui.lightbox>
@endif
