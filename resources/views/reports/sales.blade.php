<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        @media print {
            body {
                margin: 0px !important;
                background-image: none;
                font-size: 7pt !important;
                background-color: #fff;
                /* width: 5cm !important; */
                font-family: Arial;
            }
        }
        @media screen {
            body {
                margin: 0px !important;
            }
        }
        .tab {
            padding-left: 12px;
        }
    </style>
</head>

<body>
    <table style="width:5cm !important;font-size:10pt !important;border-collapse:collapse;font-family:Arial;">
        <tr>
            <td style="text-align:center;">
               
                <img style="width:5cm !important;" src="{{ public_path() . '/imgs/STRUK BS2.png' }}">
               
            </td>
        </tr>
        <tr>
            <td>
                <hr>
            </td>
        </tr>
        <tr>
            <td>
                <table style="width:100% !important;border:0;" border="0">
                    <tr>
                        <td class="text-align:left !important;">Tanggal Transaksi</td>
                        <td>Nama Produk</td>
                        <td>Harga</td>
                        <td>Qty</td>
                        <td>Sub Total</td>
                        <td>Kategori</td>
                        <td>Pelanggan</td>
                        <td>Tipe Pelanggan</td>
                        <td>Status</td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <hr>
                        </td>
                    </tr>
                    @foreach($report as $detail)
                        <tr>
                            <td>{{ $detail->transaction_date_simple_formatted }}</td>
                            <td>{!! nl2br($detail->product_name) !!}</td>
                            <td style="text-align:right;">{{ number_format($detail->product_price, 0, ',', '.') }}</td>
                            <td style="text-align:center;">{{ $detail->product_qty }}</td>
                            <td style="text-align:right;">{{ number_format($detail->product_subtotal, 0, ',', '.') }}</td>
                            <td style="text-align:right;">{{ $detail->name }}</td>
                            <td style="text-align:right;">{{ $detail->customer_name }}</td>
                            <td style="text-align:right;">{{ $detail->customer_type }}</td>
                            <td style="text-align:right;">{{ $detail->transaction_state }}</td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>
</body>

</html>