<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSettlementRequest;
use App\Http\Requests\UpdateSettlementRequest;
use App\Models\Settlement;
use App\Models\Contact;
use App\Models\ContactGroup;
use App\Enums\SettlementType;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $showArchived = $request->boolean('archived');

        $contacts = Contact::with(['groups', 'media'])
            ->when($showArchived, function ($query) {
                $query->whereHas('settlementArchive');
            }, function ($query) {
                $query->notSettlementArchived();
            })
            ->withSum(['settlements as to_receive' => function ($query) {
                $query->whereIn('type', [SettlementType::TheyOwe->value, SettlementType::IPaid->value]);
            }], 'amount')
            ->withSum(['settlements as to_pay' => function ($query) {
                $query->whereIn('type', [SettlementType::IOwe->value, SettlementType::TheyPaid->value]);
            }], 'amount')
            ->get()
            ->map(function ($contact) {
                $contact->to_receive = $contact->to_receive ?? 0;
                $contact->to_pay = $contact->to_pay ?? 0;
                $contact->net_balance = $contact->to_receive - $contact->to_pay;
                $contact->avatar_url = $contact->avatar;
                // Add group_ids for filtering in Alpine
                $contact->group_ids = $contact->groups->pluck('id')->toArray();
                return $contact;
            })
            ->values();

        // Calculate global totals for active contacts only
        $globalTotals = Contact::notSettlementArchived()
            ->withSum(['settlements as to_receive' => function ($query) {
                $query->whereIn('type', [SettlementType::TheyOwe->value, SettlementType::IPaid->value]);
            }], 'amount')
            ->withSum(['settlements as to_pay' => function ($query) {
                $query->whereIn('type', [SettlementType::IOwe->value, SettlementType::TheyPaid->value]);
            }], 'amount')
            ->get();

        $toReceive = $globalTotals->sum('to_receive') ?? 0;
        $toPay = $globalTotals->sum('to_pay') ?? 0;
        $netBalance = $toReceive - $toPay;

        $groups = ContactGroup::orderBy('name')->get();

        return view('settlements.index', compact('contacts', 'toReceive', 'toPay', 'netBalance', 'showArchived', 'groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSettlementRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Settlement $settlement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Settlement $settlement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSettlementRequest $request, Settlement $settlement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Settlement $settlement)
    {
        //
    }
}
