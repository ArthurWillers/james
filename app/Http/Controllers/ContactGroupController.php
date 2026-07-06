<?php

namespace App\Http\Controllers;

use App\Models\ContactGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $groups = ContactGroup::withCount('contacts')->orderBy('name')->get();

        return view('contact-groups.index', compact('groups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:contact_groups,name'],
        ]);

        ContactGroup::create($validated);

        return back()->with('success', 'Grupo criado com sucesso.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContactGroup $contactGroup): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:contact_groups,name,'.$contactGroup->id],
        ]);

        $contactGroup->update($validated);

        return back()->with('success', 'Grupo atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContactGroup $contactGroup): RedirectResponse
    {
        $contactGroup->delete();

        return back()->with('success', 'Grupo excluído com sucesso.');
    }
}
