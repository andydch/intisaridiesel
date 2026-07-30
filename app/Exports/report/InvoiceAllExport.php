<?php

namespace App\Exports\report;

use App\Exports\report\InvoiceExport;
use App\Exports\report\InvoiceSummaryExport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InvoiceAllExport implements WithMultipleSheets
{
    protected $branch_id;
    protected $start_date;
    protected $end_date;

    public function __construct($branch_id, $start_date, $end_date)
    {
        // ini_set('memory_limit', '64M');
        // ini_set('max_execution_time', 1800);

        $this->branch_id = $branch_id;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            new InvoiceExport($this->branch_id, $this->start_date, $this->end_date),
            new InvoiceSummaryExport($this->branch_id, $this->start_date, $this->end_date),
        ];
    }
}
