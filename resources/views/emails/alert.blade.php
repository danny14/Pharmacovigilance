<!DOCTYPE html>
<html>
<head>
    <title>Urgent Medication Recall Notice</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; border: 1px solid #dcdcdc; padding: 20px;">
        <h2 style="color: #d9534f;">⚠️ URGENT: Medication Recall Notice</h2>
        
        <p>Dear <strong>{{ $customer->name }}</strong>,</p>

        <p>This is an important warning notice regarding a medication you purchased from our compounding pharmacy.</p>

        <div style="background-color: #f9f2f4; padding: 15px; border-left: 4px solid #d9534f; margin: 20px 0;">
            <p><strong>Order ID:</strong> #{{ $order->id }}</p>
            <p><strong>Purchase Date:</strong> {{ $order->purchase_date->format('Y-m-d') }}</p>
            <p><strong>Medication:</strong> {{ $medicationName }}</p>
            <p><strong>Affected Lot Number:</strong> <span style="font-size: 1.2em; font-weight: bold; color: #d9534f;">{{ $lotNumber }}</span></p>
        </div>

        <p><strong>Recommended Action:</strong></p>
        <p>Please stop taking this medication immediately. Contact your healthcare provider or our pharmacy as soon as possible for further instructions and to arrange a safe replacement.</p>

        <p>We apologize for any inconvenience this may cause and prioritize your safety above all else.</p>

        <br>
        <p>Sincerely,</p>
        <p><strong>Pharmacovigilance Department</strong><br>
        Compounding Pharmacy</p>
    </div>
</body>
</html>
