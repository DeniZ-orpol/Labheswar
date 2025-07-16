<!DOCTYPE html>
<html>
<head>
    <title>Chalan PDF</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
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
        .info {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <h2 class="header">Chalan Receipt</h2>
    <div class="info">
        <p><strong>Chalan ID:</strong> {{ $chalan->chalan_no }}</p>
        <p><strong>Date:</strong> {{ $chalan->date }}</p>
        <p><strong>From Branch:</strong> {{ $branchData->name ?? 'N/A' }}</p>
        <p><strong>To Branch:</strong> {{ $toBranchData->name ?? 'N/A' }}</p>
        <p><strong>Created By:</strong> {{ $userData->name ?? 'N/A' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>P.Rate</th>
                <th>Box</th>
                <th>PCS</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stocks as $stock)
                <tr>
                    <td>{{ $loop->index +1 }}</td>
                    <td>{{ $stock->product->product_name ?? 'N/A' }}</td>
                    <td>{{ $stock->prate }}</td>
                    <td>{{ $stock->box }}</td>
                    <td>{{ $stock->pcs }}</td>
                    <td>{{ $stock->amount }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="5"><strong>Total Amount</strong></td>
                <td><strong>{{ number_format($chalan->total_amount, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
