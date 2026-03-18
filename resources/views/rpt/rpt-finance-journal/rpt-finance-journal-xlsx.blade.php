<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>FinanceJournal</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $qJournal = [];
                    $qJournalDtl = [];
                @endphp
                @if (strpos('J-'.$journal_no,env('P_GENERAL_JURNAL'))>0)
                    @php
                        $qJournal = \App\Models\Tx_general_journal::where([
                            'general_journal_no'=>$journal_no,
                        ])
                        ->first();
                    @endphp
                    @if ($qJournal)
                        @php
                            $qJournalDtl = \App\Models\Tx_general_journal_detail::where([
                                'general_journal_id'=>$qJournal->id,
                            ])
                            ->get();
                        @endphp
                    @endif
                @endif
                @if (strpos('J-'.$journal_no,env('P_LOKAL_JURNAL'))>0)
                    @php
                        $qJournal = \App\Models\Tx_lokal_journal::where([
                            'general_journal_no'=>$journal_no,
                        ])
                        ->first();
                    @endphp
                    @if ($qJournal)
                        @php
                            $qJournalDtl = \App\Models\Tx_lokal_journal_detail::where([
                                'lokal_journal_id'=>$qJournal->id,
                            ])
                            ->get();
                        @endphp
                    @endif
                @endif
                @if ($qJournal)
                <thead>
                    <tr>
                        <th colspan="5">
                            {!! ($qCompany?$qCompany->name:'') !!}
                        </th>
                    </tr>
                    <tr>
                        <th colspan="5">&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="5">JOURNAL</th>
                    </tr>
                    <tr>
                        <td>JOURNAL NO</td>
                        <td>{!! $journal_no !!}</td>
                        <td colspan="5">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>DATE</td>
                        <td>{{ date_format(date_create($qJournal->general_journal_date),"d-M-Y") }}</td>
                        <td colspan="5">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="7">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="text-align: center;border:1px solid black;background-color:#ff6600;">NO</td>
                        <td style="text-align: center;border:1px solid black;background-color:#ff6600;">COA NO</td>
                        <td style="text-align: center;border:1px solid black;background-color:#ff6600;">COA NAME</td>
                        <td style="text-align: center;border:1px solid black;background-color:#ff6600;">DESCRIPTION</td>
                        <td style="text-align: center;border:1px solid black;background-color:#ff6600;">DEBIT</td>
                        <td style="text-align: center;border:1px solid black;background-color:#ff6600;">CREDIT</td>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 1;
                    @endphp
                    @foreach ($qJournalDtl as $qJ)
                        <tr>
                            <td style="border:1px solid black;">{{ $i }}</td>
                            <td style="border:1px solid black;text-align:center;">{{ $qJ->coa->coa_code_complete }}</td>
                            <td style="border-left:1px solid black;">{{ $qJ->coa->coa_name }}</td>
                            <td style="border-left:1px solid black;">{{ $qJ->description }}</td>
                            <td style="border:1px solid black;text-align:right;">{{ number_format($qJ->debit,0,'.','') }}</td>
                            <td style="border-right:1px solid black;text-align:right;color:red;">{{ number_format($qJ->kredit,0,'.','') }}</td>
                        </tr>
                        @php
                            $i += 1;
                        @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td style="border-left:1px solid black;">&nbsp;</td>
                        <td style="border-right:1px solid black;">&nbsp;</td>
                        <td style="border-right:1px solid black;">&nbsp;</td>
                        <td style="border-right:1px solid black;">&nbsp;</td>
                        <td style="border-right:1px solid black;">&nbsp;</td>
                        <td style="border-right:1px solid black;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid black;">&nbsp;</td>
                        <td style="border:1px solid black;">&nbsp;</td>
                        <td style="border:1px solid black;">&nbsp;</td>
                        <td style="border:1px solid black;text-align:center;font-weight:700;">TOTAL</td>
                        <td style="border:1px solid black;text-align:right;font-weight:700;">{{ number_format($qJournal->total_debit,0,'.','') }}</td>
                        <td style="border:1px solid black;text-align:right;font-weight:700;">{{ number_format($qJournal->total_kredit,0,'.','') }}</td>
                    </tr>
                    <tr>
                        <td colspan="5">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="5">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="2">Disetujui oleh,</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td colspan="2">Dibuat oleh,</td>
                    </tr>
                    <tr>
                        <td colspan="5">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="5">&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border-bottom: 1px solid black;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td colspan="2" style="border-bottom: 1px solid black;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
