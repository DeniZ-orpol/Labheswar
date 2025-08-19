<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            padding: 40px;
            color: #333;
        }

        .invoice-box {
            background: #fff;
            max-width: 800px;
            margin: auto;
            padding: 40px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.06);
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        header img {
            height: 80px;
            margin-bottom: 10px;
        }

        header h1 {
            font-size: 28px;
            color: #6C63FF;
            margin: 0;
        }

        .invoice-meta,
        .footer {
            text-align: center;
            font-size: 14px;
            color: #777;
        }

        .section-title {
            font-size: 18px;
            margin-top: 35px;
            font-weight: bold;
            color: #6C63FF;
            border-bottom: 2px solid #eee;
            padding-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 14px;
        }

        th {
            background-color: #f9f9f9;
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #ddd;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .totals td {
            font-weight: bold;
            color: #333;
        }

        .summary td {
            padding: 8px 12px;
        }

        .highlight {
            font-size: 22px;
            color: #222;
            font-weight: bold;
            text-align: right;
            margin-top: 30px;
            padding: 15px 0;
            border-top: 2px dashed #ddd;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            font-size: 13px;
            border-top: 1px solid #eaeaea;
        }

        .bill-to {
            margin-top: 20px;
        }

        .bill-to td {
            padding: 6px 0;
        }

        .right-align {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <header>
            <img src="https://via.placeholder.com/150x150?text=Logo" alt="Logo">
            <h1>Sweetler</h1>
        </header>

        <div class="invoice-meta">
            GSTIN: {{ $branch->gst_no }} | Location: {{ $branch->location }} <br>
            Phone: +91 99786 46421
        </div>

        <div class="section-title">Stock Issue Details</div>
        <table class="summary">
            <tr>
                <td><strong>Issue No:</strong></td>
                <td>{{ $stockIssue->issue_no }}</td>
                <td><strong>Date:</strong></td>
                <td>{{ $stockIssue->date->format('d-m-Y') }}</td>
            </tr>
        </table>


        <div class="section-title">Bill To</div>
        <table class="bill-to">
            <tr>
                <td><strong>Ledger Name:</strong></td>
                <td>{{ $stockIssue->getRelation('ledger')->party_name ?? 'N/A' }}</td>
                <td><strong>Contact:</strong></td>
                <td>{{ $stockIssue->getRelation('ledger')->mobile_no ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>From Branch:</strong></td>
                <td>{{ $stockIssue->fromBranch->name ?? '-' }}</td>
                <td><strong>To Branch:</strong></td>
                <td>{{ $stockIssue->toBranch->name ?? '-' }}</td>
            </tr>
        </table>

        <div class="section-title">Products</div>
        <table>
            <thead>
                <tr>
                    <th>Particulars</th>
                    <th class="right-align">Rate (₹)</th>
                    <th class="right-align">Qty</th>
                    <th class="right-align">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($stockIssue->products as $item)
                    <tr>
                        <td>{{ strtoupper($item['product_search']) }}</td>
                        <td class="right-align">{{ number_format($item['sale_rate'], 2) }}</td>
                        <td class="right-align">{{ $item['qty'] }}</td>
                        <td class="right-align">{{ number_format($item['amount'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="summary">
            <tr class="totals">
                <td colspan="2">Items: {{ count($stockIssue->products) }}</td>
                <td class="right-align">Total Qty:</td>
                <td class="right-align">{{ collect($stockIssue->products)->sum('qty') }}</td>
            </tr>
            <tr class="totals">
                <td colspan="3" class="right-align">Total Amount:</td>
                <td class="right-align">₹{{ number_format($stockIssue->total_amount, 2) }}</td>
            </tr>
        </table>

        <div class="highlight">Total Due: ₹{{ number_format($stockIssue->total_amount, 2) }}</div>

        <div class="footer">
            This is a computer-generated invoice and does not require a signature.<br>
            <strong>Thank you for your business!</strong>
        </div>
    </div>
</body>

</html>
