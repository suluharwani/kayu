<?php
use App\Models\TransaksiModel;
$transaksiModel = new TransaksiModel();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cetak Transaksi <?= $transaksi['kode_transaksi'] ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
        }
        .info {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">FAKTUR TRANSAKSI</div>
        <div>Manajemen Stock Kayu</div>
    </div>

    <div class="info">
        <table>
            <tr>
                <td width="20%">Kode Transaksi</td>
                <td width="30%">: <?= $transaksi['kode_transaksi'] ?></td>
                <td width="20%">Tanggal</td>
                <td width="30%">: <?= date('d/m/Y H:i', strtotime($transaksi['tanggal_transaksi'])) ?></td>
            </tr>
            <tr>
                <td>Jenis Transaksi</td>
                <td>: 
                    <?php 
                    switch($transaksi['jenis_transaksi']) {
                        case 'masuk': echo 'Masuk Gudang'; break;
                        case 'keluar': echo 'Keluar Gudang'; break;
                        case 'mutasi': echo 'Mutasi Gudang'; break;
                    }
                    ?>
                </td>
                <td>Operator</td>
                <td>: <?= $transaksi['operator'] ?></td>
            </tr>
            <?php if($transaksi['jenis_transaksi'] == 'mutasi'): ?>
            <tr>
                <td>Gudang Asal</td>
                <td>: <?= $transaksi['gudang_asal'] ?></td>
                <td>Gudang Tujuan</td>
                <td>: <?= $transaksi['gudang_tujuan'] ?></td>
            </tr>
            <?php elseif($transaksi['jenis_transaksi'] == 'masuk'): ?>
            <tr>
                <td>Gudang Tujuan</td>
                <td colspan="3">: <?= $transaksi['gudang_tujuan'] ?></td>
            </tr>
            <?php elseif($transaksi['jenis_transaksi'] == 'keluar'): ?>
            <tr>
                <td>Gudang Asal</td>
                <td colspan="3">: <?= $transaksi['gudang_asal'] ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td>Keterangan</td>
                <td colspan="3">: <?= $transaksi['keterangan'] ?? '-' ?></td>
            </tr>
        </table>
    </div>

    <div class="detail">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Kayu</th>
                    <th>Jenis Kayu</th>
                    <th>Dimensi (cm)</th>
                    <th>Volume (m3)</th>
                    <th>Qty</th>
                    <th>Total Volume</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; $total_volume = 0; $total_qty = 0; ?>
                <?php foreach($detail as $d): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= $d['kode_kayu'] ?></td>
                    <td><?= $d['nama_jenis'] ?></td>
                    <td class="text-center"><?= $d['panjang'] ?> x <?= $d['lebar'] ?> x <?= $d['tebal'] ?></td>
                    <td class="text-right"><?= number_format($d['volume'], 4) ?></td>
                    <td class="text-center"><?= $d['quantity'] ?></td>
                    <td class="text-right"><?= number_format($d['volume'] * $d['quantity'], 4) ?></td>
                </tr>
                <?php 
                    $total_volume += $d['volume'] * $d['quantity'];
                    $total_qty += $d['quantity'];
                ?>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-right">Total</th>
                    <th class="text-center"><?= $total_qty ?></th>
                    <th class="text-right"><?= number_format($total_volume, 4) ?></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer">
        <div style="margin-bottom: 50px;"></div>
        <div>
            <?= date('d F Y') ?>
        </div>
        <div style="margin-top: 50px;">
            <strong><?= session()->get('nama_lengkap') ?></strong>
        </div>
    </div>
</body>
</html>