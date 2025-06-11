<!DOCTYPE html>
<html>
<head>
    <title>QR Code <?= $kayu['kode_kayu'] ?></title>
    <style>
        @page {
            margin: 0;
            padding: 0;
            size: 57mm 32mm; /* Ukuran label */
        }
        body {
            margin: 0;
            padding: 2mm;
            font-family: Arial, sans-serif;
            width: 57mm;
            height: 32mm;
            box-sizing: border-box;
        }
        .label-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            height: 100%;
            border: 1px dashed #ccc; /* Garis bantu untuk pemotongan */
            padding: 2mm;
        }
        .label-header {
            text-align: center;
            width: 100%;
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 1mm;
        }
        .qr-code-container {
            width: 25mm;
            height: 25mm;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .qr-code-container img {
            max-width: 100%;
            max-height: 100%;
        }
        .label-footer {
            text-align: center;
            width: 100%;
            font-size: 8pt;
            margin-top: 1mm;
        }
        .kode-text {
            font-family: 'Courier New', monospace;
            font-size: 9pt;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="label-container">
        <div class="label-header">
            <?= substr($kayu['nama_jenis'], 0, 20) ?>
        </div>
        
        <div class="qr-code-container">
            <img src="<?= $qrCodeImage ?>">
        </div>
        
        <div class="label-footer">
            <div class="kode-text"><?= $kayu['kode_kayu'] ?></div>
            <div><?= $kayu['panjang'] ?>x<?= $kayu['lebar'] ?>x<?= $kayu['tebal'] ?>cm</div>
            <div><?= date('d/m/Y') ?></div>
        </div>
    </div>
</body>
</html>