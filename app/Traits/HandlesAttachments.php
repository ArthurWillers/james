<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait HandlesAttachments
{
    /**
     * Sincroniza anexos (adicionando novos e deletando os marcados) para um modelo.
     */
    public function syncAttachments(Model $model, array $data, string $collection = 'attachments'): void
    {
        if (! empty($data['delete_attachments'])) {
            $model->media()->whereIn('id', $data['delete_attachments'])->delete();
        }

        if (! empty($data['attachments'])) {
            foreach ($data['attachments'] as $file) {
                $model->addMedia($file)->toMediaCollection($collection);
            }
        }
    }
}
