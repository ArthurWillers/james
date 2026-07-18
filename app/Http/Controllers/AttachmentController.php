<?php

namespace App\Http\Controllers;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AttachmentController extends Controller
{
    /**
     * Retorna o arquivo anexado para download ou visualização in-line.
     */
    public function download(Media $media, $filename = null)
    {
        // Garante que é da coleção de anexos, apenas por segurança.
        abort_if($media->collection_name !== 'attachments', 404);

        return response()->file($media->getPath(), [
            'Content-Disposition' => 'inline; filename="'.$media->file_name.'"',
        ]);
    }
}
