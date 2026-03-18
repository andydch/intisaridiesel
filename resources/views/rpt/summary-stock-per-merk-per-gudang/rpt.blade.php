<!doctype html>
<html lang="en">
    <head>
            <!-- Required meta tags -->
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">

            <!-- Bootstrap CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

            <title>abc title</title>
    </head>
    <body>
        <table>
            <thead>
                <tr><th colspan="4" style="font-weight:bold;">{{ $title01 }}</th></tr>
                <tr><th colspan="4" style="font-weight:bold;">{{ $title02 }}</th></tr>
                <tr>
                    <th style="font-weight:bold;width:150px;">MERK</th>
                    <th style="font-weight:bold;width:200px;">GUDANG</th>
                    <th style="font-weight:bold;width:150px;">COST AMOUNT (Rp)</th>
                    <th style="font-weight:bold;width:150px;">SALES AMOUNT (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total_cost_amount = 0;
                    $total_sales_amount = 0;
                    $divRow = '';
                    $divRowCostAmount = 0;
                    $divRowSalesAmount = 0;
                @endphp
                @foreach ($query as $q)
                    @if ($divRow!='' && $divRow!=$q->brand_name)
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="text-align: right;">{{ number_format($divRowCostAmount,2,',','.') }}</td>
                            <td style="text-align: right;">{{ number_format($divRowSalesAmount,2,',','.') }}</td>
                        </tr>
                        @php
                            $divRowCostAmount = 0;
                        @endphp
                    @endif
                    <tr>
                        <td>{{ ($divRow!=$q->brand_name)?$q->brand_name:'' }}</td>
                        <td>{{ $q->branch_name }}</td>
                        <td style="text-align: right;">{{ number_format($q->cost_amount,2,',','.') }}</td>
                        <td style="text-align: right;">{{ number_format($q->sales_amount,2,',','.') }}</td>
                    </tr>
                    @php
                        $divRow = $q->brand_name;
                        $divRowCostAmount += $q->cost_amount;
                        $divRowSalesAmount += $q->sales_amount;
                        $total_cost_amount += $q->cost_amount;
                        $total_sales_amount += $q->sales_amount;
                    @endphp
                @endforeach
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="text-align: right;">{{ number_format($divRowCostAmount,2,',','.') }}</td>
                    <td style="text-align: right;">{{ number_format($divRowSalesAmount,2,',','.') }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th>&nbsp;</th>
                    <th style="font-weight:bold;">GRAND TOTAL</th>
                    <th style="text-align: right;font-weight:bold;">{{ number_format($total_cost_amount,2,',','.') }}</th>
                    <th style="text-align: right;font-weight:bold;">{{ number_format($total_sales_amount,2,',','.') }}</th>
                </tr>
            </tfoot>
        </table>

        <!-- Optional JavaScript; choose one of the two! -->

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

        <!-- Option 2: Separate Popper and Bootstrap JS -->
        <!--
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
        -->
    </body>
</html>
