<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>IncomeStatement</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    switch (strtolower($lokal_input)) {
                        case 'p':
                            //empty
                            break;
                        case 'n':
                            //empty
                            break;
                        default:
                            $lokal_input = 'a';
                    }

                    $months = explode(',','JAN,FEB,MAR,APR,MAY,JUN,JUL,AUG,SEP,OCT,NOP,DEC');
                    $columns = 3;
                    if ($month_id==0){$columns = 14;}
                @endphp
                <thead>
                    <tr>
                        <th colspan="{{ $columns }}">{!! ($qCompany?$qCompany->name:'') !!}</th>
                    </tr>
                    <tr>
                        @php
                            $branchInitial = '';
                            $branches = \App\Models\Mst_branch::where([
                                'id' => $branch_id,
                            ])
                            ->first();
                            if ($branches){$branchInitial = $branches->initial;}
                        @endphp
                        <th colspan="{{ $columns }}">Branch: {!! ($branches?$branches->name:'All') !!}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $columns }}">INCOME STATEMENT REPORT</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $columns }}">PERIOD:&nbsp;{{ ($month_id==0?'':$months[$month_id-1]).' '.$year_id }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th style="text-align: left;">{{ strtoupper($lokal_input) }}</th>
                        <th colspan="{{ $columns-2 }}">&nbsp;</th>
                        <th style="text-align: right;">{{ date_format(now(),"d/m/Y") }}</th>
                    </tr>
                    <tr>
                        <th style="border: 1px solid black;background-color: #ff6600;">ACCOUNT NAME</th>
                        @if ($month_id==0)
                            @foreach ($months as $month)
                                <th style="border: 1px solid black;background-color: #ff6600;">{{ $month }}</th>
                            @endforeach
                        @else
                            <th style="border: 1px solid black;background-color: #ff6600;">{{ $months[$month_id-1] }}</th>
                        @endif
                        <th style="border: 1px solid black;background-color: #ff6600;">TOTAL</th>
                    </tr>
                </thead>
                @php
                    $SalesPerCols = [0,0,0,0,0,0,0,0,0,0,0,0];
                    $TotalSales = 0;
                    $SalesReturnPerCols = [0,0,0,0,0,0,0,0,0,0,0,0];
                    $TotalSalesReturn = 0;
                    $SalesDiscountPerCols = [0,0,0,0,0,0,0,0,0,0,0,0];
                    $TotalSalesDiscount = 0;

                    $NettoSalesPerCols = [0,0,0,0,0,0,0,0,0,0,0,0];
                    $TotalNettoSales = 0;
                    $CogsPerCols = [0,0,0,0,0,0,0,0,0,0,0,0];
                    $TotalCogs = 0;
                    $GrossProfitPerCols = [0,0,0,0,0,0,0,0,0,0,0,0];
                    $TotalGrossProfit = 0;
                    $OpeExpensePerCols = [0,0,0,0,0,0,0,0,0,0,0,0];
                    $TotalOpeExpense = 0;
                    $OpeOtherIncomePerCols = [0,0,0,0,0,0,0,0,0,0,0,0];
                    $TotalOpeOtherIncome = 0;
                    $OpeOtherExpensePerCols = [0,0,0,0,0,0,0,0,0,0,0,0];
                    $TotalOpeOtherExpense = 0;

                    $daysInMonth = [31,28,31,30,31,30,31,31,30,31,30,31];

                    $sales_coa_code_prefiks = '51';
                    $sales_return_coa_code_prefiks = '52';
                    $sales_discount_coa_code_prefiks = '53';
                    $expense_coa_code_prefiks = '6';
                    $cogs_coa_code_prefiks = '7';
                    $other_income_coa_code_prefiks = '8';
                    $other_expense_coa_code_prefiks = '9';
                @endphp
                <tbody>
                    {{-- sales --}}
                    <tr>
                        <td>SALES</td>
                        @if ($month_id==0)
                            @for ($m=0;$m<count($months);$m++)
                                @php
                                    $sumSalesGJ = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($m, $year_id){
                                        $q->select('id')
                                        ->from('tx_general_journals')
                                        ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($m+1)==1?'0'.($m+1):($m+1)).'\'')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->whereIn('coa_id', function($q) use($sales_coa_code_prefiks, $branch_id){
                                        $q->select('id')
                                        ->from('mst_coas')
                                        ->where('coa_code_complete', 'LIKE', $sales_coa_code_prefiks.'%')
                                        ->when($branch_id!=0, function($q) use($branch_id){
                                            $q->where('branch_id', '=', $branch_id);
                                        })
                                        ->where('is_master_coa', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->where('active', '=', 'Y')
                                    ->sum('kredit');

                                    $sumSalesLJ = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($m, $year_id){
                                        $q->select('id')
                                        ->from('tx_lokal_journals')
                                        ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($m+1)==1?'0'.($m+1):($m+1)).'\'')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->whereIn('coa_id', function($q) use($sales_coa_code_prefiks, $branch_id){
                                        $q->select('id')
                                        ->from('mst_coas')
                                        ->where('coa_code_complete', 'LIKE', $sales_coa_code_prefiks.'%')
                                        ->when($branch_id!=0, function($q) use($branch_id){
                                            $q->where('branch_id', '=', $branch_id);
                                        })
                                        ->where('is_master_coa', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->where('active', '=', 'Y')
                                    ->sum('kredit');

                                    $sumSales = 0;
                                    switch (strtolower($lokal_input)) {
                                        case 'p':
                                            $sumSales = $sumSalesGJ;
                                            break;
                                        case 'n':
                                            $sumSales = $sumSalesLJ;
                                            break;
                                        default:
                                            $sumSales = $sumSalesGJ+$sumSalesLJ;
                                    }

                                    $SalesPerCols[$m] = $sumSales;
                                @endphp
                                <td>{{ number_format($sumSales,0,",","") }}</td>                                
                            @endfor
                        @else
                            @php
                                $sumSalesGJ = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($month_id, $year_id){
                                    $q->select('id')
                                    ->from('tx_general_journals')
                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($month_id)==1?'0'.$month_id:$month_id).'\'')
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->whereIn('coa_id', function($q) use($sales_coa_code_prefiks, $branch_id){
                                    $q->select('id')
                                    ->from('mst_coas')
                                    ->where('coa_code_complete', 'LIKE', $sales_coa_code_prefiks.'%')
                                    ->when($branch_id!=0, function($q) use($branch_id){
                                        $q->where('branch_id', '=', $branch_id);
                                    })
                                    ->where('is_master_coa', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->where('active', '=', 'Y')
                                ->sum('kredit');

                                $sumSalesLJ = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($month_id, $year_id){
                                    $q->select('id')
                                    ->from('tx_lokal_journals')
                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($month_id)==1?'0'.$month_id:$month_id).'\'')
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->whereIn('coa_id', function($q) use($sales_coa_code_prefiks, $branch_id){
                                    $q->select('id')
                                    ->from('mst_coas')
                                    ->where('coa_code_complete', 'LIKE', $sales_coa_code_prefiks.'%')
                                    ->when($branch_id!=0, function($q) use($branch_id){
                                        $q->where('branch_id', '=', $branch_id);
                                    })
                                    ->where('is_master_coa', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->where('active', '=', 'Y')
                                ->sum('kredit');

                                $sumSales = 0;
                                switch (strtolower($lokal_input)) {
                                    case 'p':
                                        $sumSales = $sumSalesGJ;
                                        break;
                                    case 'n':
                                        $sumSales = $sumSalesLJ;
                                        break;
                                    default:
                                        $sumSales = $sumSalesGJ+$sumSalesLJ;
                                }

                                $SalesPerCols[$month_id-1] = $sumSales;
                            @endphp
                            <td>
                                {{ number_format($sumSales,0,",","") }}                                
                            </td>
                        @endif        
                        @php
                            // hitung total
                            $sumSalesGJtotal = 0;
                            $sumSalesLJtotal = 0;                            
                        @endphp                
                        @if ($month_id==0)
                            @php                                
                                $sumSalesGJtotal = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($month_id, $year_id){
                                    $q->select('id')
                                    ->from('tx_general_journals')
                                    ->when($month_id==0, function($q1) use($year_id){
                                        $q1->whereRaw('DATE_FORMAT(general_journal_date, \'%Y\')=\''.$year_id.'\'');
                                    })
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->whereIn('coa_id', function($q) use($sales_coa_code_prefiks, $branch_id){
                                    $q->select('id')
                                    ->from('mst_coas')
                                    ->where('coa_code_complete', 'LIKE', $sales_coa_code_prefiks.'%')
                                    ->when($branch_id!=0, function($q) use($branch_id){
                                        $q->where('branch_id', '=', $branch_id);
                                    })
                                    ->where('is_master_coa', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->where('active', '=', 'Y')
                                ->sum('kredit');
    
                                $sumSalesLJtotal = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($month_id, $year_id){
                                    $q->select('id')
                                    ->from('tx_lokal_journals')
                                    ->when($month_id==0, function($q1) use($year_id){
                                        $q1->whereRaw('DATE_FORMAT(general_journal_date, \'%Y\')=\''.$year_id.'\'');
                                    })
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->whereIn('coa_id', function($q) use($sales_coa_code_prefiks, $branch_id){
                                    $q->select('id')
                                    ->from('mst_coas')
                                    ->where('coa_code_complete', 'LIKE', $sales_coa_code_prefiks.'%')
                                    ->when($branch_id!=0, function($q) use($branch_id){
                                        $q->where('branch_id', '=', $branch_id);
                                    })
                                    ->where('is_master_coa', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->where('active', '=', 'Y')
                                ->sum('kredit');
                            @endphp                            
                        @else
                            @php
                                $sumSalesGJtotal = 0;
                                $sumSalesLJtotal = 0;                        
                            @endphp
                            @for ($iMonth=1;$iMonth<=$month_id;$iMonth++)
                                @php                                    
                                    $sumSalesGJtotalPerMonth = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($iMonth, $year_id){
                                        $q->select('id')
                                        ->from('tx_general_journals')
                                        ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($iMonth)==1?'0'.$iMonth:$iMonth).'\'')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->whereIn('coa_id', function($q) use($sales_coa_code_prefiks, $branch_id){
                                        $q->select('id')
                                        ->from('mst_coas')
                                        ->where('coa_code_complete', 'LIKE', $sales_coa_code_prefiks.'%')
                                        ->when($branch_id!=0, function($q) use($branch_id){
                                            $q->where('branch_id', '=', $branch_id);
                                        })
                                        ->where('is_master_coa', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->where('active', '=', 'Y')
                                    ->sum('kredit');
    
                                    $sumSalesLJtotalPerMonth = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($iMonth, $year_id){
                                        $q->select('id')
                                        ->from('tx_lokal_journals')
                                        ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($iMonth)==1?'0'.$iMonth:$iMonth).'\'')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->whereIn('coa_id', function($q) use($sales_coa_code_prefiks, $branch_id){
                                        $q->select('id')
                                        ->from('mst_coas')
                                        ->where('coa_code_complete', 'LIKE', $sales_coa_code_prefiks.'%')
                                        ->when($branch_id!=0, function($q) use($branch_id){
                                            $q->where('branch_id', '=', $branch_id);
                                        })
                                        ->where('is_master_coa', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->where('active', '=', 'Y')
                                    ->sum('kredit');

                                    $sumSalesGJtotal+=$sumSalesGJtotalPerMonth;
                                    $sumSalesLJtotal+=$sumSalesLJtotalPerMonth;
                                @endphp
                            @endfor
                        @endif
                        @php
                            $sumSalesTotal = 0;
                            switch (strtolower($lokal_input)) {
                                case 'p':
                                    $sumSalesTotal = $sumSalesGJtotal;
                                    break;
                                case 'n':
                                    $sumSalesTotal = $sumSalesLJtotal;
                                    break;
                                default:
                                    $sumSalesTotal = $sumSalesGJtotal+$sumSalesLJtotal;
                            }

                            $TotalSales = $sumSalesTotal;
                        @endphp
                        <td>{{ number_format($sumSalesTotal,0,",","") }}</td>
                    </tr>
                    {{-- sales --}}

                    {{-- sales return --}}
                    <tr>
                        <td>SALES RETURN</td>
                        @if ($month_id==0)
                            @for ($m=0;$m<count($months);$m++)
                                @php
                                    $sumSalesReturnGJ = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($m, $year_id){
                                        $q->select('id')
                                        ->from('tx_general_journals')
                                        ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($m+1)==1?'0'.($m+1):($m+1)).'\'')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->whereIn('coa_id', function($q) use($sales_return_coa_code_prefiks, $branch_id){
                                        $q->select('id')
                                        ->from('mst_coas')
                                        ->where('coa_code_complete', 'LIKE', $sales_return_coa_code_prefiks.'%')
                                        ->when($branch_id!=0, function($q) use($branch_id){
                                            $q->where('branch_id', '=', $branch_id);
                                        })
                                        ->where('is_master_coa', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->where('active', '=', 'Y')
                                    ->sum('debit');

                                    $sumSalesReturnLJ = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($m, $year_id){
                                        $q->select('id')
                                        ->from('tx_lokal_journals')
                                        ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($m+1)==1?'0'.($m+1):($m+1)).'\'')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->whereIn('coa_id', function($q) use($sales_return_coa_code_prefiks, $branch_id){
                                        $q->select('id')
                                        ->from('mst_coas')
                                        ->where('coa_code_complete', 'LIKE', $sales_return_coa_code_prefiks.'%')
                                        ->when($branch_id!=0, function($q) use($branch_id){
                                            $q->where('branch_id', '=', $branch_id);
                                        })
                                        ->where('is_master_coa', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->where('active', '=', 'Y')
                                    ->sum('debit');

                                    $sumSalesReturn = 0;
                                    switch (strtolower($lokal_input)) {
                                        case 'p':
                                            $sumSalesReturn = $sumSalesReturnGJ;
                                            break;
                                        case 'n':
                                            $sumSalesReturn = $sumSalesReturnLJ;
                                            break;
                                        default:
                                            $sumSalesReturn = $sumSalesReturnGJ+$sumSalesReturnLJ;
                                    }

                                    $SalesReturnPerCols[$m] = $sumSalesReturn;
                                @endphp
                                <td>{{ number_format($sumSalesReturn,0,",","") }}</td>                                
                            @endfor
                        @else
                            @php
                                $sumSalesReturnGJ = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($month_id, $year_id){
                                    $q->select('id')
                                    ->from('tx_general_journals')
                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($month_id)==1?'0'.$month_id:$month_id).'\'')
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->whereIn('coa_id', function($q) use($sales_return_coa_code_prefiks, $branch_id){
                                    $q->select('id')
                                    ->from('mst_coas')
                                    ->where('coa_code_complete', 'LIKE', $sales_return_coa_code_prefiks.'%')
                                    ->when($branch_id!=0, function($q) use($branch_id){
                                        $q->where('branch_id', '=', $branch_id);
                                    })
                                    ->where('is_master_coa', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->where('active', '=', 'Y')
                                ->sum('debit');

                                $sumSalesReturnLJ = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($month_id, $year_id){
                                    $q->select('id')
                                    ->from('tx_lokal_journals')
                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($month_id)==1?'0'.$month_id:$month_id).'\'')
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->whereIn('coa_id', function($q) use($sales_return_coa_code_prefiks, $branch_id){
                                    $q->select('id')
                                    ->from('mst_coas')
                                    ->where('coa_code_complete', 'LIKE', $sales_return_coa_code_prefiks.'%')
                                    ->when($branch_id!=0, function($q) use($branch_id){
                                        $q->where('branch_id', '=', $branch_id);
                                    })
                                    ->where('is_master_coa', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->where('active', '=', 'Y')
                                ->sum('debit');

                                $sumSalesReturn = 0;
                                switch (strtolower($lokal_input)) {
                                    case 'p':
                                        $sumSalesReturn = $sumSalesReturnGJ;
                                        break;
                                    case 'n':
                                        $sumSalesReturn = $sumSalesReturnLJ;
                                        break;
                                    default:
                                        $sumSalesReturn = $sumSalesReturnGJ+$sumSalesReturnLJ;
                                }

                                $SalesReturnPerCols[$month_id-1] = $sumSalesReturn;
                            @endphp
                            <td>
                                {{ number_format($sumSalesReturn,0,",","") }}                                
                            </td>
                        @endif
                        @php
                            // hitung total
                            $sumSalesReturnGJtotal = 0;
                            $sumSalesReturnLJtotal = 0;                            
                        @endphp                
                        @if ($month_id==0)
                            @php                                
                                $sumSalesReturnGJtotal = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($month_id, $year_id){
                                    $q->select('id')
                                    ->from('tx_general_journals')
                                    ->when($month_id==0, function($q1) use($year_id){
                                        $q1->whereRaw('DATE_FORMAT(general_journal_date, \'%Y\')=\''.$year_id.'\'');
                                    })
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->whereIn('coa_id', function($q) use($sales_return_coa_code_prefiks, $branch_id){
                                    $q->select('id')
                                    ->from('mst_coas')
                                    ->where('coa_code_complete', 'LIKE', $sales_return_coa_code_prefiks.'%')
                                    ->when($branch_id!=0, function($q) use($branch_id){
                                        $q->where('branch_id', '=', $branch_id);
                                    })
                                    ->where('is_master_coa', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->where('active', '=', 'Y')
                                ->sum('debit');
    
                                $sumSalesReturnLJtotal = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($month_id, $year_id){
                                    $q->select('id')
                                    ->from('tx_lokal_journals')
                                    ->when($month_id==0, function($q1) use($year_id){
                                        $q1->whereRaw('DATE_FORMAT(general_journal_date, \'%Y\')=\''.$year_id.'\'');
                                    })
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->whereIn('coa_id', function($q) use($sales_return_coa_code_prefiks, $branch_id){
                                    $q->select('id')
                                    ->from('mst_coas')
                                    ->where('coa_code_complete', 'LIKE', $sales_return_coa_code_prefiks.'%')
                                    ->when($branch_id!=0, function($q) use($branch_id){
                                        $q->where('branch_id', '=', $branch_id);
                                    })
                                    ->where('is_master_coa', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->where('active', '=', 'Y')
                                ->sum('debit');
                            @endphp                            
                        @else
                            @php
                                $sumSalesReturnGJ = 0;
                                $sumSalesReturnLJ = 0;                     
                            @endphp
                            @for ($iMonth=1;$iMonth<=$month_id;$iMonth++)
                                @php                                    
                                    $sumSalesReturnGJtotalPerMonth = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($iMonth, $year_id){
                                        $q->select('id')
                                        ->from('tx_general_journals')
                                        ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($iMonth)==1?'0'.$iMonth:$iMonth).'\'')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->whereIn('coa_id', function($q) use($sales_return_coa_code_prefiks, $branch_id){
                                        $q->select('id')
                                        ->from('mst_coas')
                                        ->where('coa_code_complete', 'LIKE', $sales_return_coa_code_prefiks.'%')
                                        ->when($branch_id!=0, function($q) use($branch_id){
                                            $q->where('branch_id', '=', $branch_id);
                                        })
                                        ->where('is_master_coa', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->where('active', '=', 'Y')
                                    ->sum('debit');
    
                                    $sumSalesReturnLJtotalPerMonth = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($iMonth, $year_id){
                                        $q->select('id')
                                        ->from('tx_lokal_journals')
                                        ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($iMonth)==1?'0'.$iMonth:$iMonth).'\'')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->whereIn('coa_id', function($q) use($sales_return_coa_code_prefiks, $branch_id){
                                        $q->select('id')
                                        ->from('mst_coas')
                                        ->where('coa_code_complete', 'LIKE', $sales_return_coa_code_prefiks.'%')
                                        ->when($branch_id!=0, function($q) use($branch_id){
                                            $q->where('branch_id', '=', $branch_id);
                                        })
                                        ->where('is_master_coa', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->where('active', '=', 'Y')
                                    ->sum('debit');

                                    $sumSalesReturnGJtotal+=$sumSalesReturnGJtotalPerMonth;
                                    $sumSalesReturnLJtotal+=$sumSalesReturnLJtotalPerMonth;
                                @endphp
                            @endfor
                        @endif
                        @php
                            $sumSalesReturntotal = 0;
                            switch (strtolower($lokal_input)) {
                                case 'p':
                                    $sumSalesReturntotal = $sumSalesReturnGJtotal;
                                    break;
                                case 'n':
                                    $sumSalesReturntotal = $sumSalesReturnLJtotal;
                                    break;
                                default:
                                    $sumSalesReturntotal = $sumSalesReturnGJtotal+$sumSalesReturnLJtotal;
                            }

                            $TotalSalesReturn = $sumSalesReturntotal;
                        @endphp
                        <td>{{ number_format($sumSalesReturntotal,0,",","") }}</td>
                    </tr>
                    {{-- sales return --}}
                    
                    {{-- sales discount --}}
                    <tr>
                        <td>SALES DISCOUNT</td>
                        @if ($month_id==0)
                            @for ($m=0;$m<count($months);$m++)
                                @php
                                    $sumSalesDiscountGJ = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($m, $year_id){
                                        $q->select('id')
                                        ->from('tx_general_journals')
                                        ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($m+1)==1?'0'.($m+1):($m+1)).'\'')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->whereIn('coa_id', function($q) use($sales_discount_coa_code_prefiks, $branch_id){
                                        $q->select('id')
                                        ->from('mst_coas')
                                        ->where('coa_code_complete', 'LIKE', $sales_discount_coa_code_prefiks.'%')
                                        ->when($branch_id!=0, function($q) use($branch_id){
                                            $q->where('branch_id', '=', $branch_id);
                                        })
                                        ->where('is_master_coa', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->where('active', '=', 'Y')
                                    ->sum('debit');

                                    $sumSalesDiscountLJ = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($m, $year_id){
                                        $q->select('id')
                                        ->from('tx_lokal_journals')
                                        ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($m+1)==1?'0'.($m+1):($m+1)).'\'')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->whereIn('coa_id', function($q) use($sales_discount_coa_code_prefiks, $branch_id){
                                        $q->select('id')
                                        ->from('mst_coas')
                                        ->where('coa_code_complete', 'LIKE', $sales_discount_coa_code_prefiks.'%')
                                        ->when($branch_id!=0, function($q) use($branch_id){
                                            $q->where('branch_id', '=', $branch_id);
                                        })
                                        ->where('is_master_coa', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->where('active', '=', 'Y')
                                    ->sum('debit');

                                    $sumSalesDiscount = 0;
                                    switch (strtolower($lokal_input)) {
                                        case 'p':
                                            $sumSalesDiscount = $sumSalesDiscountGJ;
                                            break;
                                        case 'n':
                                            $sumSalesDiscount = $sumSalesDiscountLJ;
                                            break;
                                        default:
                                            $sumSalesDiscount = $sumSalesDiscountGJ+$sumSalesDiscountLJ;
                                    }

                                    $SalesDiscountPerCols[$m] = $sumSalesDiscount;
                                @endphp
                                <td>{{ number_format($sumSalesDiscount,0,",","") }}</td>                                
                            @endfor
                        @else
                            @php
                                $sumSalesDiscountGJ = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($month_id, $year_id){
                                    $q->select('id')
                                    ->from('tx_general_journals')
                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($month_id)==1?'0'.$month_id:$month_id).'\'')
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->whereIn('coa_id', function($q) use($sales_discount_coa_code_prefiks, $branch_id){
                                    $q->select('id')
                                    ->from('mst_coas')
                                    ->where('coa_code_complete', 'LIKE', $sales_discount_coa_code_prefiks.'%')
                                    ->when($branch_id!=0, function($q) use($branch_id){
                                        $q->where('branch_id', '=', $branch_id);
                                    })
                                    ->where('is_master_coa', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->where('active', '=', 'Y')
                                ->sum('debit');

                                $sumSalesDiscountLJ = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($month_id, $year_id){
                                    $q->select('id')
                                    ->from('tx_lokal_journals')
                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($month_id)==1?'0'.$month_id:$month_id).'\'')
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->whereIn('coa_id', function($q) use($sales_discount_coa_code_prefiks, $branch_id){
                                    $q->select('id')
                                    ->from('mst_coas')
                                    ->where('coa_code_complete', 'LIKE', $sales_discount_coa_code_prefiks.'%')
                                    ->when($branch_id!=0, function($q) use($branch_id){
                                        $q->where('branch_id', '=', $branch_id);
                                    })
                                    ->where('is_master_coa', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->where('active', '=', 'Y')
                                ->sum('debit');

                                $sumSalesDiscount = 0;
                                switch (strtolower($lokal_input)) {
                                    case 'p':
                                        $sumSalesDiscount = $sumSalesDiscountGJ;
                                        break;
                                    case 'n':
                                        $sumSalesDiscount = $sumSalesDiscountLJ;
                                        break;
                                    default:
                                        $sumSalesDiscount = $sumSalesDiscountGJ+$sumSalesDiscountLJ;
                                }

                                $SalesDiscountPerCols[$month_id-1] = $sumSalesDiscount;
                            @endphp
                            <td>
                                {{ number_format($sumSalesDiscount,0,",","") }}                                
                            </td>
                        @endif
                        @php
                            // hitung total
                            $sumSalesDiscountGJtotal = 0;
                            $sumSalesDiscountLJtotal = 0;                            
                        @endphp                
                        @if ($month_id==0)
                            @php                                
                                $sumSalesDiscountGJtotal = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($month_id, $year_id){
                                    $q->select('id')
                                    ->from('tx_general_journals')
                                    ->when($month_id==0, function($q1) use($year_id){
                                        $q1->whereRaw('DATE_FORMAT(general_journal_date, \'%Y\')=\''.$year_id.'\'');
                                    })
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->whereIn('coa_id', function($q) use($sales_discount_coa_code_prefiks, $branch_id){
                                    $q->select('id')
                                    ->from('mst_coas')
                                    ->where('coa_code_complete', 'LIKE', $sales_discount_coa_code_prefiks.'%')
                                    ->when($branch_id!=0, function($q) use($branch_id){
                                        $q->where('branch_id', '=', $branch_id);
                                    })
                                    ->where('is_master_coa', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->where('active', '=', 'Y')
                                ->sum('debit');
    
                                $sumSalesDiscountLJtotal = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($month_id, $year_id){
                                    $q->select('id')
                                    ->from('tx_lokal_journals')
                                    ->when($month_id==0, function($q1) use($year_id){
                                        $q1->whereRaw('DATE_FORMAT(general_journal_date, \'%Y\')=\''.$year_id.'\'');
                                    })
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->whereIn('coa_id', function($q) use($sales_discount_coa_code_prefiks, $branch_id){
                                    $q->select('id')
                                    ->from('mst_coas')
                                    ->where('coa_code_complete', 'LIKE', $sales_discount_coa_code_prefiks.'%')
                                    ->when($branch_id!=0, function($q) use($branch_id){
                                        $q->where('branch_id', '=', $branch_id);
                                    })
                                    ->where('is_master_coa', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->where('active', '=', 'Y')
                                ->sum('debit');
                            @endphp                            
                        @else
                            @php
                                $sumSalesDiscountGJ = 0;
                                $sumSalesDiscountLJ = 0;                     
                            @endphp
                            @for ($iMonth=1;$iMonth<=$month_id;$iMonth++)
                                @php                                    
                                    $sumSalesDiscountGJtotalPerMonth = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($iMonth, $year_id){
                                        $q->select('id')
                                        ->from('tx_general_journals')
                                        ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($iMonth)==1?'0'.$iMonth:$iMonth).'\'')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->whereIn('coa_id', function($q) use($sales_discount_coa_code_prefiks, $branch_id){
                                        $q->select('id')
                                        ->from('mst_coas')
                                        ->where('coa_code_complete', 'LIKE', $sales_discount_coa_code_prefiks.'%')
                                        ->when($branch_id!=0, function($q) use($branch_id){
                                            $q->where('branch_id', '=', $branch_id);
                                        })
                                        ->where('is_master_coa', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->where('active', '=', 'Y')
                                    ->sum('debit');
    
                                    $sumSalesDiscountLJtotalPerMonth = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($iMonth, $year_id){
                                        $q->select('id')
                                        ->from('tx_lokal_journals')
                                        ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($iMonth)==1?'0'.$iMonth:$iMonth).'\'')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->whereIn('coa_id', function($q) use($sales_discount_coa_code_prefiks, $branch_id){
                                        $q->select('id')
                                        ->from('mst_coas')
                                        ->where('coa_code_complete', 'LIKE', $sales_discount_coa_code_prefiks.'%')
                                        ->when($branch_id!=0, function($q) use($branch_id){
                                            $q->where('branch_id', '=', $branch_id);
                                        })
                                        ->where('is_master_coa', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->where('active', '=', 'Y')
                                    ->sum('debit');

                                    $sumSalesDiscountGJtotal+=$sumSalesDiscountGJtotalPerMonth;
                                    $sumSalesDiscountLJtotal+=$sumSalesDiscountLJtotalPerMonth;
                                @endphp
                            @endfor
                        @endif
                        @php
                            $sumSalesDiscountTotal = 0;
                            switch (strtolower($lokal_input)) {
                                case 'p':
                                    $sumSalesDiscountTotal = $sumSalesDiscountGJtotal;
                                    break;
                                case 'n':
                                    $sumSalesDiscountTotal = $sumSalesDiscountLJtotal;
                                    break;
                                default:
                                    $sumSalesDiscountTotal = $sumSalesDiscountGJtotal+$sumSalesDiscountLJtotal;
                            }

                            $TotalSalesDiscount = $sumSalesDiscountTotal;
                        @endphp
                        <td>{{ number_format($sumSalesDiscountTotal,0,",","") }}</td>
                    </tr>
                    {{-- sales discount --}}
                    
                    {{-- netto sales --}}
                    <tr>
                        <td style="font-weight: 700;border-top:1px solid black;">NETTO SALES</td>
                        @if ($month_id==0)
                            @for ($m=0;$m<count($months);$m++)
                                @php
                                    $NettoSalesPerCols[$m] = $SalesPerCols[$m]-$SalesReturnPerCols[$m]-$SalesDiscountPerCols[$m];
                                @endphp
                                <td style="border-top:1px solid black;">
                                    {{ number_format($NettoSalesPerCols[$m],0,",","") }}
                                </td>
                            @endfor
                        @else
                            @php
                                $NettoSalesPerCols[$month_id-1] = $SalesPerCols[$month_id-1]-$SalesReturnPerCols[$month_id-1]-$SalesDiscountPerCols[$month_id-1];
                            @endphp
                            <td style="border-top:1px solid black;">
                                {{ number_format($NettoSalesPerCols[$month_id-1],0,",","") }}
                            </td>
                        @endif
                        @php
                            $TotalNettoSales = $TotalSales-$TotalSalesReturn-$TotalSalesDiscount;
                        @endphp
                        <td style="border-top:1px solid black;">
                            {{ number_format($TotalNettoSales,0,",","") }}
                        </td>
                    </tr>
                    {{-- netto sales --}}

                    {{-- cogs --}}
                    <tr>
                        <td style="font-weight: 700;border-bottom:1px solid black;">COGS</td>
                        @if ($month_id==0)
                            @for ($m=0;$m<count($months);$m++)
                                @php
                                    $sumCogsGJ = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($m, $year_id){
                                        $q->select('id')
                                        ->from('tx_general_journals')
                                        ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($m+1)==1?'0'.($m+1):($m+1)).'\'')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->whereIn('coa_id', function($q) use($cogs_coa_code_prefiks, $branch_id){
                                        $q->select('id')
                                        ->from('mst_coas')
                                        ->where('coa_code_complete', 'LIKE', $cogs_coa_code_prefiks.'%')
                                        ->when($branch_id!=0, function($q) use($branch_id){
                                            $q->where('branch_id', '=', $branch_id);
                                        })
                                        ->where('is_master_coa', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->where('active', '=', 'Y')
                                    ->sum('debit');

                                    $sumCogsLJ = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($m, $year_id){
                                        $q->select('id')
                                        ->from('tx_lokal_journals')
                                        ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($m+1)==1?'0'.($m+1):($m+1)).'\'')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->whereIn('coa_id', function($q) use($cogs_coa_code_prefiks, $branch_id){
                                        $q->select('id')
                                        ->from('mst_coas')
                                        ->where('coa_code_complete', 'LIKE', $cogs_coa_code_prefiks.'%')
                                        ->when($branch_id!=0, function($q) use($branch_id){
                                            $q->where('branch_id', '=', $branch_id);
                                        })
                                        ->where('is_master_coa', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->where('active', '=', 'Y')
                                    ->sum('debit');

                                    $sumCogs = 0;
                                    switch (strtolower($lokal_input)) {
                                        case 'p':
                                            $sumCogs = $sumCogsGJ;
                                            break;
                                        case 'n':
                                            $sumCogs = $sumCogsLJ;
                                            break;
                                        default:
                                            $sumCogs = $sumCogsGJ+$sumCogsLJ;
                                    }

                                    $CogsPerCols[$m] = $sumCogs;
                                @endphp
                                <td style="border-bottom:1px solid black;">{{ number_format($sumCogs,0,",","") }}</td>                                
                            @endfor
                        @else
                            @php
                                $sumCogsGJ = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($month_id, $year_id){
                                    $q->select('id')
                                    ->from('tx_general_journals')
                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($month_id)==1?'0'.$month_id:$month_id).'\'')
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->whereIn('coa_id', function($q) use($cogs_coa_code_prefiks, $branch_id){
                                    $q->select('id')
                                    ->from('mst_coas')
                                    ->where('coa_code_complete', 'LIKE', $cogs_coa_code_prefiks.'%')
                                    ->when($branch_id!=0, function($q) use($branch_id){
                                        $q->where('branch_id', '=', $branch_id);
                                    })
                                    ->where('is_master_coa', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->where('active', '=', 'Y')
                                ->sum('debit');

                                $sumCogsLJ = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($month_id, $year_id){
                                    $q->select('id')
                                    ->from('tx_lokal_journals')
                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($month_id)==1?'0'.$month_id:$month_id).'\'')
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->whereIn('coa_id', function($q) use($cogs_coa_code_prefiks, $branch_id){
                                    $q->select('id')
                                    ->from('mst_coas')
                                    ->where('coa_code_complete', 'LIKE', $cogs_coa_code_prefiks.'%')
                                    ->when($branch_id!=0, function($q) use($branch_id){
                                        $q->where('branch_id', '=', $branch_id);
                                    })
                                    ->where('is_master_coa', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->where('active', '=', 'Y')
                                ->sum('debit');

                                $sumCogs = 0;
                                switch (strtolower($lokal_input)) {
                                    case 'p':
                                        $sumCogs = $sumCogsGJ;
                                        break;
                                    case 'n':
                                        $sumCogs = $sumCogsLJ;
                                        break;
                                    default:
                                        $sumCogs = $sumCogsGJ+$sumCogsLJ;
                                }

                                $CogsPerCols[$month_id-1] = $sumCogs;
                            @endphp
                            <td style="border-bottom:1px solid black;">{{ number_format($sumCogs,0,",","") }}</td>
                        @endif
                        @php
                            // hitung total
                            $sumCogsGJtotal = 0;
                            $sumCogsLJtotal = 0;                            
                        @endphp                
                        @if ($month_id==0)
                            @php                                
                                $sumCogsGJtotal = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($month_id, $year_id){
                                    $q->select('id')
                                    ->from('tx_general_journals')
                                    ->when($month_id==0, function($q1) use($year_id){
                                        $q1->whereRaw('DATE_FORMAT(general_journal_date, \'%Y\')=\''.$year_id.'\'');
                                    })
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->whereIn('coa_id', function($q) use($cogs_coa_code_prefiks, $branch_id){
                                    $q->select('id')
                                    ->from('mst_coas')
                                    ->where('coa_code_complete', 'LIKE', $cogs_coa_code_prefiks.'%')
                                    ->when($branch_id!=0, function($q) use($branch_id){
                                        $q->where('branch_id', '=', $branch_id);
                                    })
                                    ->where('is_master_coa', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->where('active', '=', 'Y')
                                ->sum('debit');
    
                                $sumCogsLJtotal = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($month_id, $year_id){
                                    $q->select('id')
                                    ->from('tx_lokal_journals')
                                    ->when($month_id==0, function($q1) use($year_id){
                                        $q1->whereRaw('DATE_FORMAT(general_journal_date, \'%Y\')=\''.$year_id.'\'');
                                    })
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->whereIn('coa_id', function($q) use($cogs_coa_code_prefiks, $branch_id){
                                    $q->select('id')
                                    ->from('mst_coas')
                                    ->where('coa_code_complete', 'LIKE', $cogs_coa_code_prefiks.'%')
                                    ->when($branch_id!=0, function($q) use($branch_id){
                                        $q->where('branch_id', '=', $branch_id);
                                    })
                                    ->where('is_master_coa', '=', 'N')
                                    ->where('active', '=', 'Y');
                                })
                                ->where('active', '=', 'Y')
                                ->sum('debit');
                            @endphp                            
                        @else
                            @php
                                $sumCogsGJ = 0;
                                $sumCogsLJ = 0;                          
                            @endphp
                            @for ($iMonth=1;$iMonth<=$month_id;$iMonth++)
                                @php                                    
                                    $sumCogsGJtotalPerMonth = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($iMonth, $year_id){
                                        $q->select('id')
                                        ->from('tx_general_journals')
                                        ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($iMonth)==1?'0'.$iMonth:$iMonth).'\'')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->whereIn('coa_id', function($q) use($cogs_coa_code_prefiks, $branch_id){
                                        $q->select('id')
                                        ->from('mst_coas')
                                        ->where('coa_code_complete', 'LIKE', $cogs_coa_code_prefiks.'%')
                                        ->when($branch_id!=0, function($q) use($branch_id){
                                            $q->where('branch_id', '=', $branch_id);
                                        })
                                        ->where('is_master_coa', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->where('active', '=', 'Y')
                                    ->sum('debit');
    
                                    $sumCogsLJtotalPerMonth = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($iMonth, $year_id){
                                        $q->select('id')
                                        ->from('tx_lokal_journals')
                                        ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($iMonth)==1?'0'.$iMonth:$iMonth).'\'')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->whereIn('coa_id', function($q) use($cogs_coa_code_prefiks, $branch_id){
                                        $q->select('id')
                                        ->from('mst_coas')
                                        ->where('coa_code_complete', 'LIKE', $cogs_coa_code_prefiks.'%')
                                        ->when($branch_id!=0, function($q) use($branch_id){
                                            $q->where('branch_id', '=', $branch_id);
                                        })
                                        ->where('is_master_coa', '=', 'N')
                                        ->where('active', '=', 'Y');
                                    })
                                    ->where('active', '=', 'Y')
                                    ->sum('debit');

                                    $sumCogsGJtotal+=$sumCogsGJtotalPerMonth;
                                    $sumCogsLJtotal+=$sumCogsLJtotalPerMonth;
                                @endphp
                            @endfor
                        @endif
                        @php
                            $sumCogsTotal = 0;
                            switch (strtolower($lokal_input)) {
                                case 'p':
                                    $sumCogsTotal = $sumCogsGJtotal;
                                    break;
                                case 'n':
                                    $sumCogsTotal = $sumCogsLJtotal;
                                    break;
                                default:
                                    $sumCogsTotal = $sumCogsGJtotal+$sumCogsLJtotal;
                            }

                            $TotalCogs = $sumCogsTotal;
                        @endphp
                        <td style="border-bottom:1px solid black;">{{ number_format($sumCogsTotal,0,",","") }}</td>
                    </tr>
                    {{-- cogs --}}
                    
                    {{-- gross profit --}}
                    <tr>
                        <td style="font-weight: 700;">GROSS PROFIT</td>
                        @if ($month_id==0)
                            @for ($m=0;$m<count($months);$m++)
                                @php
                                    $GrossProfitPerCols[$m] = $NettoSalesPerCols[$m]-$CogsPerCols[$m];
                                @endphp
                                <td>
                                    {{ number_format($GrossProfitPerCols[$m],0,",","") }}
                                </td>
                            @endfor
                        @else
                            @php
                                $GrossProfitPerCols[$month_id-1] = $NettoSalesPerCols[$month_id-1]-$CogsPerCols[$month_id-1];
                            @endphp
                            <td>
                                {{ number_format($GrossProfitPerCols[$month_id-1],0,",","") }}
                            </td>
                        @endif
                        @php
                            $TotalGrossProfit = $TotalNettoSales-$TotalCogs;
                        @endphp
                        <td>
                            {{ number_format($TotalGrossProfit,0,",","") }}
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        @if ($month_id==0)
                            @for ($m=0;$m<count($months);$m++)
                                <td style="text-align: right;">
                                    {{ number_format($NettoSalesPerCols[$m]!=0?($GrossProfitPerCols[$m]/$NettoSalesPerCols[$m])*100:0,2,",","") }}
                                </td>
                            @endfor
                        @else
                            <td style="text-align: right;">
                                {{ number_format($NettoSalesPerCols[$month_id-1]!=0?($GrossProfitPerCols[$month_id-1]/$NettoSalesPerCols[$month_id-1])*100:0,2,",","") }}
                            </td>
                        @endif
                        <td style="text-align: right;">
                            {{ number_format($TotalNettoSales>0?($TotalGrossProfit/$TotalNettoSales)*100:0,2,",","") }}
                        </td>
                    </tr>
                    {{-- gross profit --}}
                    
                    {{-- blank row --}}
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    {{-- blank row --}}
                    
                    
                    {{-- expense --}}
                    <tr>
                        <td style="font-weight: 700;">EXPENSES</td>
                    </tr>
                    @php
                        $ExpBranches = \App\Models\Mst_branch::when($branch_id!=0, function($q) use($branch_id){
                            $q->where('id', '=', $branch_id);
                        })
                        ->where('active', '=', 'Y')
                        ->orderBy('initial', 'ASC')
                        ->get();
                    @endphp
                    @foreach ($ExpBranches as $xb)                        
                        @if (strtolower($lokal_input)=='p' || strtolower($lokal_input)=='a')
                            <tr>
                                <td>EXPENSE {{ $xb->initial }}-P</td>
                            </tr>
                            @php
                                $qCoa6xP = \App\Models\Mst_coa::where('coa_code_complete', 'LIKE', $expense_coa_code_prefiks.'%')
                                ->when(strtolower($lokal_input)=='p' || strtolower($lokal_input)=='a', function($q){
                                    $q->whereRaw('UPPER(local)=\'P\'');
                                })
                                ->where([
                                    'branch_id' => $xb->id,
                                    'is_master_coa' => 'N',
                                    'active' => 'Y',
                                ])
                                ->orderBy('coa_name', 'ASC')
                                ->orderBy('coa_code_complete', 'ASC')
                                ->get();                            
                            @endphp
                            @foreach ($qCoa6xP as $p)
                                <tr>
                                    <td>- {{ $p->coa_name }}</td>
                                    @if ($month_id==0)
                                        @for ($m=0;$m<count($months);$m++)
                                            @php
                                                $sumExpenseGJ = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($m, $year_id){
                                                    $q->select('id')
                                                    ->from('tx_general_journals')
                                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($m+1)==1?'0'.($m+1):($m+1)).'\'')
                                                    ->where('is_draft', '=', 'N')
                                                    ->where('active', '=', 'Y');
                                                })
                                                ->where([
                                                    'coa_id' => $p->id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('debit');

                                                $OpeExpensePerCols[$m]+=$sumExpenseGJ;
                                            @endphp
                                            <td>{{ number_format($sumExpenseGJ,0,",","") }}</td>                                
                                        @endfor

                                        @php
                                            $sumTotalExpenseGJ = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($year_id){
                                                $q->select('id')
                                                ->from('tx_general_journals')
                                                ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y\')=\''.$year_id.'\'')
                                                ->where('is_draft', '=', 'N')
                                                ->where('active', '=', 'Y');
                                            })
                                            ->where([
                                                'coa_id' => $p->id,
                                                'active' => 'Y',
                                            ])
                                            ->sum('debit');

                                            $TotalOpeExpense+=$sumTotalExpenseGJ;
                                        @endphp
                                        <td>{{ number_format($sumTotalExpenseGJ,0,",","") }}</td> 
                                    @else
                                        @php
                                            $sumExpenseGJ = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($month_id, $year_id){
                                                $q->select('id')
                                                ->from('tx_general_journals')
                                                ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($month_id)==1?'0'.$month_id:$month_id).'\'')
                                                ->where('is_draft', '=', 'N')
                                                ->where('active', '=', 'Y');
                                            })
                                            ->where([
                                                'coa_id' => $p->id,
                                                'active' => 'Y',
                                            ])
                                            ->sum('debit');

                                            $OpeExpensePerCols[$month_id-1]+=$sumExpenseGJ;
                                        @endphp
                                        <td>{{ number_format($sumExpenseGJ,0,",","") }}</td>

                                        @php
                                            $sumExpenseGJtotal = 0;                          
                                        @endphp
                                        @for ($iMonth=1;$iMonth<=$month_id;$iMonth++)
                                            @php                                                
                                                $sumExpenseGJtotalPerMonth = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($iMonth, $year_id){
                                                    $q->select('id')
                                                    ->from('tx_general_journals')
                                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($iMonth)==1?'0'.$iMonth:$iMonth).'\'')
                                                    ->where('is_draft', '=', 'N')
                                                    ->where('active', '=', 'Y');
                                                })
                                                ->where([
                                                    'coa_id' => $p->id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('debit');

                                                $sumExpenseGJtotal+=$sumExpenseGJtotalPerMonth;
                                            @endphp
                                        @endfor
                                        @php
                                            $TotalOpeExpense+=$sumExpenseGJtotal;
                                        @endphp
                                        <td>{{ number_format($sumExpenseGJtotal,0,",","") }}</td>
                                    @endif 
                                </tr>
                            @endforeach
                        @endif
                        @if (strtolower($lokal_input)=='n' || strtolower($lokal_input)=='a')
                            <tr>
                                <td>EXPENSE {{ $xb->initial }}-NP</td>
                            </tr>
                            @php
                                $qCoa6xN = \App\Models\Mst_coa::where('coa_code_complete', 'LIKE', $expense_coa_code_prefiks.'%')
                                ->when(strtolower($lokal_input)=='n' || strtolower($lokal_input)=='a', function($q){
                                    $q->whereRaw('UPPER(local)=\'N\'');
                                })
                                ->where([
                                    'branch_id' => $xb->id,
                                    'is_master_coa' => 'N',
                                    'active' => 'Y',
                                ])
                                ->orderBy('coa_name', 'ASC')
                                ->orderBy('coa_code_complete', 'ASC')
                                ->get();                            
                            @endphp
                            @foreach ($qCoa6xN as $n)
                                <tr>
                                    <td>- {{ $n->coa_name }}</td>
                                    @if ($month_id==0)
                                        @for ($m=0;$m<count($months);$m++)
                                            @php
                                                $sumExpenseLJ = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($m, $year_id){
                                                    $q->select('id')
                                                    ->from('tx_lokal_journals')
                                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($m+1)==1?'0'.($m+1):($m+1)).'\'')
                                                    ->where('is_draft', '=', 'N')
                                                    ->where('active', '=', 'Y');
                                                })
                                                ->where([
                                                    'coa_id' => $n->id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('debit');

                                                $OpeExpensePerCols[$m]+=$sumExpenseLJ;
                                            @endphp
                                            <td>{{ number_format($sumExpenseLJ,0,",","") }}</td>                                
                                        @endfor

                                        @php
                                            $sumTotalExpenseLJ = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($year_id){
                                                $q->select('id')
                                                ->from('tx_lokal_journals')
                                                ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y\')=\''.$year_id.'\'')
                                                ->where('is_draft', '=', 'N')
                                                ->where('active', '=', 'Y');
                                            })
                                            ->where([
                                                'coa_id' => $n->id,
                                                'active' => 'Y',
                                            ])
                                            ->sum('debit');

                                            $TotalOpeExpense+=$sumTotalExpenseLJ;
                                        @endphp
                                        <td>{{ number_format($sumTotalExpenseLJ,0,",","") }}</td>
                                    @else
                                        @php
                                            $sumExpenseLJ = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($month_id, $year_id){
                                                $q->select('id')
                                                ->from('tx_lokal_journals')
                                                ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($month_id)==1?'0'.$month_id:$month_id).'\'')
                                                ->where('is_draft', '=', 'N')
                                                ->where('active', '=', 'Y');
                                            })
                                            ->where([
                                                'coa_id' => $n->id,
                                                'active' => 'Y',
                                            ])
                                            ->sum('debit');

                                            $OpeExpensePerCols[$month_id-1]+=$sumExpenseLJ;
                                        @endphp
                                        <td>{{ number_format($sumExpenseLJ,0,",","") }}</td>

                                        @php
                                            $sumExpenseLJtotal = 0;                          
                                        @endphp
                                        @for ($iMonth=1;$iMonth<=$month_id;$iMonth++)
                                            @php                                                
                                                $sumExpenseLJtotalPerMonth = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($iMonth, $year_id){
                                                    $q->select('id')
                                                    ->from('tx_lokal_journals')
                                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($iMonth)==1?'0'.$iMonth:$iMonth).'\'')
                                                    ->where('is_draft', '=', 'N')
                                                    ->where('active', '=', 'Y');
                                                })
                                                ->where([
                                                    'coa_id' => $n->id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('debit');

                                                $sumExpenseLJtotal+=$sumExpenseLJtotalPerMonth;
                                            @endphp
                                        @endfor
                                        @php
                                            $TotalOpeExpense+=$sumExpenseLJtotal;
                                        @endphp
                                        <td>{{ number_format($sumExpenseLJtotal,0,",","") }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                    {{-- expense --}}

                    <tr>
                        <td style="font-weight: 700;border-top:1px solid black;">TOTAL OPERATING EXPENSES</td>
                        @if ($month_id==0)
                            @for ($m=0;$m<count($months);$m++)
                                <td style="border-top:1px solid black;">
                                    {{ number_format($OpeExpensePerCols[$m],0,",","") }}
                                </td>
                            @endfor
                        @else
                            <td style="border-top:1px solid black;">
                                {{ number_format($OpeExpensePerCols[$month_id-1],0,",","") }}
                            </td>
                        @endif
                        <td style="border-top:1px solid black;">
                            {{ number_format($TotalOpeExpense,0,",","") }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700;">&nbsp;</td>
                        @if ($month_id==0)
                            @for ($m=0;$m<count($months);$m++)
                                <td style="text-align: right;">
                                    {{ $NettoSalesPerCols[$m]!=0?number_format(($OpeExpensePerCols[$m]/$NettoSalesPerCols[$m])*100,2,",",""):0 }}
                                </td>
                            @endfor
                        @else
                            <td style="text-align: right;">
                                {{ $NettoSalesPerCols[$month_id-1]!=0?number_format(($OpeExpensePerCols[$month_id-1]/$NettoSalesPerCols[$month_id-1])*100,2,",",""):0 }}
                            </td>
                        @endif
                        <td style="text-align: right;">
                            {{ $TotalNettoSales!=0?number_format(($TotalOpeExpense/$TotalNettoSales)*100,2,",",""):0 }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700;">NETTO PROFIT OPERATION</td>
                        @if ($month_id==0)
                            @for ($m=0;$m<count($months);$m++)
                                <td style="text-align:right;">
                                    {{ number_format($GrossProfitPerCols[$m]-$OpeExpensePerCols[$m],0,",","") }}
                                </td>
                            @endfor
                        @else
                            <td style="text-align:right;">
                                {{ number_format($GrossProfitPerCols[$month_id-1]-$OpeExpensePerCols[$month_id-1],0,",","") }}
                            </td>
                        @endif
                        <td style="text-align:right;">
                            {{ number_format($TotalGrossProfit-$TotalOpeExpense,0,",","") }}
                        </td>
                    </tr>

                    {{-- blank row --}}
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    {{-- blank row --}}
                    
                    
                    {{-- other income --}}
                    <tr>
                        <td style="font-weight: 700;">OTHER INCOME</td>
                    </tr>
                    @php
                        $ExpBranches = \App\Models\Mst_branch::when($branch_id!=0, function($q) use($branch_id){
                            $q->where('id', '=', $branch_id);
                        })
                        ->where('active', '=', 'Y')
                        ->orderBy('initial', 'ASC')
                        ->get();
                    @endphp
                    @foreach ($ExpBranches as $xb)                        
                        @if (strtolower($lokal_input)=='p' || strtolower($lokal_input)=='a')
                            {{-- <tr>
                                <td>EXPENSE {{ $xb->initial }}-P</td>
                            </tr> --}}
                            @php
                                $qCoa8xP = \App\Models\Mst_coa::where('coa_code_complete', 'LIKE', $other_income_coa_code_prefiks.'%')
                                ->when(strtolower($lokal_input)=='p' || strtolower($lokal_input)=='a', function($q){
                                    $q->whereRaw('UPPER(local)=\'P\'');
                                })
                                ->where([
                                    'branch_id' => $xb->id,
                                    'is_master_coa' => 'N',
                                    'active' => 'Y',
                                ])
                                ->orderBy('coa_name', 'ASC')
                                ->orderBy('coa_code_complete', 'ASC')
                                ->get();                            
                            @endphp
                            @foreach ($qCoa8xP as $p)
                                <tr>
                                    <td>- {{ $p->coa_name }}</td>
                                    @if ($month_id==0)
                                        @for ($m=0;$m<count($months);$m++)
                                            @php
                                                $sumOtherIncomeGJdebit = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($m, $year_id){
                                                    $q->select('id')
                                                    ->from('tx_general_journals')
                                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($m+1)==1?'0'.($m+1):($m+1)).'\'')
                                                    ->where('is_draft', '=', 'N')
                                                    ->where('active', '=', 'Y');
                                                })
                                                ->where([
                                                    'coa_id' => $p->id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('debit');

                                                $sumOtherIncomeGJkredit = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($m, $year_id){
                                                    $q->select('id')
                                                    ->from('tx_general_journals')
                                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($m+1)==1?'0'.($m+1):($m+1)).'\'')
                                                    ->where('is_draft', '=', 'N')
                                                    ->where('active', '=', 'Y');
                                                })
                                                ->where([
                                                    'coa_id' => $p->id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('kredit');

                                                $sumOtherIncomeGJ=$sumOtherIncomeGJdebit-$sumOtherIncomeGJkredit;
                                                $OpeOtherIncomePerCols[$m]+=$sumOtherIncomeGJ;
                                            @endphp
                                            <td>{{ number_format($sumOtherIncomeGJ,0,",","") }}</td>                                
                                        @endfor

                                        @php
                                            $sumTotalOtherIncomeGJdebit = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($year_id){
                                                $q->select('id')
                                                ->from('tx_general_journals')
                                                ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y\')=\''.$year_id.'\'')
                                                ->where('is_draft', '=', 'N')
                                                ->where('active', '=', 'Y');
                                            })
                                            ->where([
                                                'coa_id' => $p->id,
                                                'active' => 'Y',
                                            ])
                                            ->sum('debit');

                                            $sumTotalOtherIncomeGJkredit = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($year_id){
                                                $q->select('id')
                                                ->from('tx_general_journals')
                                                ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y\')=\''.$year_id.'\'')
                                                ->where('is_draft', '=', 'N')
                                                ->where('active', '=', 'Y');
                                            })
                                            ->where([
                                                'coa_id' => $p->id,
                                                'active' => 'Y',
                                            ])
                                            ->sum('kredit');

                                            $sumTotalOtherIncomeGJ=$sumTotalOtherIncomeGJdebit-$sumTotalOtherIncomeGJkredit;
                                            $TotalOpeOtherIncome+=$sumTotalOtherIncomeGJ;
                                        @endphp
                                        <td>{{ number_format($sumTotalOtherIncomeGJ,0,",","") }}</td> 
                                    @else
                                        @php
                                            $sumOtherIncomeGJdebit = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($month_id, $year_id){
                                                $q->select('id')
                                                ->from('tx_general_journals')
                                                ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($month_id)==1?'0'.$month_id:$month_id).'\'')
                                                ->where('is_draft', '=', 'N')
                                                ->where('active', '=', 'Y');
                                            })
                                            ->where([
                                                'coa_id' => $p->id,
                                                'active' => 'Y',
                                            ])
                                            ->sum('debit');

                                            $sumOtherIncomeGJkredit = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($month_id, $year_id){
                                                $q->select('id')
                                                ->from('tx_general_journals')
                                                ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($month_id)==1?'0'.$month_id:$month_id).'\'')
                                                ->where('is_draft', '=', 'N')
                                                ->where('active', '=', 'Y');
                                            })
                                            ->where([
                                                'coa_id' => $p->id,
                                                'active' => 'Y',
                                            ])
                                            ->sum('kredit');

                                            $sumOtherIncomeGJ=$sumOtherIncomeGJdebit-$sumOtherIncomeGJkredit;
                                            $OpeOtherIncomePerCols[$month_id-1]+=$sumOtherIncomeGJ;
                                        @endphp
                                        <td>{{ number_format($sumOtherIncomeGJ,0,",","") }}</td>

                                        @php
                                            $sumOtherIncomeGJtotal = 0;                          
                                        @endphp
                                        @for ($iMonth=1;$iMonth<=$month_id;$iMonth++)
                                            @php                                                
                                                $sumOtherIncomeGJtotalPerMonthdebit = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($iMonth, $year_id){
                                                    $q->select('id')
                                                    ->from('tx_general_journals')
                                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($iMonth)==1?'0'.$iMonth:$iMonth).'\'')
                                                    ->where('is_draft', '=', 'N')
                                                    ->where('active', '=', 'Y');
                                                })
                                                ->where([
                                                    'coa_id' => $p->id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('debit');

                                                $sumOtherIncomeGJtotalPerMonthkredit = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($iMonth, $year_id){
                                                    $q->select('id')
                                                    ->from('tx_general_journals')
                                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($iMonth)==1?'0'.$iMonth:$iMonth).'\'')
                                                    ->where('is_draft', '=', 'N')
                                                    ->where('active', '=', 'Y');
                                                })
                                                ->where([
                                                    'coa_id' => $p->id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('kredit');

                                                $sumOtherIncomeGJtotalPerMonth=$sumOtherIncomeGJtotalPerMonthdebit-$sumOtherIncomeGJtotalPerMonthkredit;
                                                $sumOtherIncomeGJtotal+=$sumOtherIncomeGJtotalPerMonth;
                                            @endphp
                                        @endfor
                                        @php
                                            $TotalOpeOtherIncome+=$sumOtherIncomeGJtotal;
                                        @endphp
                                        <td>{{ number_format($sumOtherIncomeGJtotal,0,",","") }}</td>
                                    @endif 
                                </tr>
                            @endforeach
                        @endif
                        @if (strtolower($lokal_input)=='n' || strtolower($lokal_input)=='a')
                            {{-- <tr>
                                <td>EXPENSE {{ $xb->initial }}-NP</td>
                            </tr> --}}
                            @php
                                $qCoa8xN = \App\Models\Mst_coa::where('coa_code_complete', 'LIKE', $other_income_coa_code_prefiks.'%')
                                ->when(strtolower($lokal_input)=='n' || strtolower($lokal_input)=='a', function($q){
                                    $q->whereRaw('UPPER(local)=\'N\'');
                                })
                                ->where([
                                    'branch_id' => $xb->id,
                                    'is_master_coa' => 'N',
                                    'active' => 'Y',
                                ])
                                ->orderBy('coa_name', 'ASC')
                                ->orderBy('coa_code_complete', 'ASC')
                                ->get();                            
                            @endphp
                            @foreach ($qCoa8xN as $n)
                                <tr>
                                    <td>- {{ $n->coa_name }}</td>
                                    @if ($month_id==0)
                                        @for ($m=0;$m<count($months);$m++)
                                            @php
                                                $sumOtherIncomeLJdebit = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($m, $year_id){
                                                    $q->select('id')
                                                    ->from('tx_lokal_journals')
                                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($m+1)==1?'0'.($m+1):($m+1)).'\'')
                                                    ->where('is_draft', '=', 'N')
                                                    ->where('active', '=', 'Y');
                                                })
                                                ->where([
                                                    'coa_id' => $n->id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('debit');

                                                $sumOtherIncomeLJkredit = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($m, $year_id){
                                                    $q->select('id')
                                                    ->from('tx_lokal_journals')
                                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($m+1)==1?'0'.($m+1):($m+1)).'\'')
                                                    ->where('is_draft', '=', 'N')
                                                    ->where('active', '=', 'Y');
                                                })
                                                ->where([
                                                    'coa_id' => $n->id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('kredit');

                                                $sumOtherIncomeLJ=$sumOtherIncomeLJdebit-$sumOtherIncomeLJkredit;
                                                $OpeOtherIncomePerCols[$m]+=$sumOtherIncomeLJ;
                                            @endphp
                                            <td>{{ number_format($sumOtherIncomeLJ,0,",","") }}</td>                                
                                        @endfor

                                        @php
                                            $sumTotalOtherIncomeLJdebit = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($year_id){
                                                $q->select('id')
                                                ->from('tx_lokal_journals')
                                                ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y\')=\''.$year_id.'\'')
                                                ->where('is_draft', '=', 'N')
                                                ->where('active', '=', 'Y');
                                            })
                                            ->where([
                                                'coa_id' => $n->id,
                                                'active' => 'Y',
                                            ])
                                            ->sum('debit');

                                            $sumTotalOtherIncomeLJkredit = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($year_id){
                                                $q->select('id')
                                                ->from('tx_lokal_journals')
                                                ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y\')=\''.$year_id.'\'')
                                                ->where('is_draft', '=', 'N')
                                                ->where('active', '=', 'Y');
                                            })
                                            ->where([
                                                'coa_id' => $n->id,
                                                'active' => 'Y',
                                            ])
                                            ->sum('kredit');

                                            $sumTotalOtherIncomeLJ=$sumTotalOtherIncomeLJdebit-$sumTotalOtherIncomeLJkredit;
                                            $TotalOpeOtherIncome+=$sumTotalOtherIncomeLJ;
                                        @endphp
                                        <td>{{ number_format($sumTotalOtherIncomeLJ,0,",","") }}</td>
                                    @else
                                        @php
                                            $sumOtherIncomeLJdebit = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($month_id, $year_id){
                                                $q->select('id')
                                                ->from('tx_lokal_journals')
                                                ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($month_id)==1?'0'.$month_id:$month_id).'\'')
                                                ->where('is_draft', '=', 'N')
                                                ->where('active', '=', 'Y');
                                            })
                                            ->where([
                                                'coa_id' => $n->id,
                                                'active' => 'Y',
                                            ])
                                            ->sum('debit');

                                            $sumOtherIncomeLJkredit = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($month_id, $year_id){
                                                $q->select('id')
                                                ->from('tx_lokal_journals')
                                                ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($month_id)==1?'0'.$month_id:$month_id).'\'')
                                                ->where('is_draft', '=', 'N')
                                                ->where('active', '=', 'Y');
                                            })
                                            ->where([
                                                'coa_id' => $n->id,
                                                'active' => 'Y',
                                            ])
                                            ->sum('kredit');

                                            $sumOtherIncomeLJ=$sumOtherIncomeLJdebit-$sumOtherIncomeLJkredit;
                                            $OpeOtherIncomePerCols[$month_id-1]+=$sumOtherIncomeLJ;
                                        @endphp
                                        <td>{{ number_format($sumOtherIncomeLJ,0,",","") }}</td>

                                        @php
                                            $sumOtherIncomeLJtotal = 0;                          
                                        @endphp
                                        @for ($iMonth=1;$iMonth<=$month_id;$iMonth++)
                                            @php                                                
                                                $sumOtherIncomeLJtotalPerMonthdebit = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($iMonth, $year_id){
                                                    $q->select('id')
                                                    ->from('tx_lokal_journals')
                                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($iMonth)==1?'0'.$iMonth:$iMonth).'\'')
                                                    ->where('is_draft', '=', 'N')
                                                    ->where('active', '=', 'Y');
                                                })
                                                ->where([
                                                    'coa_id' => $n->id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('debit');

                                                $sumOtherIncomeLJtotalPerMonthkredit = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($iMonth, $year_id){
                                                    $q->select('id')
                                                    ->from('tx_lokal_journals')
                                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($iMonth)==1?'0'.$iMonth:$iMonth).'\'')
                                                    ->where('is_draft', '=', 'N')
                                                    ->where('active', '=', 'Y');
                                                })
                                                ->where([
                                                    'coa_id' => $n->id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('kredit');

                                                $sumOtherIncomeLJtotalPerMonth=$sumOtherIncomeLJtotalPerMonthdebit-$sumOtherIncomeLJtotalPerMonthkredit;
                                                $sumOtherIncomeLJtotal+=$sumOtherIncomeLJtotalPerMonth;
                                            @endphp
                                        @endfor
                                        @php
                                            $TotalOpeOtherIncome+=$sumOtherIncomeLJtotal;
                                        @endphp
                                        <td>{{ number_format($sumOtherIncomeLJtotal,0,",","") }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                    {{-- other income --}}

                    {{-- blank row --}}
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    {{-- blank row --}}
                    
                    
                    {{-- other expense --}}
                    <tr>
                        <td style="font-weight: 700;">OTHER EXPENSE</td>
                    </tr>
                    @php
                        $ExpBranches = \App\Models\Mst_branch::when($branch_id!=0, function($q) use($branch_id){
                            $q->where('id', '=', $branch_id);
                        })
                        ->where('active', '=', 'Y')
                        ->orderBy('initial', 'ASC')
                        ->get();
                    @endphp
                    @foreach ($ExpBranches as $xb)                        
                        @if (strtolower($lokal_input)=='p' || strtolower($lokal_input)=='a')
                            @php
                                $qCoa9xP = \App\Models\Mst_coa::where('coa_code_complete', 'LIKE', $other_expense_coa_code_prefiks.'%')
                                ->when(strtolower($lokal_input)=='p' || strtolower($lokal_input)=='a', function($q){
                                    $q->whereRaw('UPPER(local)=\'P\'');
                                })
                                ->where([
                                    'branch_id' => $xb->id,
                                    'is_master_coa' => 'N',
                                    'active' => 'Y',
                                ])
                                ->orderBy('coa_name', 'ASC')
                                ->orderBy('coa_code_complete', 'ASC')
                                ->get();                            
                            @endphp
                            @foreach ($qCoa9xP as $p)
                                <tr>
                                    <td>- {{ $p->coa_name }}</td>
                                    @if ($month_id==0)
                                        @for ($m=0;$m<count($months);$m++)
                                            @php
                                                $sumOtherExpenseGJdebit = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($m, $year_id){
                                                    $q->select('id')
                                                    ->from('tx_general_journals')
                                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($m+1)==1?'0'.($m+1):($m+1)).'\'')
                                                    ->where('is_draft', '=', 'N')
                                                    ->where('active', '=', 'Y');
                                                })
                                                ->where([
                                                    'coa_id' => $p->id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('debit');

                                                $sumOtherExpenseGJkredit = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($m, $year_id){
                                                    $q->select('id')
                                                    ->from('tx_general_journals')
                                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($m+1)==1?'0'.($m+1):($m+1)).'\'')
                                                    ->where('is_draft', '=', 'N')
                                                    ->where('active', '=', 'Y');
                                                })
                                                ->where([
                                                    'coa_id' => $p->id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('kredit');

                                                $sumOtherExpenseGJ=$sumOtherExpenseGJdebit-$sumOtherExpenseGJkredit;
                                                $OpeOtherExpensePerCols[$m]+=$sumOtherExpenseGJ;
                                            @endphp
                                            <td>{{ number_format($sumOtherExpenseGJ,0,",","") }}</td>                                
                                        @endfor

                                        @php
                                            $sumTotalOtherExpenseGJdebit = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($year_id){
                                                $q->select('id')
                                                ->from('tx_general_journals')
                                                ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y\')=\''.$year_id.'\'')
                                                ->where('is_draft', '=', 'N')
                                                ->where('active', '=', 'Y');
                                            })
                                            ->where([
                                                'coa_id' => $p->id,
                                                'active' => 'Y',
                                            ])
                                            ->sum('debit');

                                            $sumTotalOtherExpenseGJkredit = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($year_id){
                                                $q->select('id')
                                                ->from('tx_general_journals')
                                                ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y\')=\''.$year_id.'\'')
                                                ->where('is_draft', '=', 'N')
                                                ->where('active', '=', 'Y');
                                            })
                                            ->where([
                                                'coa_id' => $p->id,
                                                'active' => 'Y',
                                            ])
                                            ->sum('kredit');

                                            $sumTotalOtherExpenseGJ=$sumTotalOtherExpenseGJdebit-$sumTotalOtherExpenseGJkredit;
                                            $TotalOpeOtherExpense+=$sumTotalOtherExpenseGJ;
                                        @endphp
                                        <td>{{ number_format($sumTotalOtherExpenseGJ,0,",","") }}</td> 
                                    @else
                                        @php
                                            $sumOtherExpenseGJdebit = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($month_id, $year_id){
                                                $q->select('id')
                                                ->from('tx_general_journals')
                                                ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($month_id)==1?'0'.$month_id:$month_id).'\'')
                                                ->where('is_draft', '=', 'N')
                                                ->where('active', '=', 'Y');
                                            })
                                            ->where([
                                                'coa_id' => $p->id,
                                                'active' => 'Y',
                                            ])
                                            ->sum('debit');

                                            $sumOtherExpenseGJkredit = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($month_id, $year_id){
                                                $q->select('id')
                                                ->from('tx_general_journals')
                                                ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($month_id)==1?'0'.$month_id:$month_id).'\'')
                                                ->where('is_draft', '=', 'N')
                                                ->where('active', '=', 'Y');
                                            })
                                            ->where([
                                                'coa_id' => $p->id,
                                                'active' => 'Y',
                                            ])
                                            ->sum('kredit');

                                            $sumOtherExpenseGJ=$sumOtherExpenseGJdebit-$sumOtherExpenseGJkredit;
                                            $OpeOtherExpensePerCols[$month_id-1]+=$sumOtherExpenseGJ;
                                        @endphp
                                        <td>{{ number_format($sumOtherExpenseGJ,0,",","") }}</td>

                                        @php
                                            $sumOtherExpenseGJtotal = 0;                          
                                        @endphp
                                        @for ($iMonth=1;$iMonth<=$month_id;$iMonth++)
                                            @php                                                
                                                $sumOtherExpenseGJtotalPerMonthdebit = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($iMonth, $year_id){
                                                    $q->select('id')
                                                    ->from('tx_general_journals')
                                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($iMonth)==1?'0'.$iMonth:$iMonth).'\'')
                                                    ->where('is_draft', '=', 'N')
                                                    ->where('active', '=', 'Y');
                                                })
                                                ->where([
                                                    'coa_id' => $p->id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('debit');

                                                $sumOtherExpenseGJtotalPerMonthkredit = \App\Models\Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($iMonth, $year_id){
                                                    $q->select('id')
                                                    ->from('tx_general_journals')
                                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($iMonth)==1?'0'.$iMonth:$iMonth).'\'')
                                                    ->where('is_draft', '=', 'N')
                                                    ->where('active', '=', 'Y');
                                                })
                                                ->where([
                                                    'coa_id' => $p->id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('kredit');

                                                $sumOtherExpenseGJtotalPerMonth=$sumOtherExpenseGJtotalPerMonthdebit-$sumOtherExpenseGJtotalPerMonthkredit;
                                                $sumOtherExpenseGJtotal+=$sumOtherExpenseGJtotalPerMonth;
                                            @endphp
                                        @endfor
                                        @php
                                            $TotalOpeOtherExpense+=$sumOtherExpenseGJtotal;
                                        @endphp
                                        <td>{{ number_format($sumOtherExpenseGJtotal,0,",","") }}</td>
                                    @endif 
                                </tr>
                            @endforeach
                        @endif
                        @if (strtolower($lokal_input)=='n' || strtolower($lokal_input)=='a')
                            @php
                                $qCoa9xN = \App\Models\Mst_coa::where('coa_code_complete', 'LIKE', $other_expense_coa_code_prefiks.'%')
                                ->when(strtolower($lokal_input)=='n' || strtolower($lokal_input)=='a', function($q){
                                    $q->whereRaw('UPPER(local)=\'N\'');
                                })
                                ->where([
                                    'branch_id' => $xb->id,
                                    'is_master_coa' => 'N',
                                    'active' => 'Y',
                                ])
                                ->orderBy('coa_name', 'ASC')
                                ->orderBy('coa_code_complete', 'ASC')
                                ->get();                            
                            @endphp
                            @foreach ($qCoa9xN as $n)
                                <tr>
                                    <td>- {{ $n->coa_name }}</td>
                                    @if ($month_id==0)
                                        @for ($m=0;$m<count($months);$m++)
                                            @php
                                                $sumOtherExpenseLJdebit = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($m, $year_id){
                                                    $q->select('id')
                                                    ->from('tx_lokal_journals')
                                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($m+1)==1?'0'.($m+1):($m+1)).'\'')
                                                    ->where('is_draft', '=', 'N')
                                                    ->where('active', '=', 'Y');
                                                })
                                                ->where([
                                                    'coa_id' => $n->id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('debit');

                                                $sumOtherExpenseLJkredit = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($m, $year_id){
                                                    $q->select('id')
                                                    ->from('tx_lokal_journals')
                                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($m+1)==1?'0'.($m+1):($m+1)).'\'')
                                                    ->where('is_draft', '=', 'N')
                                                    ->where('active', '=', 'Y');
                                                })
                                                ->where([
                                                    'coa_id' => $n->id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('kredit');

                                                $sumOtherExpenseLJ=$sumOtherExpenseLJdebit-$sumOtherExpenseLJkredit;
                                                $OpeOtherExpensePerCols[$m]+=$sumOtherExpenseLJ;
                                            @endphp
                                            <td>{{ number_format($sumOtherExpenseLJ,0,",","") }}</td>                                
                                        @endfor

                                        @php
                                            $sumTotalOtherExpenseLJdebit = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($year_id){
                                                $q->select('id')
                                                ->from('tx_lokal_journals')
                                                ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y\')=\''.$year_id.'\'')
                                                ->where('is_draft', '=', 'N')
                                                ->where('active', '=', 'Y');
                                            })
                                            ->where([
                                                'coa_id' => $n->id,
                                                'active' => 'Y',
                                            ])
                                            ->sum('debit');

                                            $sumTotalOtherExpenseLJkredit = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($year_id){
                                                $q->select('id')
                                                ->from('tx_lokal_journals')
                                                ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y\')=\''.$year_id.'\'')
                                                ->where('is_draft', '=', 'N')
                                                ->where('active', '=', 'Y');
                                            })
                                            ->where([
                                                'coa_id' => $n->id,
                                                'active' => 'Y',
                                            ])
                                            ->sum('kredit');

                                            $sumTotalOtherExpenseLJ=$sumTotalOtherExpenseLJdebit-$sumTotalOtherExpenseLJkredit;
                                            $TotalOpeOtherExpense+=$sumTotalOtherExpenseLJ;
                                        @endphp
                                        <td>{{ number_format($sumTotalOtherExpenseLJ,0,",","") }}</td>
                                    @else
                                        @php
                                            $sumOtherExpenseLJdebit = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($month_id, $year_id){
                                                $q->select('id')
                                                ->from('tx_lokal_journals')
                                                ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($month_id)==1?'0'.$month_id:$month_id).'\'')
                                                ->where('is_draft', '=', 'N')
                                                ->where('active', '=', 'Y');
                                            })
                                            ->where([
                                                'coa_id' => $n->id,
                                                'active' => 'Y',
                                            ])
                                            ->sum('debit');

                                            $sumOtherExpenseLJkredit = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($month_id, $year_id){
                                                $q->select('id')
                                                ->from('tx_lokal_journals')
                                                ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($month_id)==1?'0'.$month_id:$month_id).'\'')
                                                ->where('is_draft', '=', 'N')
                                                ->where('active', '=', 'Y');
                                            })
                                            ->where([
                                                'coa_id' => $n->id,
                                                'active' => 'Y',
                                            ])
                                            ->sum('kredit');

                                            $sumOtherExpenseLJ=$sumOtherExpenseLJdebit-$sumOtherExpenseLJkredit;
                                            $OpeOtherExpensePerCols[$month_id-1]+=$sumOtherExpenseLJ;
                                        @endphp
                                        <td>{{ number_format($sumOtherExpenseLJ,0,",","") }}</td>

                                        @php
                                            $sumOtherExpenseLJtotal = 0;                          
                                        @endphp
                                        @for ($iMonth=1;$iMonth<=$month_id;$iMonth++)
                                            @php                                                
                                                $sumOtherExpenseLJtotalPerMonthdebit = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($iMonth, $year_id){
                                                    $q->select('id')
                                                    ->from('tx_lokal_journals')
                                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($iMonth)==1?'0'.$iMonth:$iMonth).'\'')
                                                    ->where('is_draft', '=', 'N')
                                                    ->where('active', '=', 'Y');
                                                })
                                                ->where([
                                                    'coa_id' => $n->id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('debit');

                                                $sumOtherExpenseLJtotalPerMonthkredit = \App\Models\Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($iMonth, $year_id){
                                                    $q->select('id')
                                                    ->from('tx_lokal_journals')
                                                    ->whereRaw('DATE_FORMAT(general_journal_date, \'%Y-%m\')=\''.$year_id.'-'.(strlen($iMonth)==1?'0'.$iMonth:$iMonth).'\'')
                                                    ->where('is_draft', '=', 'N')
                                                    ->where('active', '=', 'Y');
                                                })
                                                ->where([
                                                    'coa_id' => $n->id,
                                                    'active' => 'Y',
                                                ])
                                                ->sum('kredit');

                                                $sumOtherExpenseLJtotalPerMonth=$sumOtherExpenseLJtotalPerMonthdebit-$sumOtherExpenseLJtotalPerMonthkredit;
                                                $sumOtherExpenseLJtotal+=$sumOtherExpenseLJtotalPerMonth;
                                            @endphp
                                        @endfor
                                        @php
                                            $TotalOpeOtherExpense+=$sumOtherExpenseLJtotal;
                                        @endphp
                                        <td>{{ number_format($sumOtherExpenseLJtotal,0,",","") }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                    {{-- other expense --}}

                    <tr>
                        <td style="font-weight: 700;border-top:1px solid black;">TOTAL OTHER INCOME</td>
                        @if ($month_id==0)
                            @for ($m=0;$m<count($months);$m++)
                                <td style="border-top:1px solid black;">
                                    {{ number_format($OpeOtherIncomePerCols[$m]-$OpeOtherExpensePerCols[$m],0,",","") }}
                                </td>
                            @endfor
                        @else
                            <td style="border-top:1px solid black;">
                                {{ number_format($OpeOtherIncomePerCols[$month_id-1]-$OpeOtherExpensePerCols[$month_id-1],0,",","") }}
                            </td>
                        @endif
                        <td style="border-top:1px solid black;">
                            {{ number_format($TotalOpeOtherIncome-$TotalOpeOtherExpense,0,",","") }}
                        </td>
                    </tr>

                    {{-- blank row --}}
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    {{-- blank row --}}

                    <tr>
                        <td style="font-weight: 700;">NETTO PROFIT BEFORE TAX</td>
                        @if ($month_id==0)
                            @for ($m=0;$m<count($months);$m++)
                                <td style="text-align:right;">
                                    {{ number_format($GrossProfitPerCols[$m]-$OpeExpensePerCols[$m]+($OpeOtherIncomePerCols[$m]-$OpeOtherExpensePerCols[$m]),0,",","") }}
                                </td>
                            @endfor
                        @else
                            <td style="text-align:right;">
                                {{ number_format($GrossProfitPerCols[$month_id-1]-$OpeExpensePerCols[$month_id-1]+($OpeOtherIncomePerCols[$month_id-1]-$OpeOtherExpensePerCols[$month_id-1]),0,",","") }}
                            </td>
                        @endif
                        <td style="text-align:right;">
                            {{ number_format($TotalGrossProfit-$TotalOpeExpense+($TotalOpeOtherIncome-$TotalOpeOtherExpense),0,",","") }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700;">&nbsp;</td>
                        @if ($month_id==0)
                            @for ($m=0;$m<count($months);$m++)
                                <td style="text-align:right;">
                                    {{ $NettoSalesPerCols[$m]!=0?number_format((($GrossProfitPerCols[$m]-$OpeExpensePerCols[$m]+($OpeOtherIncomePerCols[$m]-$OpeOtherExpensePerCols[$m]))/$NettoSalesPerCols[$m])*100,2,",",""):0 }}
                                </td>
                            @endfor
                        @else
                            <td style="text-align:right;">
                                {{ $NettoSalesPerCols[$month_id-1]!=0?number_format((($GrossProfitPerCols[$month_id-1]-$OpeExpensePerCols[$month_id-1]+($OpeOtherIncomePerCols[$month_id-1]-$OpeOtherExpensePerCols[$month_id-1]))/$NettoSalesPerCols[$month_id-1])*100,2,",",""):0 }}
                            </td>
                        @endif
                        <td style="text-align:right;">
                            {{ $TotalNettoSales!=0?number_format((($TotalGrossProfit-$TotalOpeExpense+($TotalOpeOtherIncome-$TotalOpeOtherExpense))/$TotalNettoSales)/100,2,",",""):0 }}
                        </td>
                    </tr>

                    
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
