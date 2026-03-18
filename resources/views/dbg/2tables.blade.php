<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>BalanceSheet</title>
    </head>
    <body>
        @php
            $date = now();
            $totCols = 12;

            $timezoneNow = new DateTimeZone('Asia/Jakarta');
            $date_local_now = new DateTime();
            $date_local_now->setTimeZone($timezoneNow);
            $period_year = 2024;

            $totAktiva = 0;
            $totPasiva = 0;

            $leftColumn = [];

            $qCoas1_1 = \App\Models\Mst_coa::select(
                'id',
                'coa_name',
                'coa_parent',
                'coa_code_complete',
                'coa_level',
            )
            ->where([
                'coa_level'=>1,
                'coa_code_complete'=>'1',
                'is_balance_sheet'=>'Y',
                'active'=>'Y',
            ])
            ->orderBy('coa_name','ASC')
            ->first();

            $leftRow = 0;
        @endphp
        @if ($qCoas1_1)
            @php
                $leftColumn = [
                    $leftRow => [
                        'id'=>$qCoas1_1->id,
                        'coa_name'=>$qCoas1_1->coa_name,
                        'coa_level'=>$qCoas1_1->coa_level,
                        'value'=>'',
                    ],
                ];

                $qCoas1_2 = \App\Models\Mst_coa::select(
                    'id',
                    'coa_name',
                    'coa_parent',
                    'coa_code_complete',
                    'coa_level',
                )
                ->where([
                    'coa_level'=>2,
                    // 'coa_code_complete'=>'1',
                    'coa_parent'=>$qCoas1_1->id,
                    'is_balance_sheet'=>'Y',
                    'active'=>'Y',
                ])
                ->orderBy('coa_name','ASC')
                ->get();

                $i1_2 = 0;
            @endphp
            @foreach ($qCoas1_2 as $c1_2)
                @php
                    $leftRow += 1;
                    $leftColumnNew = [
                        $leftRow => [
                            'id'=>$c1_2->id,
                            'coa_name'=>$c1_2->coa_name,
                            'coa_level'=>$c1_2->coa_level,
                            'value'=>'',
                        ],
                    ];

                    $leftColumn = array_merge($leftColumn, $leftColumnNew);
                    $i1_2 += 1;

                    $qCoas1_3 = \App\Models\Mst_coa::select(
                        'id',
                        'coa_name',
                        'coa_parent',
                        'coa_code_complete',
                        'coa_level',
                    )
                    ->where([
                        'coa_level'=>3,
                        // 'coa_code_complete'=>'1',
                        'coa_parent'=>$c1_2->id,
                        'is_balance_sheet'=>'Y',
                        'active'=>'Y',
                    ])
                    ->orderBy('coa_name','ASC')
                    ->get();

                    $i1_3 = 0;
                @endphp
                @foreach ($qCoas1_3 as $c1_3)
                    @php
                        $sumGJ_1_3 = \App\Models\Tx_general_journal_detail::leftJoin('tx_general_journals as tx_gj','tx_general_journal_details.general_journal_id','=','tx_gj.id')
                        ->whereRaw('tx_gj.general_journal_no NOT LIKE \'%Draft%\'')
                        ->whereRaw('year(tx_gj.general_journal_date)='.$period_year)
                        ->where([
                            'tx_general_journal_details.coa_id'=>$c1_3->id,
                            'tx_general_journal_details.active'=>'Y',
                            'tx_gj.is_wt_for_appr'=>'N',
                            'tx_gj.active'=>'Y',
                        ]);
                        $sumLJ_1_3 = \App\Models\Tx_lokal_journal_detail::leftJoin('tx_lokal_journals as tx_lj','tx_lokal_journal_details.lokal_journal_id','=','tx_lj.id')
                        ->whereRaw('tx_lj.general_journal_no NOT LIKE \'%Draft%\'')
                        ->whereRaw('year(tx_lj.general_journal_date)='.$period_year)
                        ->where([
                            'tx_lokal_journal_details.coa_id'=>$c1_3->id,
                            'tx_lokal_journal_details.active'=>'Y',
                            'tx_lj.is_wt_for_appr'=>'N',
                            'tx_lj.active'=>'Y',
                        ]);
                        $sumValGJ_1_3 = $sumGJ_1_3->sum('debit')-$sumGJ_1_3->sum('kredit');
                        $sumValLJ_1_3 = $sumLJ_1_3->sum('debit')-$sumLJ_1_3->sum('kredit');

                        $leftRow += 1;
                        $leftColumnNew = [
                            $leftRow => [
                                'id'=>$c1_3->id,
                                'coa_name'=>$c1_3->coa_name,
                                'coa_level'=>$c1_3->coa_level,
                                'value'=>($sumValGJ_1_3+$sumValLJ_1_3),
                            ],
                        ];

                        $leftColumn = array_merge($leftColumn, $leftColumnNew);
                        $i1_3 += 1;

                        $qCoas1_4 = \App\Models\Mst_coa::select(
                            'id',
                            'coa_name',
                            'coa_parent',
                            'coa_code_complete',
                            'coa_level',
                        )
                        ->where([
                            'coa_level'=>4,
                            // 'coa_code_complete'=>'1',
                            'coa_parent'=>$c1_3->id,
                            'is_balance_sheet'=>'Y',
                            'active'=>'Y',
                        ])
                        ->orderBy('coa_name','ASC')
                        ->get();

                        $i1_4 = 0;
                    @endphp
                    @foreach ($qCoas1_4 as $c1_4)
                        @php
                            $sumGJ_1_4 = \App\Models\Tx_general_journal_detail::leftJoin('tx_general_journals as tx_gj','tx_general_journal_details.general_journal_id','=','tx_gj.id')
                            ->whereRaw('tx_gj.general_journal_no NOT LIKE \'%Draft%\'')
                            ->whereRaw('year(tx_gj.general_journal_date)='.$period_year)
                            ->where([
                                'tx_general_journal_details.coa_id'=>$c1_4->id,
                                'tx_general_journal_details.active'=>'Y',
                                'tx_gj.is_wt_for_appr'=>'N',
                                'tx_gj.active'=>'Y',
                            ]);
                            $sumLJ_1_4 = \App\Models\Tx_lokal_journal_detail::leftJoin('tx_lokal_journals as tx_lj','tx_lokal_journal_details.lokal_journal_id','=','tx_lj.id')
                            ->whereRaw('tx_lj.general_journal_no NOT LIKE \'%Draft%\'')
                            ->whereRaw('year(tx_lj.general_journal_date)='.$period_year)
                            ->where([
                                'tx_lokal_journal_details.coa_id'=>$c1_4->id,
                                'tx_lokal_journal_details.active'=>'Y',
                                'tx_lj.is_wt_for_appr'=>'N',
                                'tx_lj.active'=>'Y',
                            ]);
                            $sumValGJ_1_4 = $sumGJ_1_4->sum('debit')-$sumGJ_1_4->sum('kredit');
                            $sumValLJ_1_4 = $sumLJ_1_4->sum('debit')-$sumLJ_1_4->sum('kredit');

                            $leftRow += 1;
                            $leftColumnNew = [
                                $leftRow => [
                                    'id'=>$c1_4->id,
                                    'coa_name'=>$c1_4->coa_name,
                                    'coa_level'=>$c1_4->coa_level,
                                    'value'=>($sumValGJ_1_4+$sumValLJ_1_4),
                                ],
                            ];

                            $leftColumn = array_merge($leftColumn, $leftColumnNew);
                            $i1_4 += 1;

                            $qCoas1_5 = \App\Models\Mst_coa::select(
                                'id',
                                'coa_name',
                                'coa_parent',
                                'coa_code_complete',
                                'coa_level',
                            )
                            ->where([
                                'coa_level'=>5,
                                // 'coa_code_complete'=>'1',
                                'coa_parent'=>$c1_4->id,
                                'is_balance_sheet'=>'Y',
                                'active'=>'Y',
                            ])
                            ->orderBy('coa_name','ASC')
                            ->get();

                            $i1_5 = 0;
                        @endphp
                        @foreach ($qCoas1_5 as $c1_5)
                            @php
                                $sumGJ_1_5 = \App\Models\Tx_general_journal_detail::leftJoin('tx_general_journals as tx_gj','tx_general_journal_details.general_journal_id','=','tx_gj.id')
                                ->whereRaw('tx_gj.general_journal_no NOT LIKE \'%Draft%\'')
                                ->whereRaw('year(tx_gj.general_journal_date)='.$period_year)
                                ->where([
                                    'tx_general_journal_details.coa_id'=>$c1_5->id,
                                    'tx_general_journal_details.active'=>'Y',
                                    'tx_gj.is_wt_for_appr'=>'N',
                                    'tx_gj.active'=>'Y',
                                ]);
                                $sumLJ_1_5 = \App\Models\Tx_lokal_journal_detail::leftJoin('tx_lokal_journals as tx_lj','tx_lokal_journal_details.lokal_journal_id','=','tx_lj.id')
                                ->whereRaw('tx_lj.general_journal_no NOT LIKE \'%Draft%\'')
                                ->whereRaw('year(tx_lj.general_journal_date)='.$period_year)
                                ->where([
                                    'tx_lokal_journal_details.coa_id'=>$c1_5->id,
                                    'tx_lokal_journal_details.active'=>'Y',
                                    'tx_lj.is_wt_for_appr'=>'N',
                                    'tx_lj.active'=>'Y',
                                ]);
                                $sumValGJ_1_5 = $sumGJ_1_5->sum('debit')-$sumGJ_1_5->sum('kredit');
                                $sumValLJ_1_5 = $sumLJ_1_5->sum('debit')-$sumLJ_1_5->sum('kredit');

                                $leftRow += 1;
                                $leftColumnNew = [
                                    $leftRow => [
                                        'id'=>$c1_5->id,
                                        'coa_name'=>$c1_5->coa_name,
                                        'coa_level'=>$c1_5->coa_level,
                                        'value'=>($sumValGJ_1_5+$sumValLJ_1_5),
                                    ],
                                ];

                                $leftColumn = array_merge($leftColumn, $leftColumnNew);
                                $i1_5 += 1;
                            @endphp
                        @endforeach
                    @endforeach
                @endforeach
            @endforeach
        @endif
        @php
            $rightColumn = [];

            $qCoas2_1 = \App\Models\Mst_coa::select(
                'id',
                'coa_name',
                'coa_parent',
                'coa_code_complete',
                'coa_level',
            )
            ->where([
                'coa_level'=>1,
                'coa_code_complete'=>'2',
                'is_balance_sheet'=>'Y',
                'active'=>'Y',
            ])
            ->orderBy('coa_name','ASC')
            ->first();

            $rightRow = 0;
        @endphp
        @if ($qCoas2_1)
            @php
                $rightColumn = [
                    $rightRow => [
                        'id'=>$qCoas2_1->id,
                        'coa_name'=>$qCoas2_1->coa_name,
                        'coa_level'=>$qCoas2_1->coa_level,
                        'value'=>'',
                    ],
                ];

                $qCoas2_2 = \App\Models\Mst_coa::select(
                    'id',
                    'coa_name',
                    'coa_parent',
                    'coa_code_complete',
                    'coa_level',
                )
                ->where([
                    'coa_level'=>2,
                    // 'coa_code_complete'=>'1',
                    'coa_parent'=>$qCoas2_1->id,
                    'is_balance_sheet'=>'Y',
                    'active'=>'Y',
                ])
                ->orderBy('coa_name','ASC')
                ->get();

                $i2_2 = 0;
            @endphp
            @foreach ($qCoas2_2 as $c2_2)
                @php
                    $rightRow += 1;
                    $rightColumnNew = [
                        $rightRow => [
                            'id'=>$c2_2->id,
                            'coa_name'=>$c2_2->coa_name,
                            'coa_level'=>$c2_2->coa_level,
                            'value'=>'',
                        ],
                    ];
                    $rightColumn = array_merge($rightColumn, $rightColumnNew);
                    $i2_2 += 1;

                    $qCoas2_3 = \App\Models\Mst_coa::select(
                        'id',
                        'coa_name',
                        'coa_parent',
                        'coa_code_complete',
                        'coa_level',
                    )
                    ->where([
                        'coa_level'=>3,
                        // 'coa_code_complete'=>'1',
                        'coa_parent'=>$c2_2->id,
                        'is_balance_sheet'=>'Y',
                        'active'=>'Y',
                    ])
                    ->orderBy('coa_name','ASC')
                    ->get();

                    $i2_3 = 0;
                @endphp
                @foreach ($qCoas2_3 as $c2_3)
                    @php
                        $sumGJ_2_3 = \App\Models\Tx_general_journal_detail::leftJoin('tx_general_journals as tx_gj','tx_general_journal_details.general_journal_id','=','tx_gj.id')
                        ->whereRaw('tx_gj.general_journal_no NOT LIKE \'%Draft%\'')
                        ->whereRaw('year(tx_gj.general_journal_date)='.$period_year)
                        ->where([
                            'tx_general_journal_details.coa_id'=>$c2_3->id,
                            'tx_general_journal_details.active'=>'Y',
                            'tx_gj.is_wt_for_appr'=>'N',
                            'tx_gj.active'=>'Y',
                        ]);
                        $sumLJ_2_3 = \App\Models\Tx_lokal_journal_detail::leftJoin('tx_lokal_journals as tx_lj','tx_lokal_journal_details.lokal_journal_id','=','tx_lj.id')
                        ->whereRaw('tx_lj.general_journal_no NOT LIKE \'%Draft%\'')
                        ->whereRaw('year(tx_lj.general_journal_date)='.$period_year)
                        ->where([
                            'tx_lokal_journal_details.coa_id'=>$c2_3->id,
                            'tx_lokal_journal_details.active'=>'Y',
                            'tx_lj.is_wt_for_appr'=>'N',
                            'tx_lj.active'=>'Y',
                        ]);
                        $sumValGJ_2_3 = $sumGJ_2_3->sum('debit')-$sumGJ_2_3->sum('kredit');
                        $sumValLJ_2_3 = $sumLJ_2_3->sum('debit')-$sumLJ_2_3->sum('kredit');

                        $rightRow += 1;
                        $rightColumnNew = [
                            $rightRow => [
                                'id'=>$c2_3->id,
                                'coa_name'=>$c2_3->coa_name,
                                'coa_level'=>$c2_3->coa_level,
                                'value'=>($sumValGJ_2_3+$sumValLJ_2_3),
                            ],
                        ];
                        $rightColumn = array_merge($rightColumn, $rightColumnNew);
                        $i2_3 += 1;

                        $qCoas2_4 = \App\Models\Mst_coa::select(
                            'id',
                            'coa_name',
                            'coa_parent',
                            'coa_code_complete',
                            'coa_level',
                        )
                        ->where([
                            'coa_level'=>4,
                            // 'coa_code_complete'=>'1',
                            'coa_parent'=>$c2_3->id,
                            'is_balance_sheet'=>'Y',
                            'active'=>'Y',
                        ])
                        ->orderBy('coa_name','ASC')
                        ->get();

                        $i2_4 = 0;
                    @endphp
                    @foreach ($qCoas2_4 as $c2_4)
                        @php
                            $sumGJ_2_4 = \App\Models\Tx_general_journal_detail::leftJoin('tx_general_journals as tx_gj','tx_general_journal_details.general_journal_id','=','tx_gj.id')
                            ->whereRaw('tx_gj.general_journal_no NOT LIKE \'%Draft%\'')
                            ->whereRaw('year(tx_gj.general_journal_date)='.$period_year)
                            ->where([
                                'tx_general_journal_details.coa_id'=>$c2_4->id,
                                'tx_general_journal_details.active'=>'Y',
                                'tx_gj.is_wt_for_appr'=>'N',
                                'tx_gj.active'=>'Y',
                            ]);
                            $sumLJ_2_4 = \App\Models\Tx_lokal_journal_detail::leftJoin('tx_lokal_journals as tx_lj','tx_lokal_journal_details.lokal_journal_id','=','tx_lj.id')
                            ->whereRaw('tx_lj.general_journal_no NOT LIKE \'%Draft%\'')
                            ->whereRaw('year(tx_lj.general_journal_date)='.$period_year)
                            ->where([
                                'tx_lokal_journal_details.coa_id'=>$c2_4->id,
                                'tx_lokal_journal_details.active'=>'Y',
                                'tx_lj.is_wt_for_appr'=>'N',
                                'tx_lj.active'=>'Y',
                            ]);
                            $sumValGJ_2_4 = $sumGJ_2_4->sum('debit')-$sumGJ_2_4->sum('kredit');
                            $sumValLJ_2_4 = $sumLJ_2_4->sum('debit')-$sumLJ_2_4->sum('kredit');

                            $rightRow += 1;
                            $rightColumnNew = [
                                $rightRow => [
                                    'id'=>$c2_4->id,
                                    'coa_name'=>$c2_4->coa_name,
                                    'coa_level'=>$c2_4->coa_level,
                                    'value'=>($sumValGJ_2_4+$sumValLJ_2_4),
                                ],
                            ];
                            $rightColumn = array_merge($rightColumn, $rightColumnNew);
                            $i2_4 += 1;

                            $qCoas2_5 = \App\Models\Mst_coa::select(
                                'id',
                                'coa_name',
                                'coa_parent',
                                'coa_code_complete',
                                'coa_level',
                            )
                            ->where([
                                'coa_level'=>5,
                                // 'coa_code_complete'=>'1',
                                'coa_parent'=>$c2_4->id,
                                'is_balance_sheet'=>'Y',
                                'active'=>'Y',
                            ])
                            ->orderBy('coa_name','ASC')
                            ->get();

                            $i2_5 = 0;
                        @endphp
                        @foreach ($qCoas2_5 as $c2_5)
                            @php
                                $sumGJ_2_5 = \App\Models\Tx_general_journal_detail::leftJoin('tx_general_journals as tx_gj','tx_general_journal_details.general_journal_id','=','tx_gj.id')
                                ->whereRaw('tx_gj.general_journal_no NOT LIKE \'%Draft%\'')
                                ->whereRaw('year(tx_gj.general_journal_date)='.$period_year)
                                ->where([
                                    'tx_general_journal_details.coa_id'=>$c2_5->id,
                                    'tx_general_journal_details.active'=>'Y',
                                    'tx_gj.is_wt_for_appr'=>'N',
                                    'tx_gj.active'=>'Y',
                                ]);
                                $sumLJ_2_5 = \App\Models\Tx_lokal_journal_detail::leftJoin('tx_lokal_journals as tx_lj','tx_lokal_journal_details.lokal_journal_id','=','tx_lj.id')
                                ->whereRaw('tx_lj.general_journal_no NOT LIKE \'%Draft%\'')
                                ->whereRaw('year(tx_lj.general_journal_date)='.$period_year)
                                ->where([
                                    'tx_lokal_journal_details.coa_id'=>$c2_5->id,
                                    'tx_lokal_journal_details.active'=>'Y',
                                    'tx_lj.is_wt_for_appr'=>'N',
                                    'tx_lj.active'=>'Y',
                                ]);
                                $sumValGJ_2_5 = $sumGJ_2_5->sum('debit')-$sumGJ_2_5->sum('kredit');
                                $sumValLJ_2_5 = $sumLJ_2_5->sum('debit')-$sumLJ_2_5->sum('kredit');

                                $rightRow += 1;
                                $rightColumnNew = [
                                    $rightRow => [
                                        'id'=>$c2_5->id,
                                        'coa_name'=>$c2_5->coa_name,
                                        'coa_level'=>$c2_5->coa_level,
                                        'value'=>($sumValGJ_2_5+$sumValLJ_2_5),
                                    ],
                                ];
                                $rightColumn = array_merge($rightColumn, $rightColumnNew);
                                $i2_5 += 1;
                            @endphp
                        @endforeach
                    @endforeach
                @endforeach
            @endforeach
        @endif
        @php
            $qCoas3_1 = \App\Models\Mst_coa::select(
                'id',
                'coa_name',
                'coa_parent',
                'coa_code_complete',
                'coa_level',
            )
            ->where([
                'coa_level'=>1,
                'coa_code_complete'=>'3',
                'is_balance_sheet'=>'Y',
                'active'=>'Y',
            ])
            ->orderBy('coa_name','ASC')
            ->first();
        @endphp
        @if ($qCoas3_1)
            @php
                $rightRow += 1;
                $rightColumnNew = [
                    $rightRow => [
                        'id'=>$qCoas3_1->id,
                        'coa_name'=>$qCoas3_1->coa_name,
                        'coa_level'=>$qCoas3_1->coa_level,
                        'value'=>'',
                    ],
                ];
                $rightColumn = array_merge($rightColumn, $rightColumnNew);

                $qCoas3_2 = \App\Models\Mst_coa::select(
                    'id',
                    'coa_name',
                    'coa_parent',
                    'coa_code_complete',
                    'coa_level',
                )
                ->where([
                    'coa_level'=>2,
                    // 'coa_code_complete'=>'1',
                    'coa_parent'=>$qCoas3_1->id,
                    'is_balance_sheet'=>'Y',
                    'active'=>'Y',
                ])
                ->orderBy('coa_name','ASC')
                ->get();

                $i3_2 = 0;
            @endphp
            @foreach ($qCoas3_2 as $c3_2)
                @php
                    $rightRow += 1;
                    $rightColumnNew = [
                        $rightRow => [
                            'id'=>$c3_2->id,
                            'coa_name'=>$c3_2->coa_name,
                            'coa_level'=>$c3_2->coa_level,
                            'value'=>'',
                        ],
                    ];
                    $rightColumn = array_merge($rightColumn, $rightColumnNew);
                    $i3_2 += 1;

                    $qCoas3_3 = \App\Models\Mst_coa::select(
                        'id',
                        'coa_name',
                        'coa_parent',
                        'coa_code_complete',
                        'coa_level',
                    )
                    ->where([
                        'coa_level'=>3,
                        // 'coa_code_complete'=>'1',
                        'coa_parent'=>$c3_2->id,
                        'is_balance_sheet'=>'Y',
                        'active'=>'Y',
                    ])
                    ->orderBy('coa_name','ASC')
                    ->get();

                    $i3_3 = 0;
                @endphp
                @foreach ($qCoas3_3 as $c3_3)
                    @php
                        $sumGJ_3_3 = \App\Models\Tx_general_journal_detail::leftJoin('tx_general_journals as tx_gj','tx_general_journal_details.general_journal_id','=','tx_gj.id')
                        ->whereRaw('tx_gj.general_journal_no NOT LIKE \'%Draft%\'')
                        ->whereRaw('year(tx_gj.general_journal_date)='.$period_year)
                        ->where([
                            'tx_general_journal_details.coa_id'=>$c3_3->id,
                            'tx_general_journal_details.active'=>'Y',
                            'tx_gj.is_wt_for_appr'=>'N',
                            'tx_gj.active'=>'Y',
                        ]);
                        $sumLJ_3_3 = \App\Models\Tx_lokal_journal_detail::leftJoin('tx_lokal_journals as tx_lj','tx_lokal_journal_details.lokal_journal_id','=','tx_lj.id')
                        ->whereRaw('tx_lj.general_journal_no NOT LIKE \'%Draft%\'')
                        ->whereRaw('year(tx_lj.general_journal_date)='.$period_year)
                        ->where([
                            'tx_lokal_journal_details.coa_id'=>$c3_3->id,
                            'tx_lokal_journal_details.active'=>'Y',
                            'tx_lj.is_wt_for_appr'=>'N',
                            'tx_lj.active'=>'Y',
                        ]);
                        $sumValGJ_3_3 = $sumGJ_3_3->sum('debit')-$sumGJ_3_3->sum('kredit');
                        $sumValLJ_3_3 = $sumLJ_3_3->sum('debit')-$sumLJ_3_3->sum('kredit');

                        $rightRow += 1;
                        $rightColumnNew = [
                            $rightRow => [
                                'id'=>$c3_3->id,
                                'coa_name'=>$c3_3->coa_name,
                                'coa_level'=>$c3_3->coa_level,
                                'value'=>($sumValGJ_3_3+$sumValLJ_3_3),
                            ],
                        ];
                        $rightColumn = array_merge($rightColumn, $rightColumnNew);
                        $i3_3 += 1;

                        $qCoas3_4 = \App\Models\Mst_coa::select(
                            'id',
                            'coa_name',
                            'coa_parent',
                            'coa_code_complete',
                            'coa_level',
                        )
                        ->where([
                            'coa_level'=>4,
                            // 'coa_code_complete'=>'1',
                            'coa_parent'=>$c3_3->id,
                            'is_balance_sheet'=>'Y',
                            'active'=>'Y',
                        ])
                        ->orderBy('coa_name','ASC')
                        ->get();

                        $i3_4 = 0;
                    @endphp
                    @foreach ($qCoas3_4 as $c3_4)
                        @php
                            $sumGJ_3_4 = \App\Models\Tx_general_journal_detail::leftJoin('tx_general_journals as tx_gj','tx_general_journal_details.general_journal_id','=','tx_gj.id')
                            ->whereRaw('tx_gj.general_journal_no NOT LIKE \'%Draft%\'')
                            ->whereRaw('year(tx_gj.general_journal_date)='.$period_year)
                            ->where([
                                'tx_general_journal_details.coa_id'=>$c3_4->id,
                                'tx_general_journal_details.active'=>'Y',
                                'tx_gj.is_wt_for_appr'=>'N',
                                'tx_gj.active'=>'Y',
                            ]);
                            $sumLJ_3_4 = \App\Models\Tx_lokal_journal_detail::leftJoin('tx_lokal_journals as tx_lj','tx_lokal_journal_details.lokal_journal_id','=','tx_lj.id')
                            ->whereRaw('tx_lj.general_journal_no NOT LIKE \'%Draft%\'')
                            ->whereRaw('year(tx_lj.general_journal_date)='.$period_year)
                            ->where([
                                'tx_lokal_journal_details.coa_id'=>$c3_4->id,
                                'tx_lokal_journal_details.active'=>'Y',
                                'tx_lj.is_wt_for_appr'=>'N',
                                'tx_lj.active'=>'Y',
                            ]);
                            $sumValGJ_3_4 = $sumGJ_3_4->sum('debit')-$sumGJ_3_4->sum('kredit');
                            $sumValLJ_3_4 = $sumLJ_3_4->sum('debit')-$sumLJ_3_4->sum('kredit');

                            $rightRow += 1;
                            $rightColumnNew = [
                                $rightRow => [
                                    'id'=>$c3_4->id,
                                    'coa_name'=>$c3_4->coa_name,
                                    'coa_level'=>$c3_4->coa_level,
                                    'value'=>($sumValGJ_3_4+$sumValLJ_3_4),
                                ],
                            ];
                            $rightColumn = array_merge($rightColumn, $rightColumnNew);
                            $i3_4 += 1;

                            $qCoas3_5 = \App\Models\Mst_coa::select(
                                'id',
                                'coa_name',
                                'coa_parent',
                                'coa_code_complete',
                                'coa_level',
                            )
                            ->where([
                                'coa_level'=>5,
                                // 'coa_code_complete'=>'1',
                                'coa_parent'=>$c3_4->id,
                                'is_balance_sheet'=>'Y',
                                'active'=>'Y',
                            ])
                            ->orderBy('coa_name','ASC')
                            ->get();

                            $i3_5 = 0;
                        @endphp
                        @foreach ($qCoas3_5 as $c3_5)
                            @php
                                $sumGJ_3_5 = \App\Models\Tx_general_journal_detail::leftJoin('tx_general_journals as tx_gj','tx_general_journal_details.general_journal_id','=','tx_gj.id')
                                ->whereRaw('tx_gj.general_journal_no NOT LIKE \'%Draft%\'')
                                ->whereRaw('year(tx_gj.general_journal_date)='.$period_year)
                                ->where([
                                    'tx_general_journal_details.coa_id'=>$c3_5->id,
                                    'tx_general_journal_details.active'=>'Y',
                                    'tx_gj.is_wt_for_appr'=>'N',
                                    'tx_gj.active'=>'Y',
                                ]);
                                $sumLJ_3_5 = \App\Models\Tx_lokal_journal_detail::leftJoin('tx_lokal_journals as tx_lj','tx_lokal_journal_details.lokal_journal_id','=','tx_lj.id')
                                ->whereRaw('tx_lj.general_journal_no NOT LIKE \'%Draft%\'')
                                ->whereRaw('year(tx_lj.general_journal_date)='.$period_year)
                                ->where([
                                    'tx_lokal_journal_details.coa_id'=>$c3_5->id,
                                    'tx_lokal_journal_details.active'=>'Y',
                                    'tx_lj.is_wt_for_appr'=>'N',
                                    'tx_lj.active'=>'Y',
                                ]);
                                $sumValGJ_3_5 = $sumGJ_3_5->sum('debit')-$sumGJ_3_5->sum('kredit');
                                $sumValLJ_3_5 = $sumLJ_3_5->sum('debit')-$sumLJ_3_5->sum('kredit');

                                $rightRow += 1;
                                $rightColumnNew = [
                                    $rightRow => [
                                        'id'=>$c3_5->id,
                                        'coa_name'=>$c3_5->coa_name,
                                        'coa_level'=>$c3_5->coa_level,
                                        'value'=>($sumValGJ_3_5+$sumValLJ_3_5),
                                    ],
                                ];
                                $rightColumn = array_merge($rightColumn, $rightColumnNew);
                            @endphp
                        @endforeach
                    @endforeach
                @endforeach
            @endforeach
        @endif

        @php
            $totalRows = (count($leftColumn)>count($rightColumn)?count($leftColumn):count($rightColumn));
        @endphp
        <table>
            @for ($iRows=0;$iRows<$totalRows;$iRows++)
                <tr>
                    @if ($iRows<count($leftColumn))
                        @switch($leftColumn[$iRows]['coa_level'])
                            @case(1)
                                <td style="font-weight: 700;">{{ $leftColumn[$iRows]['coa_name'] }}</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                @break
                            @case(2)
                                <td>&nbsp;</td>
                                <td style="font-weight: 700;">{{ $leftColumn[$iRows]['coa_name'] }}</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                @break
                            @case(3)
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>{{ $leftColumn[$iRows]['coa_name'] }}</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="text-align: right;">{{ $leftColumn[$iRows]['value'] }}</td>
                                @break
                            @case(4)
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>{{ $leftColumn[$iRows]['coa_name'] }}</td>
                                <td>&nbsp;</td>
                                <td style="text-align: right;">{{ $leftColumn[$iRows]['value'] }}</td>
                                @break
                            @case(5)
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>{{ $leftColumn[$iRows]['coa_name'] }}</td>
                                <td style="text-align: right;">{{ $leftColumn[$iRows]['value'] }}</td>
                                @break
                            @default
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                        @endswitch
                    @else
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    @endif

                    @if ($iRows<count($rightColumn))
                        @switch($rightColumn[$iRows]['coa_level'])
                            @case(1)
                                <td style="font-weight: 700;">{{ $rightColumn[$iRows]['coa_name'] }}</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                @break
                            @case(2)
                                <td style="font-weight: 700;">&nbsp;</td>
                                <td>{{ $rightColumn[$iRows]['coa_name'] }}</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                @break
                            @case(3)
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>{{ $rightColumn[$iRows]['coa_name'] }}</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="text-align: right;">{{ $rightColumn[$iRows]['value'] }}</td>
                                @break
                            @case(4)
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>{{ $rightColumn[$iRows]['coa_name'] }}</td>
                                <td>&nbsp;</td>
                                <td style="text-align: right;">{{ $rightColumn[$iRows]['value'] }}</td>
                                @break
                            @case(5)
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>{{ $rightColumn[$iRows]['coa_name'] }}</td>
                                <td style="text-align: right;">{{ $rightColumn[$iRows]['value'] }}</td>
                                @break
                            @default
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                        @endswitch
                    @else
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    @endif
                </tr>
            @endfor
        </table>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
