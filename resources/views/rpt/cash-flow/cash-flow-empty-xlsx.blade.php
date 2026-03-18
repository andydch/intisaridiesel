<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"> --}}

        <title>CashFlow</title>

        {{-- <style>
            table thead tr th {
                text-align: center;
            }

            .header-01 {
                background-color: #0070c0;
                color: white;
                font-weight: bold;
            }

            .header-02 {
                background-color: #ccffff;
                color: black;
                font-weight: bold;
            }
        </style> --}}
    </head>
    <body>
        @php
            $date = now();
            $timezoneNow = new DateTimeZone('Asia/Jakarta');
            $date_local_now = new DateTime();
            $date_local_now->setTimeZone($timezoneNow);

            $emptyLine = '';
        @endphp
        @for ($col=1;$col<=($monthDays+2);$col++)
            @php
                $emptyLine .= '&lt;td&gt;&lt;/td&gt;';
            @endphp
        @endfor
        @php
            $emptyLine = '&lt;tr&gt;'.$emptyLine.'&lt;/tr&gt;';
        @endphp
        <table>
            <thead>
                <tr>
                    <th class="header-01">TANGGAL</th>
                    <th class="header-01">NAMA RELASI</th>
                    @for ($iDays=1;$iDays<=$monthDays;$iDays++)
                        <th class="header-02">{{ $iDays }}</th>                        
                    @endfor
                </tr>
            </thead>
            <tbody>
                {!! html_entity_decode($emptyLine) !!}            
            </tbody>
            {{-- <tfooter></tfooter> --}}
        </table>

        {{-- <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> --}}
    </body>
</html>
