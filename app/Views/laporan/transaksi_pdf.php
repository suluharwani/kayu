<!DOCTYPE html>
<html>
<head>
    <title>Laporan Transaksi</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; text-align: left; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { margin-top: 20px; text-align: right; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Transaksi</h2>
        <p>Periode: <?= date('d/m/Y', strtotime($start_date)) ?> - <?= date('d/m/Y', strtotime($end_date)) ?></p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Transaksi</th>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Gudang Asal</th>
                <th>Gudang Tujuan</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach($transaksi as $trx): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $trx['kode_transaksi'] ?></td>
                <td><?= date('d/m/Y H:i', strtotime($trx['tanggal_transaksi'])) ?></td>
                <td><?= ucfirst($trx['jenis_transaksi']) ?></td>
                <td><?= $trx['gudang_asal'] ?? '-' ?></td>
                <td><?= $trx['gudang_tujuan'] ?? '-' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="footer">
        <p>Dicetak pada: <?= date('d/m/Y H:i:s') ?></p>
    </div>
</body>
</html>