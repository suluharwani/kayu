<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Laporan Stock</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                            <i class="fas fa-print mr-1"></i> Cetak
                        </button>
                        <a href="/laporan/print/stock?<?= $_SERVER['QUERY_STRING'] ?>" class="btn btn-sm btn-success" target="_blank">
                            <i class="fas fa-file-pdf mr-1"></i> PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="get" action="/laporan/stock">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label>Gudang</label>
                                <select class="form-control" name="id_gudang">
                                    <option value="all">Semua Gudang</option>
                                    <?php foreach($gudang as $g): ?>
                                    <option value="<?= $g['id_gudang'] ?>" <?= $selected_gudang == $g['id_gudang'] ? 'selected' : '' ?>>
                                        <?= $g['nama_gudang'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Kategori</label>
                                <select class="form-control" name="kategori">
                                    <option value="all">Semua Kategori</option>
                                    <option value="Kayu Solid" <?= $kategori == 'Kayu Solid' ? 'selected' : '' ?>>Kayu Solid</option>
                                    <option value="Kayu Lapis" <?= $kategori == 'Kayu Lapis' ? 'selected' : '' ?>>Kayu Lapis</option>
                                    <option value="Kayu Olahan" <?= $kategori == 'Kayu Olahan' ? 'selected' : '' ?>>Kayu Olahan</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>&nbsp;</label><br>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter mr-1"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Gudang</th>
                                    <th>Kode Kayu</th>
                                    <th>Jenis Kayu</th>
                                    <th>Kategori</th>
                                    <th>Dimensi (cm)</th>
                                    <th>Volume (m3)</th>
                                    <th>Quantity</th>
                                    <th>Total Volume</th>
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
                                    <td><?= $s['panjang'] ?>x<?= $s['lebar'] ?>x<?= $s['tebal'] ?></td>
                                    <td><?= number_format($s['volume'], 4) ?></td>
                                    <td><?= $s['quantity'] ?></td>
                                    <td><?= number_format($s['volume'] * $s['quantity'], 4) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>