<!DOCTYPE html>
<html>
<head>
    <title>Tax Invoice</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .info {
            margin-bottom: 15px;
            border: 1px solid #333;
            padding: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .total {
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img alt="Labheshwer" class="w-6" src="{{ asset('images/logo.png') }}"> 
        <h2>Chalan </h2>
    </div>
    <div class="info">
        <p><strong>Chalan ID:</strong> {{ $chalan->chalan_no }}</p>
        <p><strong>Date:</strong> {{ $chalan->date }}</p>
        <p><strong>From Branch:</strong> {{ $branchData->name ?? 'N/A' }}</p>
        <p><strong>To Branch:</strong> {{ $toBranchData->name ?? 'N/A' }}</p>
        <p><strong>Created By:</strong> {{ $userData->name ?? 'N/A' }}</p>
        <p><strong>Gst NO:</strong> {{ $branchData->gst_no }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>HSN</th>
                <th>P.Rate</th>
                <th>Box</th>
                <th>PCS</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stocks as $stock)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $stock->product->product_name ?? 'N/A' }}</td>
                    <td>{{ $stock->product->hsnCode->hsn_code ?? 'N/A' }}</td>
                    <td>{{ number_format($stock->prate, 2) }}</td>
                    <td>{{ $stock->box }}</td>
                    <td>{{ $stock->pcs }}</td>
                    <td>{{ number_format($stock->amount, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="6" class="total">Total Amount</td>
                <td class="total">{{ number_format($chalan->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="6" class="total">Grand Total</td>
                <td class="total">{{ number_format($chalan->total_amount + $chalan->total_amount  , 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>For any inquiries, please contact us at [Your Contact Information]</p>
    </div>
</body>
</html>
