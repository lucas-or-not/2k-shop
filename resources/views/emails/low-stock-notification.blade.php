<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Alert</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #dc2626;">Low Stock Alert</h2>
        
        <p>Hello Admin,</p>
        
        <p>The following product is running low on stock:</p>
        
        <div style="background-color: #f3f4f6; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin-top: 0;">{{ $product->name }}</h3>
            <p><strong>Current Stock:</strong> {{ $stockQuantity }} units</p>
            <p><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
            @if($product->description)
                <p><strong>Description:</strong> {{ $product->description }}</p>
            @endif
        </div>
        
        <p>Please consider restocking this product soon.</p>
        
        <p>Best regards,<br>2kShop System</p>
    </div>
</body>
</html>

