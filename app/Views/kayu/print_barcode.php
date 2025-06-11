<!DOCTYPE html>
<html>
<head>
    <title>Barcode <?= $kayu['kode_kayu'] ?></title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 5mm;
            width: 76mm;  /* Lebar label */
            height: 38mm; /* Tinggi label */
        }
        .label-container {
            width: 100%;
            height: 100%;
            border: 1px dashed #ccc; /* Garis bantu untuk pemotongan */
            padding: 2mm;
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
    <div class="label-container">
        <div class="barcode-header">
            <?= $kayu['kode_kayu'] ?><br>
            <?= substr($kayu['nama_jenis'], 0, 20) ?>
        </div>
        
        <img class="barcode-image" src="<?= $barcodeImage ?>">
        
        <div class="barcode-footer">
            <?= $kayu['panjang'] ?>x<?= $kayu['lebar'] ?>x<?= $kayu['tebal'] ?>cm<br>
            <?= date('d/m/Y') ?>
        </div>
    </div>
</body>
</html>