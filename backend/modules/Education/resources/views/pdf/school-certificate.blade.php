<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Certificat de scolarité</title>
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
            text-align: center;
            border-bottom: 3px solid #2DAC07;
            padding-bottom: 20px;
            margin-bottom: 40px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2DAC07;
            margin-bottom: 10px;
        }
        .title {
            font-size: 28px;
            font-weight: bold;
            color: #0B5A31;
            margin: 30px 0;
            text-transform: uppercase;
        }
        .content {
            text-align: justify;
            font-size: 14px;
            margin: 30px 0;
        }
        .student-name {
            font-size: 18px;
            font-weight: bold;
            color: #2DAC07;
            text-decoration: underline;
        }
        .info-box {
            background-color: #f9f9f9;
            border-left: 4px solid #2DAC07;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 60px;
            text-align: right;
        }
        .signature {
            margin-top: 40px;
            border-top: 1px solid #333;
            padding-top: 10px;
            width: 250px;
            display: inline-block;
        }
        .arabic {
            font-family: 'Amiri', serif;
            direction: rtl;
            text-align: center;
            font-size: 20px;
            color: #0B5A31;
            margin: 20px 0;
        }
        .attendance {
            display: inline-block;
            padding: 8px 15px;
            background-color: #2DAC07;
            color: white;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">🕌 Nujum Al-Huda Institute Center</div>
        <div class="arabic">مركز نجوم الهدى للتعليم والتربية الإسلامية</div>
        <div style="font-size: 12px; color: #666;">Institut franco-anglo-arabe d'enseignement islamique</div>
        <div style="font-size: 11px; color: #999; margin-top: 5px;">Dakar, Sénégal • contact@nujumalhuda.com</div>
    </div>

    <h1 class="title">Certificat de Scolarité</h1>

    <div class="content">
        <p>Le Directeur de l'Institut Nujum Al-Huda certifie que :</p>

        <p style="text-align: center; margin: 30px 0;">
            <span class="student-name">{{ $student_name }}</span>
        </p>

        <p>est inscrit(e) et suit régulièrement les cours du programme suivant :</p>

        <div class="info-box">
            <p><strong>Programme :</strong> {{ $program_name }}</p>
            <p><strong>Promotion :</strong> {{ $promotion_name }}</p>
            <p><strong>Année académique :</strong> {{ $academic_year }}-{{ $academic_year + 1 }}</p>
            <p><strong>Date de début :</strong> {{ $start_date }}</p>
            <p><strong>Taux d'assiduité :</strong> <span class="attendance">{{ $attendance_rate }}%</span></p>
        </div>

        <p>L'étudiant(e) suit les enseignements avec assiduité et participe activement aux activités pédagogiques de l'institut.</p>

        <p>Ce certificat est délivré à l'intéressé(e) pour servir et valoir ce que de droit.</p>
    </div>

    <div class="footer">
        <p>Fait à Dakar, le {{ $issued_date }}</p>
        <div class="signature">
            <p><strong>Le Directeur</strong></p>
            <p style="font-style: italic; font-size: 12px;">(Signature et cachet)</p>
        </div>
    </div>

    <div style="position: fixed; bottom: 1cm; left: 0; right: 0; text-align: center; font-size: 10px; color: #999;">
        <p>Foi — Savoir — Éducation — Éthique — Excellence</p>
        <p>www.nujumalhuda.com</p>
    </div>
</body>
</html>
