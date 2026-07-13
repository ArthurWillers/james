<?php

namespace App\Http\Controllers;

use App\Models\ContactSettlementArchive;
use Illuminate\Http\Request;

class SettlementArchiveController extends Controller
{
    /**
     * Archive the specified contacts.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'contact_ids' => ['required', 'array'],
            'contact_ids.*' => ['exists:contacts,id'],
        ]);

        foreach ($validated['contact_ids'] as $contactId) {
            ContactSettlementArchive::firstOrCreate(['contact_id' => $contactId]);
        }

        return redirect()->back()->with('success', 'Contatos arquivados com sucesso.');
    }

    /**
     * Unarchive the specified contacts.
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'contact_ids' => ['required', 'array'],
            'contact_ids.*' => ['exists:contacts,id'],
        ]);

        ContactSettlementArchive::whereIn('contact_id', $validated['contact_ids'])->delete();

        return redirect()->back()->with('success', 'Contatos desarquivados com sucesso.');
    }
}
