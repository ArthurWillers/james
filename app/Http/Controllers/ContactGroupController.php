<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $groups = ContactGroup::withCount('contacts')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'ilike', '%'.$search.'%');
            })
            ->orderBy('name')
            ->get();

        return view('contacts.groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $allContacts = Contact::with('media')->orderBy('name')->get(['id', 'name'])->append('avatar');

        return view('contacts.groups.create', compact('allContacts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:contact_groups,name'],
            'contact_ids' => ['nullable', 'array'],
            'contact_ids.*' => ['exists:contacts,id'],
        ]);

        $group = ContactGroup::create([
            'name' => $validated['name'],
        ]);

        if (isset($validated['contact_ids'])) {
            $group->contacts()->sync($validated['contact_ids']);
        }

        return redirect()->route('contacts.groups.index')->with('success', 'Grupo criado com sucesso.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContactGroup $group): View
    {
        $group->load('contacts');
        $allContacts = Contact::with('media')->orderBy('name')->get(['id', 'name'])->append('avatar');

        return view('contacts.groups.edit', compact('group', 'allContacts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContactGroup $group): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:contact_groups,name,'.$group->id],
            'contact_ids' => ['nullable', 'array'],
            'contact_ids.*' => ['exists:contacts,id'],
        ]);

        $group->update([
            'name' => $validated['name'],
        ]);

        $group->contacts()->sync($validated['contact_ids'] ?? []);

        return redirect()->route('contacts.groups.index')->with('success', 'Grupo atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContactGroup $group): RedirectResponse
    {
        $group->delete();

        return back()->with('success', 'Grupo excluído com sucesso.');
    }
}
