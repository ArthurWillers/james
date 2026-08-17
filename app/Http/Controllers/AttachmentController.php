<?php

namespace App\Http\Controllers;

use App\Models\FinancialTransaction;
use App\Models\Settlement;
use App\Models\SettlementGroup;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * Retorna o arquivo anexado para download ou visualização in-line.
     */
    public function download(Request $request, Media $media, ?string $filename = null): StreamedResponse
    {
        $allowedModelTypes = [
            FinancialTransaction::class,
            Settlement::class,
            SettlementGroup::class,
        ];

        abort_unless(
            $media->collection_name === 'attachments'
            && in_array($media->model_type, $allowedModelTypes, true),
            404
        );

        return $media->toInlineResponse($request);
    }
}
