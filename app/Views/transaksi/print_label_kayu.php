<!DOCTYPE html>
<html>
<head>
    <title>Label Kayu</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .label {
            display: inline-block;
            width: 50mm;
            height: 45mm;
            margin: 2mm;
            border: 1px dashed #ccc;
            padding: 2mm;
            box-sizing: border-box;
            vertical-align: top;
            font-size: 7pt;
        }

        .header {
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 2mm;
        }

        .barcode {
            text-align: center;
            margin: 2mm 0;
        }

        .barcode img {
            width: 50px;
            height: 50px;
        }

        .info {
            font-size: 7pt;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
        }

        /* Break after every 15 labels (3x5) */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
<?php 
$counter = 0;
foreach($transaksi['detail'] as $item): 
    for($i = 0; $i < $item['quantity']; $i++): 
        $counter++;
?>
    <div class="label">
        <div class="header">
            <?= $transaksi['kode_transaksi'] ?> - <?= $item['kode_kayu'] ?>
        </div>

        <div class="barcode">
            <img src="data:image/png;base64,<?= $item['qrcode_base64'] ?>" alt="QR Code">
        </div>

        <div class="info">
            <div class="info-row">
                <span>Barcode:</span>
                <span><?= $item['barcode'] ?></span>
            </div>
            <div class="info-row">
                <span>Part:</span>
                <span><?= $item['grade'] ?></span>
            </div>
            <div class="info-row">
                <span>Jenis:</span>
                <span><?= $item['nama_jenis'] ?></span>
            </div>
            <div class="info-row">
                <span>Dimensi:</span>
                <span><?= $item['panjang'] ?>x<?= $item['lebar'] ?>x<?= $item['tebal'] ?> cm</span>
            </div>
            <div class="info-row">
                <span>Volume:</span>
                <span><?= number_format($item['volume'], 4) ?> m³</span>
            </div>
            <div class="info-row">
                <span>Tanggal:</span>
                <span><?= date('d/m/Y', strtotime($transaksi['tanggal_transaksi'])) ?></span>
            </div>
        </div>
    </div>
<?php 
        // Tambah page break setiap 15 label
        if ($counter % 15 == 0) echo '<div class="page-break"></div>';
    endfor; 
endforeach; 
?>
</body>
</html>
