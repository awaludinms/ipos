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
                width: 5cm !important;
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
                @if($type == 'download')
                <img style="width:5cm !important;" src="{{ public_path() . '/imgs/STRUK BS2.png' }}">
                @else
                <img style="width:5cm !important;" src="{{ asset('/imgs/STRUK BS2.png') }}">
                @endif
            </td>
        </tr>
        <tr>
            <td>
                <hr>
            </td>
        </tr>
        <tr>
            <td>
                <table style="width:100% !important;border:0" border="0">
                    <tr>
                        <td width="1">Nota</td>
                        <td width="1">:</td>
                        <td width="30%">{{ $transaction_number }}</td>
                        <td>&nbsp;&nbsp;</td>
                        <td width="1">Tgl Pesan</td>
                        <td width="1">:</td>
                        <td width="30%">{{ date('d/m/Y', strtotime($transaction->transaction_date)) }}</td>
                    </tr>
                    <tr>
                        <td width="1">Tel/HP</td>
                        <td width="1">:</td>
                        <td width="30%">{{ $transaction->customer->phone }}</td>
                        <td>&nbsp;&nbsp;</td>
                        <td width="1">Jam</td>
                        <td width="1">:</td>
                        <td width="30%">{{ date('H:i', strtotime($transaction->transaction_date)) }}</td>
                    </tr>
                    <tr>
                        <td width="1">Pelanggan</td>
                        <td width="1">:</td>
                        <td width="30%">{{ $transaction->customer->name }}</td>
                        <td>&nbsp;&nbsp;</td>
                        <td width="1">Tgl Ambil</td>
                        <td width="1">:</td>
                        <td width="30%">{{ date('d/m/Y', strtotime($taken_date)) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td>
                        <td>&nbsp;&nbsp;</td>
                        <td width="1">Jam Ambil</td>
                        <td width="1">:</td>
                        <td width="30%">{{ date('H:i', strtotime($taken_time)) }}</td>
                    </tr>
                </table>
            <td>
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
                        <td class="text-align:left !important;">Nama Produk</td>
                        <td>Harga</td>
                        <td>Jumlah</td>
                        <td>Subtotal</td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <hr>
                        </td>
                    </tr>
                    @foreach($det_trans as $detail)
                        <tr>
                            <td>{!! nl2br($detail->product_name) !!}</td>
                            <td style="text-align:right;">{{ number_format($detail->product_price, 0, ',', '.') }}</td>
                            <td style="text-align:center;">{{ $detail->product_qty }}</td>
                            <td style="text-align:right;">{{ number_format($detail->product_subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="4">
                            <hr>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <center>
                                <table style="width:90% !important;border:0;border-collapse:0;cell-spacing:0"
                                    border="0">
                                    <tr>
                                        <td width="40%">Jumlah keseluruhan</td>
                                        <td width="1">:</td>
                                        <td>Rp. {{ number_format($transaction->grand_total, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td width="100%" colspan="3">
                                            <hr>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="1">Sisa</td>
                                        <td width="1">:</td>
                                        <td width="">Rp.
                                            {{ number_format($transaction->grand_total - $transaction->paid, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="1">Bayar</td>
                                        <td width="1">:</td>
                                        <td width="">Rp. {{ number_format($transaction->paid, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td width="1">Kembali</td>
                                        <td width="1">:</td>
                                        <td width="">Rp. {{ number_format($transaction->change_return, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </table>
                            </center>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <hr>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <center>
                                <table style="width:99%">
                                    <tr>
                                        <td width="60%">
                                            <div style="border:1px solid;padding:4px;">Pembayaran ke: {{ $pembayaran_ke }}<br><strong>
                                                    <div>
                                                        {{ $transaction->grand_total <= $transaction->paid ? 'Lunas' : (($transaction->paid != 0) ? 'DP' : 'Belum Lunas') }}
                                                    </div>
                                                </strong></div>
                                        </td>
                                        <td width="10%">&nbsp;</td>
                                        <td width="30%">Petugas<br>{{ $transaction->staff->name }}</td>
                                    </tr>
                                </table>
                            </center>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <div>Keterangan</div>
                {{  $transaction->keterangan }}
            </td>
        </tr>
    </table>
    <script>
        window.print()
        window.addEventListener("afterprint", () => self.close);
    </script>
</body>

</html>