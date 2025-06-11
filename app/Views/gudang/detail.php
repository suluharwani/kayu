<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detail Gudang: <?= $gudang['nama_gudang']; ?></h3>
                    <div class="card-tools">
                        <a href="/gudang" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">Kode Gudang</th>
                                    <td><?= $gudang['kode_gudang']; ?></td>
                                </tr>
                                <tr>
                                    <th>Nama Gudang</th>
                                    <td><?= $gudang['nama_gudang']; ?></td>
                                </tr>
                                <tr>
                                    <th>Alamat</th>
                                    <td><?= $gudang['alamat']; ?></td>
                                </tr>
                                <tr>
                                    <th>Kapasitas</th>
                                    <td><?= number_format($gudang['kapasitas']); ?> m3</td>
                                </tr>
                                <tr>
                                    <th>Penanggung Jawab</th>
                                    <td><?= $gudang['penanggung_jawab']; ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h5 class="card-title">Statistik Stock</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="60%">Total Item</th>
                                            <td><?= $stock_summary['total_item'] ?? 0; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Total Volume</th>
                                            <td><?= number_format($stock_summary['total_volume'] ?? 0, 4); ?> m3</td>
                                        </tr>
                                        <tr>
                                            <th>Kapasitas Tersedia</th>
                                            <td>
                                                <?php 
                                                $used = $stock_summary['total_volume'] ?? 0;
                                                $available = $gudang['kapasitas'] - $used;
                                                echo number_format($available, 4) . ' m3';
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Persentase Terisi</th>
                                            <td>
                                                <?php 
                                                $percentage = ($used / $gudang['kapasitas']) * 100;
                                                echo number_format($percentage, 2) . '%';
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h4 class="mt-4">Daftar Stock</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Kode Kayu</th>
                                    <th>Jenis Kayu</th>
                                    <th>Dimensi (cm)</th>
                                    <th>Volume (m3)</th>
                                    <th>Grade</th>
                                    <th>Kualitas</th>
                                    <th>Quantity</th>
                                    <th>Total Volume</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php foreach($stock as $s): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= $s['kode_kayu']; ?></td>
                                    <td><?= $s['nama_jenis']; ?></td>
                                    <td><?= $s['panjang']; ?> x <?= $s['lebar']; ?> x <?= $s['tebal']; ?></td>
                                    <td><?= number_format($s['volume'], 4); ?></td>
                                    <td><?= $s['grade']; ?></td>
                                    <td><?= $s['kualitas']; ?></td>
                                    <td class="<?= $s['quantity'] < 10 ? 'text-warning font-weight-bold' : ''; ?>">
                                        <?= $s['quantity']; ?>
                                    </td>
                                    <td><?= number_format($s['quantity'] * $s['volume'], 4); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="/transaksi/masuk?id_gudang=<?= $gudang['id_gudang']; ?>" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i> Transaksi Masuk
                    </a>
                    <a href="/transaksi/keluar?id_gudang=<?= $gudang['id_gudang']; ?>" class="btn btn-success">
                        <i class="fas fa-minus mr-1"></i> Transaksi Keluar
                    </a>
                    <a href="/transaksi/mutasi?asal=<?= $gudang['id_gudang']; ?>" class="btn btn-info">
                        <i class="fas fa-exchange-alt mr-1"></i> Mutasi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>