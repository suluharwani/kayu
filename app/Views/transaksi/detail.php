<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container">
    <div class="row">
        <div class="col">
            <h2 class="mt-2">Detail Transaksi <?= $transaksi['kode_transaksi']; ?></h2>
            
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Kode Transaksi</th>
                                    <td><?= $transaksi['kode_transaksi']; ?></td>
                                </tr>
                                <tr>
                                    <th>Jenis Transaksi</th>
                                    <td>
                                        <?php 
                                        switch($transaksi['jenis_transaksi']) {
                                            case 'masuk': echo 'Masuk Gudang'; break;
                                            case 'keluar': echo 'Keluar Gudang'; break;
                                            case 'mutasi': echo 'Mutasi Antar Gudang'; break;
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tanggal</th>
                                    <td><?= date('d/m/Y H:i', strtotime($transaksi['tanggal_transaksi'])); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <?php if($transaksi['jenis_transaksi'] == 'masuk') : ?>
                                    <tr>
                                        <th>Gudang Tujuan</th>
                                        <td><?= $transaksi['gudang_tujuan']; ?></td>
                                    </tr>
                                <?php elseif($transaksi['jenis_transaksi'] == 'keluar') : ?>
                                    <tr>
                                        <th>Gudang Asal</th>
                                        <td><?= $transaksi['gudang_asal']; ?></td>
                                    </tr>
                                <?php else : ?>
                                    <tr>
                                        <th>Gudang Asal</th>
                                        <td><?= $transaksi['gudang_asal']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Gudang Tujuan</th>
                                        <td><?= $transaksi['gudang_tujuan']; ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <th>Operator</th>
                                    <td><?= $transaksi['operator']; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <?php if($transaksi['keterangan']) : ?>
                        <div class="alert alert-info">
                            <strong>Keterangan:</strong> <?= $transaksi['keterangan']; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <h4>Detail Barang</h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Kayu</th>
                            <th>Jenis Kayu</th>
                            <th>Dimensi (cm)</th>
                            <th>Volume (m3)</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; $totalVolume = 0; $totalQuantity = 0; ?>
                        <?php foreach($detail as $d) : ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= $d['kode_kayu']; ?></td>
                                <td><?= $d['nama_jenis']; ?></td>
                                <td><?= $d['panjang']; ?> x <?= $d['lebar']; ?> x <?= $d['tebal']; ?></td>
                                <td><?= number_format($d['volume'], 4); ?></td>
                                <td><?= $d['quantity']; ?></td>
                            </tr>
                            <?php 
                                $totalVolume += ($d['volume'] * $d['quantity']);
                                $totalQuantity += $d['quantity'];
                            ?>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4">Total</th>
                            <th><?= number_format($totalVolume, 4); ?> m3</th>
                            <th><?= $totalQuantity; ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="mt-3">
                <a href="/transaksi/print/<?= $transaksi['id_transaksi']; ?>" class="btn btn-primary" target="_blank">
                    <i class="fas fa-print"></i> Cetak Faktur
                </a>
                <div class="mt-3">
    <a href="/transaksi/print-label/<?= $transaksi['id_transaksi'] ?>" class="btn btn-primary" target="_blank">
        <i class="fas fa-print"></i> Cetak Label Transaksi
    </a>
    <a href="/transaksi/print-label-kayu/<?= $transaksi['id_transaksi'] ?>" class="btn btn-success" target="_blank">
        <i class="fas fa-barcode"></i> Cetak Label Kayu
    </a>
    <a href="/transaksi" class="btn btn-secondary">Kembali</a>
</div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>