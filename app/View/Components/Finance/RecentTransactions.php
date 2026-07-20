<?php

namespace App\View\Components\Finance;

use App\Models\FinancialAccount;
use Illuminate\View\Component;
use Illuminate\View\View;

class RecentTransactions extends Component
{
    public $account;

    public $recentTransactions;

    /**
     * Create a new component instance.
     */
    public function __construct(FinancialAccount $account, int $limit = 10)
    {
        $this->account = $account;

        $this->recentTransactions = $account->transactions()
            ->with(['invoice.creditCard', 'account', 'tags'])
            ->latest('date')
            ->latest('updated_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.finance.recent-transactions');
    }
}
