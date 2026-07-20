<?php

namespace App\View\Components\Contacts;

use App\Enums\SettlementType;
use App\Models\Contact;
use App\Models\Settlement;
use Illuminate\View\Component;
use Illuminate\View\View;

class BalanceCard extends Component
{
    public $contact;

    public $netBalance;

    /**
     * Create a new component instance.
     */
    public function __construct(Contact $contact)
    {
        $this->contact = $contact;

        $debtTheyOweMe = Settlement::where('contact_id', $contact->id)->where('type', SettlementType::TheyOwe->value)->sum('amount');
        $paymentsTheyMade = Settlement::where('contact_id', $contact->id)->where('type', SettlementType::TheyPaid->value)->sum('amount');
        $toReceive = max(0, $debtTheyOweMe - $paymentsTheyMade);

        $debtIOweThem = Settlement::where('contact_id', $contact->id)->where('type', SettlementType::IOwe->value)->sum('amount');
        $paymentsIMade = Settlement::where('contact_id', $contact->id)->where('type', SettlementType::IPaid->value)->sum('amount');
        $toPay = max(0, $debtIOweThem - $paymentsIMade);

        $this->netBalance = $toReceive - $toPay;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.contacts.balance-card');
    }
}
