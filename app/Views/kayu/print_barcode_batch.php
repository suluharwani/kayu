<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10mm;
        }
        .label-sheet {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* 3 kolom */
            gap: 5mm;
        }
        .label-container {
            border: 1px dashed #ccc;
            padding: 2mm;
            height: 38mm;
            box-sizing: border-box;
        }
        .barcode-header {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 2mm;
        }
        .barcode-image {
            display: block;
            margin: 0 auto;
            width: 100%;
            max-height: 20mm;
        }
        .barcode-footer {
            text-align: center;
            font-size: 8pt;
            margin-top: 2mm;
        }
    </style>
</head>
<body>
    <div class="label-sheet">
        <?php foreach ($kayuList as $kayu): ?>
        <div class="label-container">
            <div class="barcode-header">
                <?= $kayu['kode_kayu'] ?><br>
                <?= substr($kayu['nama_jenis'], 0, 20) ?>
            </div>
            
            <img class="barcode-image" src="<?= $kayu['barcodeImage'] ?>">
            
            <div class="barcode-footer">
                <?= $kayu['panjang'] ?>x<?= $kayu['lebar'] ?>x<?= $kayu['tebal'] ?>cm<br>
                <?= date('d/m/Y') ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>