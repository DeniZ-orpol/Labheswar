<!DOCTYPE html>
<html>
<head>
    <title>Stock Group PDF</title>
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
    </style>
</head>
<body>
    <h2 class="header">Chalan</h2>
    <p><strong>Chalan ID:</strong> {{ $stocks->first()->chalan_id }}</p>
    <p><strong>Date:</strong> {{ $stocks->first()->date }}</p>
    <p><strong>From:</strong> {{ $userData->branch->name ?? '' }}</p>
    <p><strong>To:</strong> {{ $branchData->name ?? '' }}</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Product ID</th>
                <th>MRP</th>
                <th>Box</th>
                <th>PCS</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stocks as $stock)
                <tr>
                    <td>{{ $stock->id }}</td>
                    <td>{{ $stock->product_id }}</td>
                    <td>{{ $stock->mrp }}</td>
                    <td>{{ $stock->box }}</td>
                    <td>{{ $stock->pcs }}</td>
                    <td>{{ $stock->amount }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="5"><strong>Total Amount</strong></td>
                <td><strong>{{ $stocks->sum('amount') }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>