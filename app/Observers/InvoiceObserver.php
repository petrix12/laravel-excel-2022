<?php

namespace App\Observers;

use App\Models\Invoice;

class InvoiceObserver
{
    public function creating(Invoice $invoice){
        $invoice->correlative = Invoice::where('serie', $invoice->serie)->count() + 1;
    }
}
