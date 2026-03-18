<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>print</title>

        <style>
            @font-face {
                font-family: dotMatrix;
                src: url({{ url('assets/fonts/DOTMATRI.TTF') }});
            }

            thead tr th {
                border-top: 1px solid black;
                border-bottom: 1px solid black;
                padding: 10px;
                font-size: 17px;
                font-family: 'Calibri';
            }

            tr td {
                padding: 10px;
                font-size: 17px;
            }

            tr#row-1 td {
                font-family: Tahoma;
            }

            tr#row-2 td {
                font-family: 'Courier 10 cpi';
            }

            tr#row-3 td {
                font-family: 'Courier New';
            }

            tr#row-4 td {
                font-family: 'Calibri';
            }

            tr#row-5 td {
                font-family: 'dotMatrix';
            }

            @media print {
                html, body {
                    width: 5.5in; /* was 8.5in */
                    height: 8.5in; /* was 5.5in */
                    /* font-family: "Courier 10 cpi"; */
                    /* font-family: "Courier New"; */
                    /* font-family: "Calibri"; */
                    /* font-family: dotMatrix !important; */
                    /*font-size: auto; NOT A VALID PROPERTY */
                    /* padding-top: 3px; */
                    margin: auto;
                    display: block;
                }

                table {
                    margin: auto;
                    /* display: block; */
                }

                @page {
                    size: 5.5in 8.5in /* . Random dot? */;
                }
            }
        </style>
    </head>
    <body>
        {{-- <h1>Hello, world!</h1><br/><br/>
        <p style="font-weight: 700;font-size: 25px;">Test 12345</p><br/><br/> --}}
        <table>
            <thead>
                <tr>
                    <th>Font Family</th>
                    <th>Column 1</th>
                    <th>Column 2</th>
                    <th>Column 3</th>
                </tr>
            </thead>
            <tbody>
                <tr id="row-1">
                    <td style="font-weight:300;">Tahoma</td>
                    <td style="font-weight:700;">Column 1</td>
                    <td style="font-weight:300;">Column 2</td>
                    <td style="border-bottom: 1px solid black;font-weight:500;">Column 3</td>
                </tr>
                <tr id="row-2">
                    <td style="font-weight:300;">Courier 10 cpi</td>
                    <td style="font-weight:700;">Column 1</td>
                    <td style="font-weight:300;">Column 2</td>
                    <td style="border-bottom: 1px solid black;font-weight:500;">Column 3</td>
                </tr>
                <tr id="row-3">
                    <td style="font-weight:300;">Courier New</td>
                    <td style="font-weight:700;">Column 1</td>
                    <td style="font-weight:300;">Column 2</td>
                    <td style="border-bottom: 1px solid black;font-weight:500;">Column 3</td>
                </tr>
                <tr id="row-4">
                    <td style="font-weight:300;">Calibri</td>
                    <td style="font-weight:700;">Column 1</td>
                    <td style="font-weight:300;">Column 2</td>
                    <td style="border-bottom: 1px solid black;font-weight:500;">Column 3</td>
                </tr>
                <tr id="row-5">
                    <td style="font-weight:300;">dotMatrix</td>
                    <td style="font-weight:700;">Column 1</td>
                    <td style="font-weight:300;">Column 2</td>
                    <td style="border-bottom: 1px solid black;font-weight:500;">Column 3</td>
                </tr>
            </tbody>
        </table>

        <script>
            window.print();
        </script>
    </body>
</html>
