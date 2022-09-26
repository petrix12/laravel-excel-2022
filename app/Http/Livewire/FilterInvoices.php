<?php

namespace App\Http\Livewire;

use App\Exports\InvoiceExport;
use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class FilterInvoices extends Component
{
    use WithPagination;

    public $filters = [
        'serie' => '',
        'fromNumber' => '',
        'toNumber' => '',
        'fromDate' => '',
        'toDate' => ''
    ];

    public function generateReport(){
        return new InvoiceExport($this->filters);
    }

    public function render()
    {
        $invoices = Invoice::filter($this->filters)->paginate(10);
        return view('livewire.filter-invoices', compact('invoices'));
    }
}
