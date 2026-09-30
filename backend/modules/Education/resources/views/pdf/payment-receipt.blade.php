<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu de paiement</title>
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 3px solid #2DAC07;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 20px;
            font-weight: bold;
            color: #2DAC07;
        }
        .receipt-number {
            font-size: 14px;
            color: #666;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #0B5A31;
            text-align: center;
            margin: 20px 0;
            text-transform: uppercase;
        }
        .info-section {
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            width: 180px;
            font-weight: bold;
            color: #666;
        }
        .info-value {
            flex: 1;
        }
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .payment-table th {
            background-color: #2DAC07;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .payment-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
            font-size: 16px;
        }
        .footer-note {
            margin-top: 40px;
            padding: 15px;
            background-color: #f0f0f0;
            border-left: 4px solid #2DAC07;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="logo">🕌 Nujum Al-Huda</div>
            <div style="font-size: 10px; color: #999;">Institut d'enseignement islamique</div>
        </div>
        <div style="text-align: right;">
            <div class="receipt-number">Reçu N° {{ $receipt_number }}</div>
            <div style="font-size: 12px; color: #666;">Date: {{ $issued_date }}</div>
        </div>
    </div>

    <h1 class="title">Reçu de Paiement</h1>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Nom de l'étudiant :</div>
            <div class="info-value">{{ $student_name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Email :</div>
            <div class="info-value">{{ $student_email }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Programme :</div>
            <div class="info-value">{{ $program_name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Date de paiement :</div>
            <div class="info-value">{{ $payment_date }}</div>
        </div>
    </div>

    <table class="payment-table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: right;">Montant</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Frais de scolarité</td>
                <td style="text-align: right;">{{ number_format($tuition_amount, 0, ',', ' ') }} {{ $currency }}</td>
            </tr>
            <tr>
                <td>Frais d'inscription</td>
                <td style="text-align: right;">{{ number_format($registration_amount, 0, ',', ' ') }} {{ $currency }}</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL</td>
                <td style="text-align: right;">{{ number_format($total_amount, 0, ',', ' ') }} {{ $currency }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer-note">
        <p><strong>Mode de paiement :</strong> Espèces / Virement bancaire / Mobile Money</p>
        <p><strong>Note :</strong> Ce reçu fait office de preuve de paiement. Veuillez le conserver soigneusement.</p>
        <p style="margin-top: 15px; font-style: italic;">
            En cas de questions concernant ce reçu, veuillez nous contacter à contact@nujumalhuda.com ou au +221 77 123 45 67.
        </p>
    </div>

    <div style="position: fixed; bottom: 1cm; left: 0; right: 0; text-align: center; font-size: 9px; color: #999;">
        <p>Nujum Al-Huda Institute Center — Dakar, Sénégal</p>
        <p>www.nujumalhuda.com • contact@nujumalhuda.com</p>
    </div>
</body>
</html>
