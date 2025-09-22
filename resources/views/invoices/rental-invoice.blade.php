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
            background-color: #f5f5dc;
            font-size: 12px;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5dc;
        }

        /* Header Styles */
        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }

        .gdv-logo {
            font-size: 48px;
            font-weight: bold;
            color: #000;
            margin-right: 20px;
            font-family: 'Arial Black', sans-serif;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .company-name {
            font-size: 32px;
            font-weight: bold;
            color: #C94C4C;
            font-family: 'Arial Black', sans-serif;
            text-transform: uppercase;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .tagline {
            font-size: 14px;
            color: #333;
            font-weight: normal;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .top-image {
            width: 100%;
            max-width: 500px;
            height: 200px;
            object-fit: contain;
            margin: 20px auto;
            display: block;
        }

        .top-image-placeholder {
            width: 100%;
            max-width: 400px;
            height: 100px;
            margin: 20px auto;
            display: block;
            border: 2px dashed #ccc;
            text-align: center;
            line-height: 100px;
            color: #666;
            background-color: #f9f9f9;
        }

        .signature-placeholder {
            width: 200px;
            height: 50px;
            margin: 10px 0;
            border: 1px dashed #ccc;
            text-align: center;
            line-height: 50px;
            color: #666;
            background-color: #f9f9f9;
            font-style: italic;
        }

        .invoice-title {
            font-size: 48px;
            font-weight: bold;
            color: #333;
            text-align: center;
            margin: 20px 0;
            text-transform: uppercase;
        }

        /* Invoice Details */
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .invoice-info-left {
            flex: 1;
        }

        .invoice-info-right {
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

        .detail-row-right {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 5px;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            background-color: #ffffff;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            color: #000;
            border-bottom: 1px solid #000;
            text-transform: uppercase;
        }

        .items-table th:first-child {
            text-align: left;
        }

        .items-table th:nth-child(2) {
            text-align: right;
        }

        .items-table th:nth-child(3) {
            text-align: right;
        }

        .items-table th:last-child {
            text-align: right;
        }

        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #000;
        }

        .items-table td:first-child {
            text-align: left;
        }

        .items-table td:nth-child(2) {
            text-align: right;
        }

        .items-table td:nth-child(3) {
            text-align: right;
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
            border-bottom: 1px solid #000;
        }

        .summary-table .total-row {
            border-top: 1px solid #000;
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
            text-transform: uppercase;
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
            border-top: 1px dotted #000;
            font-size: 11px;
            color: #000;
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
            @if(file_exists(public_path('images/topimage.png')))
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/topimage.png'))) }}" class="top-image">
            @else
                <div class="top-image-placeholder">Top Image</div>
            @endif
        </div>

        <!-- Invoice Title -->
        <div class="invoice-title">INVOICE</div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="invoice-info-left">
                <div class="detail-row">
                    <span class="detail-label">Invoice no:</span>
                    <span class="detail-value">GDV-{{ str_pad($rental->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Issued to:</span>
                    <span class="detail-value">{{ $rental->user->name ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="invoice-info-right">
                <div class="detail-row-right">
                    <span class="detail-label">Issued date:</span>
                    <span class="detail-value">{{ $rental->created_at->format('M d, Y') }}</span>
                </div>
                <div class="detail-row-right">
                    <span class="detail-label">Due date:</span>
                    <span class="detail-value">{{ $rental->created_at->addDays(30)->format('M d, Y') }}</span>
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
                    $tax = 0; // Tax hidden for now
                    $total = $subtotal; // Total without tax
                @endphp
            </tbody>
        </table>

        <!-- Summary Section -->
        <div class="summary-section">
            <table class="summary-table">
                <tr>
                    <td class="label">SUBTOTAL:</td>
                    <td class="amount">${{ number_format($subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">INSURANCE:</td>
                    <td class="amount">${{ number_format((float)($rental->insurance_option ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <td class="label">DAMAGE WAIVER:</td>
                    <td class="amount">${{ number_format((float)($rental->damage_waiver ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <td class="label">TAX ({{ number_format((float)($rental->tax_rate ?? 0), 2) }}%):</td>
                    <td class="amount">${{ number_format((float)($rental->tax_amount ?? 0), 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td class="label">TOTAL:</td>
                    <td class="amount">${{ number_format((float)($rental->total_amount ?? ($subtotal + (float)($rental->insurance_option ?? 0) + (float)($rental->damage_waiver ?? 0))), 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="footer-content">
                <div class="payment-info">
                    <h4>Payment info:</h4>
                    <p><strong>Payment Method:</strong> {{ ucfirst($rental->payment_method ?? 'Not specified') }}</p>
                    <p><strong>Payment Status:</strong> {{ ucfirst($rental->payment_status ?? 'Pending') }}</p>
                    @if($rental->payment_status === 'pending' || $rental->payment_status === 'unpaid')
                        <p>Payment due upon delivery</p>
                        <p>Cash, Credit Card, or Check accepted</p>
                    @elseif($rental->payment_status === 'paid')
                        <p>Payment completed - Thank you!</p>
                    @endif
                    <p>Contact us for payment arrangements</p>
                </div>
                <div class="thank-you">
                    <h3>THANK YOU FOR YOUR ORDER!</h3>
                    @if(file_exists(public_path('images/signature.png')))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/signature.png'))) }}" alt="Game Day Valet Signature" class="company-signature">
                    @else
                        <div class="signature-placeholder">Game Day Valet</div>
                    @endif
                </div>
            </div>
            <div class="contact-info">
                support@gamedayvaletrentals.com | 704.472.9099
            </div>
        </div>
    </div>
</body>
</html>
