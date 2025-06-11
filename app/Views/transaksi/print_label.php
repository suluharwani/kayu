<!DOCTYPE html>
<html>
<head>
    <title>Label Transaksi <?= $transaksi['kode_transaksi'] ?></title>
    <style>
        @page { margin: 0; padding: 0; size: A5 landscape; }
        body { 
            font-family: Arial, sans-serif;
            margin: 1cm;
        }
        .header { 
            text-align: center; 
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .qr-section {
            float: right;
            width: 150px;
            text-align: center;
        }
        .info-section {
            margin-right: 160px;
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .detail-table th, .detail-table td {
            border: 1px solid #000;
            padding: 5px;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>TRANSAKSI <?= strtoupper($transaksi['jenis_transaksi']) ?></h2>
        <h3><?= $transaksi['kode_transaksi'] ?></h3>
    </div>
    
    <div class="qr-section">
        <img src="<?= $qrCode ?>" alt="QR Code">
        <p>Scan untuk validasi</p>
    </div>
    
    <div class="info-section">
        <p><strong>Tanggal:</strong> <?= date('d/m/Y H:i', strtotime($transaksi['tanggal_transaksi'])) ?></p>
        <?php if($transaksi['jenis_transaksi'] == 'mutasi'): ?>
            <p><strong>Dari Gudang:</strong> <?= $transaksi['gudang_asal'] ?></p>
            <p><strong>Ke Gudang:</strong> <?= $transaksi['gudang_tujuan'] ?></p>
        <?php elseif($transaksi['jenis_transaksi'] == 'masuk'): ?>
            <p><strong>Gudang Tujuan:</strong> <?= $transaksi['gudang_tujuan'] ?></p>
        <?php else: ?>
            <p><strong>Gudang Asal:</strong> <?= $transaksi['gudang_asal'] ?></p>
        <?php endif; ?>
        <p><strong>Operator:</strong> <?= $transaksi['operator'] ?></p>
        <p><strong>Keterangan:</strong> <?= $transaksi['keterangan'] ?? '-' ?></p>
    </div>
    
    <table class="detail-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Kayu</th>
                <th>Jenis</th>
                <th>Dimensi</th>
                <th>Volume</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; $totalVolume = 0; $totalQuantity = 0; ?>
            <?php foreach($transaksi['detail'] as $item): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $item['kode_kayu'] ?></td>
                <td><?= $item['nama_jenis'] ?></td>
                <td><?= $item['panjang'] ?>x<?= $item['lebar'] ?>x<?= $item['tebal'] ?> cm</td>
                <td><?= number_format($item['volume'], 4) ?> m³</td>
                <td><?= $item['quantity'] ?></td>
            </tr>
            <?php 
                $totalVolume += ($item['volume'] * $item['quantity']);
                $totalQuantity += $item['quantity'];
            ?>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">TOTAL</th>
                <th><?= number_format($totalVolume, 4) ?> m³</th>
                <th><?= $totalQuantity ?></th>
            </tr>
        </tfoot>
    </table>
    
    <div class="footer">
        <p>Dicetak pada: <?= date('d/m/Y H:i:s') ?></p>
    </div>
</body>
</html>