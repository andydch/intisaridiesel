<!doctype html>
<html lang="en">

    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>Hello, world!</title>

        <style>
            table thead,
            table tr,
            table th {
                text-align: center;
                font-size: 10;
                font-weight: 300;
                padding: 5px;
                border: 1px solid black !important;
            }

            table tbody,
            table tr,
            table td {
                text-align: center;
                font-size: 10;
                font-weight: 300;
                padding: 5px;
                border: 1px solid black !important;
            }

            table {
                border: 1px solid black;
                background-color: whitesmoke;
            }

            table td.no-idx,
            table td.val-num {
                text-align: right;
                font-size: 10;
                font-weight: 300;
                /* border: 1px solid black !important; */
            }

            table tfoot,
            /* table tr, */
            table td.footer {
                text-align: left;
                font-size: 10;
                font-weight: 300;
                padding: 5px;
                border: 0px solid black !important;
            }

        </style>
    </head>

    <body>
        <img src="{{ $_SERVER['DOCUMENT_ROOT'].'/assets/images/logo_intisaridiesel_fc_64x64.png' }}" alt=""><br /><br />
        <h1 style="text-align: center;font-weight:bold;">PURCHASE MEMO</h1>
        <h1 style="text-align: center;">NO : 007/APM/UID/2022</h1><br /><br />
        <span style="font-size: small;">
            Kepada YTH,<br />
            PT APM LEAF SPRING INDONESIA<br />
            Suryacipta City of Industry<br />
            Jl. Surya Kencana Kavling 1 - MIJK Ciampel<br />
            Karawang - Jawa Barat<br />
            up. Bp. Himawan<br /><br />
            TGL. 28-Des-22
        </span><br /><br />
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th>NO</th>
                    <th>PARTS NO</th>
                    <th>DESCRIPTION</th>
                    <th>QTY</th>
                    <th>P/LIST</th>
                    <th>HARGA</th>
                    <th>TOTAL HARGA</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="no-idx">1</td>
                    <td>48110-EW050-F1</td>
                    <td>LEAF SPRING FR NO 1 HN 500 w/BUSH</td>
                    <td class="val-num">30</td>
                    <td class="val-num">915.000</td>
                    <td class="val-num">680.670</td>
                    <td class="val-num">20.420.105</td>
                </tr>
                <tr>
                    <td class="no-idx">2</td>
                    <td>48110-EW050-F2</td>
                    <td>LEAF SPRING FR NO 2 HN 500 w/BUSH</td>
                    <td class="val-num">30</td>
                    <td class="val-num">915.000</td>
                    <td class="val-num">680.670</td>
                    <td class="val-num">20.420.105</td>
                </tr>
                <tr>
                    <td class="no-idx">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class="val-num">&nbsp;</td>
                    <td class="val-num">&nbsp;</td>
                    <td class="val-num">&nbsp;</td>
                    <td class="val-num">&nbsp;</td>
                </tr>
                <tr>
                    <td class="no-idx">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class="val-num">&nbsp;</td>
                    <td class="val-num">&nbsp;</td>
                    <td class="val-num">&nbsp;</td>
                    <td class="val-num">&nbsp;</td>
                </tr>
                <tr>
                    <td class="no-idx">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>Total</td>
                    <td class="val-num">60</td>
                    <td class="val-num">&nbsp;</td>
                    <td class="val-num">&nbsp;</td>
                    <td class="val-num">40.840.000</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td class="footer" colspan="2">
                        Hormat kami,<br /><br />
                        <span style="text-decoration: underline;">Sulian Fajari HW</span><br />
                        PT. Usaha Intisari Diesel
                    </td>
                    <td class="footer" colspan="2">&nbsp;</td>
                    <td class="footer" colspan="3">
                        Alamat Delivery :<br />
                        Intisari Diesel - Bp. AZIZ<br />
                        d/a PT. MITRA INDAH LESTARI<br />
                        Jl. Soekarno Hatta KM 2,5<br />
                        Balikpapan<br />
                        Kalimantan TImur
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Optional JavaScript; choose one of the two! -->

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
        </script>

        <!-- Option 2: Separate Popper and Bootstrap JS -->
        <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    -->
    </body>

</html>
