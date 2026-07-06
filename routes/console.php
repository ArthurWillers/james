<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('finance:rollover-invoices')->daily();
Schedule::command('finance:rollover-transactions')->daily();
Schedule::command('finance:process-recurrences')->daily();
