<!DOCTYPE html>
<html>
<head>
    <title>Laporan Stock Kayu</title>
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
        <h2>Laporan Stock Kayu</h2>
        <p>Gudang: <?= $selected_gudang == 'all' ? 'Semua Gudang' : $gudang[$selected_gudang]['nama_gudang'] ?></p>
        <p>Kategori: <?= $kategori == 'all' ? 'Semua Kategori' : $kategori ?></p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Gudang</th>
                <th>Kode Kayu</th>
                <th>Jenis Kayu</th>
                <th>Kategori</th>
                <th>Quantity</th>
                <th>Total Volume (m3)</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach($stock as $s): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $s['nama_gudang'] ?></td>
                <td><?= $s['kode_kayu'] ?></td>
                <td><?= $s['nama_jenis'] ?></td>
                <td><?= $s['kategori'] ?></td>
                <td><?= $s['quantity'] ?></td>
                <td><?= number_format($s['volume'] * $s['quantity'], 4) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="footer">
        <p>Dicetak pada: <?= date('d/m/Y H:i:s') ?></p>
    </div>
</body>
</html>