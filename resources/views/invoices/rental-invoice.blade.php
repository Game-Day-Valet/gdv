<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $rental->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.4;
            color: #333;
            background-color: #ffffff;
            font-size: 12px;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
        }

        /* Header Styles */
        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #C94C4C;
        }

        .company-logo {
            width: 100%;
            max-width: 400px;
            height: auto;
            margin: 0 auto 15px auto;
            display: block;
        }

        .company-info {
            text-align: center;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #C94C4C;
            margin-bottom: 5px;
            font-family: 'Arial Black', sans-serif;
            text-transform: uppercase;
        }

        .tagline {
            font-size: 12px;
            color: #333;
            font-weight: normal;
            text-transform: uppercase;
        }

        .invoice-title {
            font-size: 48px;
            font-weight: bold;
            color: #333;
            text-align: center;
            margin: 20px 0;
        }

        /* Invoice Details */
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .invoice-info {
            flex: 1;
        }

        .invoice-info h3 {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 5px;
        }

        .detail-label {
            font-weight: bold;
            min-width: 80px;
            color: #666;
        }

        .detail-value {
            color: #333;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            background-color: #f8f9fa;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            color: #333;
            border-bottom: 2px solid #C94C4C;
        }

        .items-table th:first-child {
            text-align: left;
        }

        .items-table th:nth-child(2) {
            text-align: center;
        }

        .items-table th:nth-child(3) {
            text-align: center;
        }

        .items-table th:last-child {
            text-align: right;
        }

        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e9ecef;
        }

        .items-table td:first-child {
            text-align: left;
        }

        .items-table td:nth-child(2) {
            text-align: center;
        }

        .items-table td:nth-child(3) {
            text-align: center;
        }

        .items-table td:last-child {
            text-align: right;
        }

        .items-table tr:last-child td {
            border-bottom: none;
        }

        /* Summary Section */
        .summary-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .summary-table {
            width: 300px;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #e9ecef;
        }

        .summary-table .total-row {
            border-top: 2px solid #C94C4C;
            font-weight: bold;
            font-size: 14px;
        }

        .summary-table .label {
            text-align: left;
        }

        .summary-table .amount {
            text-align: right;
        }

        /* Footer */
        .invoice-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .payment-info {
            flex: 1;
        }

        .payment-info h4 {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .thank-you {
            text-align: right;
        }

        .thank-you h3 {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .company-signature {
            width: 200px;
            height: auto;
            margin: 10px 0;
        }

        .contact-info {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px dotted #ccc;
            font-size: 11px;
            color: #666;
        }

        /* Page Break */
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <img src="https://drive.google.com/uc?export=download&id=1k0Ud895vW8x-xSzLKUZmtBFcU3I6j6P2" alt="GDV Game Day Valet" class="company-logo">
            <div class="company-info">
                <div class="company-name">GAME DAY VALET</div>
                <div class="tagline">WHERE THE FANS ARE THE MVP</div>
            </div>
        </div>

        <!-- Invoice Title -->
        <div class="invoice-title">INVOICE</div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="invoice-info">
                <div class="detail-row">
                    <span class="detail-label">Invoice no:</span>
                    <span class="detail-value">GDV-{{ str_pad($rental->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Issued to:</span>
                    <span class="detail-value">{{ $rental->coach_name ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="invoice-info">
                <div class="detail-row">
                    <span class="detail-label">Issued date:</span>
                    <span class="detail-value">{{ now()->format('M d, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Due date:</span>
                    <span class="detail-value">{{ now()->addDays(30)->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>DESCRIPTION</th>
                    <th>QTY</th>
                    <th>PRICE</th>
                    <th>SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $subtotal = 0;
                @endphp

                <!-- Rented Items -->
                @if(!empty($rental->items) && is_array($rental->items))
                    @foreach($rental->items as $item)
                        @if(is_array($item) && isset($item['item_id']) && isset($item['quantity']))
                            @php
                                $itemName = $itemNames[$item['item_id']] ?? ('Item #' . $item['item_id']);
                                $itemPrice = $itemPrices[$item['item_id']] ?? 0;
                                $itemSubtotal = $itemPrice * $item['quantity'];
                                $subtotal += $itemSubtotal;
                            @endphp
                            <tr>
                                <td>{{ $itemName }}</td>
                                <td>{{ $item['quantity'] }}</td>
                                <td>${{ number_format($itemPrice, 2) }}</td>
                                <td>${{ number_format($itemSubtotal, 2) }}</td>
                            </tr>
                        @endif
                    @endforeach
                @endif

                <!-- Rented Bundles -->
                @if(!empty($rental->bundles) && is_array($rental->bundles))
                    @foreach($rental->bundles as $bundle)
                        @if(is_array($bundle) && isset($bundle['bundle_id']))
                            @php
                                $bundleName = $bundleNames[$bundle['bundle_id']] ?? ('Bundle #' . $bundle['bundle_id']);
                                $bundlePrice = $bundlePrices[$bundle['bundle_id']] ?? 0;
                                $bundleQuantity = $bundle['quantity'] ?? 1;
                                $bundleSubtotal = $bundlePrice * $bundleQuantity;
                                $subtotal += $bundleSubtotal;
                            @endphp
                            <tr>
                                <td>{{ $bundleName }}</td>
                                <td>{{ $bundleQuantity }}</td>
                                <td>${{ number_format($bundlePrice, 2) }}</td>
                                <td>${{ number_format($bundleSubtotal, 2) }}</td>
                            </tr>
                        @elseif(is_numeric($bundle))
                            @php
                                $bundleName = $bundleNames[$bundle] ?? ('Bundle #' . $bundle);
                                $bundlePrice = $bundlePrices[$bundle] ?? 0;
                                $bundleSubtotal = $bundlePrice;
                                $subtotal += $bundleSubtotal;
                            @endphp
                            <tr>
                                <td>{{ $bundleName }}</td>
                                <td>1</td>
                                <td>${{ number_format($bundlePrice, 2) }}</td>
                                <td>${{ number_format($bundleSubtotal, 2) }}</td>
                            </tr>
                        @endif
                    @endforeach
                @endif

                @php
                    $tax = 10; // Fixed tax amount as shown in image
                    $total = $subtotal + $tax;
                @endphp
            </tbody>
        </table>

        <!-- Summary Section -->
        <div class="summary-section">
            <table class="summary-table">
                <tr>
                    <td class="label">TAX:</td>
                    <td class="amount">${{ number_format($tax, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td class="label">TOTAL:</td>
                    <td class="amount">${{ number_format($total, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="footer-content">
                <div class="payment-info">
                    <h4>Payment info:</h4>
                </div>
                <div class="thank-you">
                    <h3>THANK YOU FOR YOUR ORDER!</h3>
                    <img src="https://drive.google.com/uc?export=download&id=1R3Kvu8bhqyXs6ISnG92HhPw40KxWKd4s" alt="Game Day Valet Signature" class="company-signature">
                </div>
            </div>
            <div class="contact-info">
                support@gamedayvaletrentals.com | 704.472.9099
            </div>
        </div>
    </div>
</body>
</html>
