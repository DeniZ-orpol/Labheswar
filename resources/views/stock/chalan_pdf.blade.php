<!DOCTYPE html>
<html>
<head>
    <title>Delivery Chalan</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .logo {
            max-width: 150px;
        }
        .info-table, .product-table, .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .info-table td, .product-table th, .product-table td, .signature-table td {
            border: 1px solid #333;
            padding: 5px;
            vertical-align: top;
        }
        .product-table th {
            background-color: #eee;
        }
        .total-row td {
            font-weight: bold;
        }
        .terms-box {
            border: 1px solid #333;
            height: 100px;
            padding: 5px;
        }
        .signature-table td {
            height: 80px;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 15px;
        }
    </style>
</head>
<body>

    <div class="header">
        <img class="logo" src="{{ asset('images/logo.png') }}" alt="Logo"><br>
        <h3>Delivery Chalan (Challan)</h3>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>Challan ID:</strong> {{ $chalan->chalan_no }}</td>
            <td><strong>Date:</strong> {{ $chalan->date }}</td>
        </tr>
        <tr>
            <td>
                <strong>From:</strong> {{ $branchData->name ?? 'Branch Name' }}<br>
                <strong>Address:</strong> {{ $branchData->address ?? 'Address' }}<br>
                <strong>Contact:</strong> {{ $branchData->contact ?? 'Contact' }}<br>
                <strong>GSTIN:</strong> {{ $branchData->gst_no ?? 'GST' }}
            </td>
            <td>
                <strong>To:</strong> {{ $toBranchData->name ?? 'Branch Name' }}<br>
                <strong>Address:</strong> {{ $toBranchData->address ?? 'Address' }}<br>
                <strong>Contact:</strong> {{ $toBranchData->contact ?? 'Contact' }}<br>
                <strong>GSTIN:</strong> {{ $toBranchData->gst_no ?? 'GST' }}
            </td>
        </tr>
    </table>

    <table class="product-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Item Name</th>
                <th>HSN</th>
                <th>Box</th>
                <th>PCS</th>
                <th>Amount</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stocks as $stock)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $stock->product->product_name ?? 'N/A' }}</td>
                    <td>{{ $stock->product->hsnCode->hsn_code ?? 'N/A' }}</td>
                    <td>{{ $stock->box }}</td>
                    <td>{{ $stock->pcs }}</td>
                    <td>{{ number_format($stock->amount, 2) }}</td>
                    <td>{{ $stock->unit ?? '-' }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5">Total</td>
                <td>{{ number_format($chalan->total_amount, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <table width="100%" style="margin-bottom: 10px;">
        <tr>
            <td width="49%" class="terms-box">
                <strong>Terms & Conditions:</strong><br><br>
                <!-- Optional text -->
            </td>
            <td width="49%" class="terms-box">
                <strong>For, Company Name</strong><br><br>
                <!-- Optional signature -->
            </td>
        </tr>
    </table>

    <table class="signature-table" style="gap: 2px;">
        <tr style="margin-bottom: 10px;">
            <td>
                <strong>Delivered by</strong><br><br>
                Name: ___________<br>
                Date: ___________<br>
                Sign: ___________
            </td>
        </tr>
        <tr>
            <td>
                <strong>Received by</strong><br><br>
                Name: ___________<br>
                Date: ___________<br>
                Sign: ___________
            </td>
        </tr>
    </table>

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>For any inquiries, contact us at [Your Contact Information]</p>
    </div>

</body>
</html>
