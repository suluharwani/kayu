<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Total Gudang</h5>
                            <h2 class="mb-0"><?= number_format($total_gudang); ?></h2>
                        </div>
                        <i class="fas fa-warehouse fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="/gudang">Lihat Detail</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Stock Aman</h5>
                            <h2 class="mb-0"><?= count($stock_rendah) == 0 ? 'Ya' : 'Tidak'; ?></h2>
                        </div>
                        <i class="fas fa-check-circle fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="/laporan/stock">Lihat Detail</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Stock Rendah</h5>
                            <h2 class="mb-0"><?= count($stock_rendah); ?></h2>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-3x"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="/laporan/stock-rendah">Lihat Detail</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-history mr-2"></i>Transaksi Terakhir</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Kode</th>
                                    <th>Jenis</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($transaksi_terakhir as $t): ?>
                                <tr>
                                    <td>
                                        <a href="/transaksi/detail/<?= $t['id_transaksi']; ?>">
                                            <?= $t['kode_transaksi']; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php 
                                        switch($t['jenis_transaksi']) {
                                            case 'masuk': echo 'Masuk'; break;
                                            case 'keluar': echo 'Keluar'; break;
                                            case 'mutasi': echo 'Mutasi'; break;
                                        }
                                        ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($t['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-exclamation-circle mr-2"></i>Stock Rendah</h5>
                </div>
                <div class="card-body">
                    <?php if(count($stock_rendah) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Kayu</th>
                                        <th>Gudang</th>
                                        <th>Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($stock_rendah as $s): ?>
                                    <tr>
                                        <td><?= $s['kode_kayu']; ?></td>
                                        <td><?= $s['nama_gudang']; ?></td>
                                        <td class="<?= $s['quantity'] < 5 ? 'text-danger font-weight-bold' : 'text-warning'; ?>">
                                            <?= $s['quantity']; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle mr-2"></i> Semua stock dalam kondisi aman
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>