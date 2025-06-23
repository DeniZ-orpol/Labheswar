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
        }

        .divider {
            border-top: 1px solid #000;
            margin: 10px 0;
        }

        .section-title {
            font-weight: bold;
            margin: 10px 0 5px;
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
            border-bottom: 1px dotted #aaa;
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
            Labheshwar<br>
        </div>
        <div class="price-box"> {{ $order->total }} </div>
    </div>

    <!-- Full Receipt -->
    <div class="receipt-container">
        <div class="center">
            <img src="https://via.placeholder.com/80x80?text=Logo" alt="Logo"><br>
            <strong>Labheshwar</strong><br>
        </div>

        <div class="small-text">
            GSTIN :- {{ $branch->gst_no }}<br>
            {{ $branch->location }}<br>
            {{-- <strong>Phone : +91 99786 46421</strong> --}}
        </div>

        <div class="center section-title">TAX INVOICE</div>

        <div class="small-text">
            <strong>Bill No :-</strong> {{ $order->id }}<br>
            <strong>Date :-</strong> {{ $order->created_at->format('d-m-Y') }}
        </div>

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
                <tr>
                    @foreach ($orderItems as $item)
                        <td>CGST @ {{ $item->gst }} %<br> {{ $item->product->product_name }} </td>
                        <td> {{ $item->total_amount }} </td>
                        <td> {{ $item->product_weight ? $item->product_weight : $item->product_quantity }} </td>
                        <td> {{ $item->product_price }} </td>
                    @endforeach
                </tr>
            </tbody>
        </table>

        <div class="section-title">ITEM: {{ $totalItems }}</div>
        <table>
            <tr class="totals">
                <td>Qty:</td>
                <td colspan="3">{{ $totalItems }}</td>
            </tr>
            <tr class="totals">
                <td>Total:</td>
                <td colspan="3">₹{{ $order->total }}</td>
            </tr>
        </table>

        <div class="small-text">Saved Rs. -1 /- on MRP</div>
        <div class="small-text">This is computer generated invoice does not require signature.</div>

        <div class="footer"><strong>Thank You Visit Again.</strong></div>
    </div>

</body>

</html>
