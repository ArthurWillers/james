<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $contacts = Contact::query()
            ->search(request('search'), 'name')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('contacts.index', compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('contacts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContactRequest $request): RedirectResponse
    {
        $contact = Contact::create($request->validated());

        if ($request->hasFile('avatar')) {
            $contact->addMediaFromRequest('avatar')->toMediaCollection('avatar');
        }

        return redirect()
            ->route('contacts.show', $contact)
            ->with('success', 'Contato criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact): View
    {
        return view('contacts.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact): View
    {
        return view('contacts.edit', compact('contact'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContactRequest $request, Contact $contact): RedirectResponse
    {
        $contact->update($request->validated());

        if ($request->hasFile('avatar')) {
            $contact->addMediaFromRequest('avatar')->toMediaCollection('avatar');
        }

        return redirect()
            ->route('contacts.show', $contact)
            ->with('success', 'Contato atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()
            ->route('contacts.index')
            ->with('success', 'Contato movido para a lixeira.');
    }

    /**
     * Display a listing of trashed resources.
     */
    public function trashed(): View
    {
        $contacts = Contact::onlyTrashed()
            ->latest('deleted_at')
            ->paginate(15)
            ->withQueryString();

        return view('contacts.trashed', compact('contacts'));
    }

    /**
     * Restore a trashed resource.
     */
    public function restore(Contact $contact): RedirectResponse
    {
        $contact->restore();

        return redirect()
            ->route('contacts.show', $contact)
            ->with('success', 'Contato restaurado com sucesso.');
    }

    /**
     * Permanently delete a trashed resource.
     */
    public function forceDestroy(Contact $contact): RedirectResponse
    {
        $contact->clearMediaCollection('avatar');
        $contact->forceDelete();

        return redirect()
            ->route('contacts.trashed')
            ->with('success', 'Contato excluído permanentemente.');
    }

    /**
     * Remove the avatar from a contact.
     */
    public function destroyAvatar(Contact $contact): RedirectResponse
    {
        $contact->clearMediaCollection('avatar');

        return redirect()
            ->route('contacts.edit', $contact)
            ->with('success', 'Avatar removido com sucesso.');
    }
}
