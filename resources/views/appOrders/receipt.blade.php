<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Receipt</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            padding: 20px;
            background: #f7f7f7;
        }

        .receipt-container {
            width: 300px;
            background: white;
            border: 1px solid #ddd;
            margin: 20px auto;
            padding: 10px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .center {
            text-align: center;
        }

        .logo {
            font-size: 14px;
            font-weight: bold;
        }

        .price-box {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            padding: 10px 0;
            padding-top: 0px;
        }

        .divider {
            border-top: 1px solid #000;
            margin: 10px 0;
        }

        .section-title {
            font-weight: bold;
            margin: 10px 0 5px;
            text-align: center;
        }

        table {
            width: 100%;
            font-size: 12px;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: left;
            padding: 3px;
            /* border-bottom: 1px dotted #aaa; */
        }

        .divider {
            margin: 0;
        }

        .divider1 {
            margin-bottom: 0px;
        }

        .totals td {
            font-weight: bold;
        }

        .footer {
            font-size: 12px;
            margin-top: 10px;
            text-align: center;
        }

        .small-text {
            font-size: 11px;
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }
    </style>
</head>

<body>

    <!-- Top Price Box -->
    <div class="receipt-container center">
        <div class="logo">
            <img src="https://via.placeholder.com/80x80?text=Logo" alt="Logo"><br>
            Sweetler<br>
            <hr>
        </div>
        <div class="price-box">₹{{ $order->total }} </div>
    </div>

    <!-- Full Receipt -->
    <div class="receipt-container">
        <div class="center">
            <img src="https://via.placeholder.com/80x80?text=Logo" alt="Logo"><br>
            <strong>Sweetler</strong><br>
        </div>
        <hr>
        <div class="small-text">
            GSTIN :- {{ $branch->gst_no }}<br>
            {{ $branch->location }}<br>
            <strong>Phone : +91 99786 46421</strong>
        </div>
        <hr>
        <div class="center section-title">TAX INVOICE</div>

        <div class="small-text">
            <strong>Bill No :-</strong> {{ $order->id }}<br>
            <strong>Date :-</strong> {{ $order->created_at->format('d-m-Y') }}
        </div>
        <hr>
        <div class="section-title">Products</div>
        <table>
            <thead>
                <tr>
                    <th>Particulars</th>
                    <th>Rate</th>
                    <th>Qty.</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orderItems as $index => $item)
                    <tr>
                        {{-- {{dd($item->product)}} --}}
                        <td style="font-size: 9px">
                            <strong>{{ $index + 1 }} ) CGST @ {{ rtrim(rtrim($item->product->cgst1, '0'), '.') }}%,
                                SGST @ {{ rtrim(rtrim($item->product->sgst, '0'), '.') }}%</strong><br>
                            <span style="font-weight: bold">{{ $item->product->barcode }}</span>
                            {{ strtoupper($item->product->product_name) }}
                        </td>
                        <td>{{ $item->product_price }}</td>
                        <td>{{ $item->product_weight ? $item->product_weight : $item->product_quantity }}</td>
                        <td>{{ $item->total_amount }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <hr class="divider">

        <table>
            <tr class="totals">
                <td>ITEM: {{ $totalItems }}</td>
                <td></td>
                <td colspan="3" style="text-align: right; padding: 0px;">&nbsp;Qty:{{ $totalItems }}</td>
                <td colspan="3" style="text-align: center; padding: 0px;">₹{{ $order->total }}</td>
            </tr>
        </table>
        <hr class="divider1">
        <table>
            <thead>
                <tr>
                    <th style="font-size: 8px;font-weight:400">Gst IND</th>
                    <th style="font-size: 8px;font-weight:400">Goods Value</th>
                    <th style="font-size: 8px;font-weight:400">CGST</th>
                    <th style="font-size: 8px;font-weight:400">SGST</th>
                    <th style="font-size: 8px;font-weight:400">CESS</th>
                    <th style="font-size: 8px;font-weight:400">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orderItems as $index => $item)
                    <tr>
                        {{-- {{dd($item)}} --}}
                        <td style="font-size: 8px;font-weight:400">{{ $loop->iteration }}</td>
                        {{-- <td style="font-size: 9px">
                            <strong>{{ $index + 1 }} ) CGST @ {{ $item->gst }} %, SGST @ {{ $item->sgst }}
                                %</strong><br>
                            <span style="font-weight: bold">{{ $item->product->barcode }}</span>
                            {{ strtoupper($item->product->product_name) }}
                        </td> --}}
                        {{-- {{dd($item->product_price *$item->product_quantity)}} --}}
                        <td style="font-size: 8px;font-weight:600">₹{{ number_format($item->product_price * $item->product_quantity,2)   }}
                        </td>
                        <td style="font-size: 8px;font-weight:600">
                            ₹{{ number_format(($item->product_price * $item->product_quantity * $item->product->cgst1) / 100, 2) }}
                        </td>
                        <td style="font-size: 8px;font-weight:600">
                            ₹{{ number_format(($item->product_price * $item->product_quantity * $item->product->sgst) / 100, 2) }}
                        </td>
                        <td style="font-size: 8px;font-weight:600">
                            ₹{{ number_format(($item->product_price * $item->product_quantity * $item->product->cess) / 100, 2) }}
                        </td>
                        <td style="font-size: 8px; font-weight:600">
                            ₹{{ number_format(
                                $item->product_price * $item->product_quantity +
                                    ($item->product_price * $item->product_quantity * $item->product->cgst1) / 100 +
                                    ($item->product_price * $item->product_quantity * $item->product->sgst) / 100 +
                                    ($item->product_price * $item->product_quantity * $item->product->cess) / 100,
                                2,
                            ) }}
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="small-text">Saved Rs. -1 /- on MRP</div>
        <div class="small-text">This is computer generated invoice does not require signature.</div>

        <div class="footer"><strong>Thank You Visit Again.</strong></div>
    </div>

</body>

</html>
