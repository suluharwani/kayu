<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        body {
            margin: 10mm;
            font-family: Arial, sans-serif;
        }
        .label-sheet {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 5mm;
        }
        .label-container {
            border: 1px solid #eee;
            padding: 2mm;
            height: 32mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
        }
        /* Gaya lainnya sama dengan single label */
    </style>
</head>
<body>
    <div class="label-sheet">
        <?php foreach ($kayuList as $kayu): ?>
        <div class="label-container">
            <div class="label-header">
                <?= substr($kayu['nama_jenis'], 0, 15) ?>
            </div>
            
            <div class="qr-code-container">
                <img src="<?= $kayu['qrCodeImage'] ?>">
            </div>
            
            <div class="label-footer">
                <div class="kode-text"><?= $kayu['kode_kayu'] ?></div>
                <div><?= $kayu['panjang'] ?>x<?= $kayu['lebar'] ?>x<?= $kayu['tebal'] ?>cm</div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>