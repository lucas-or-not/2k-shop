<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Sales Report</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2563eb;">Daily Sales Report</h2>
        
        <p>Hello Admin,</p>
        
        <p>Here is your daily sales report for <strong>{{ $salesSummary['date'] }}</strong>:</p>
        
        <div style="background-color: #f3f4f6; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin-top: 0;">Summary</h3>
            <ul style="list-style: none; padding: 0;">
                <li style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Total Revenue:</strong> ${{ number_format($salesSummary['total_revenue'], 2) }}</li>
                <li style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;"><strong>Total Orders:</strong> {{ $salesSummary['total_orders'] }}</li>
                <li style="padding: 8px 0;"><strong>Total Items Sold:</strong> {{ $salesSummary['total_items'] }}</li>
            </ul>
        </div>
        
        @if(count($salesSummary['product_breakdown']) > 0)
            <h3>Product Breakdown</h3>
            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <thead>
                    <tr style="background-color: #2563eb; color: white;">
                        <th style="padding: 12px; text-align: left; border: 1px solid #1e40af;">Product</th>
                        <th style="padding: 12px; text-align: right; border: 1px solid #1e40af;">Quantity</th>
                        <th style="padding: 12px; text-align: right; border: 1px solid #1e40af;">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesSummary['product_breakdown'] as $product)
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 12px; border: 1px solid #e5e7eb;">{{ $product['name'] }}</td>
                            <td style="padding: 12px; text-align: right; border: 1px solid #e5e7eb;">{{ $product['quantity'] }}</td>
                            <td style="padding: 12px; text-align: right; border: 1px solid #e5e7eb;">${{ number_format($product['revenue'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="color: #6b7280; font-style: italic;">No sales were recorded for this day.</p>
        @endif
        
        <p>Best regards,<br>2kShop System</p>
    </div>
</body>
</html>

