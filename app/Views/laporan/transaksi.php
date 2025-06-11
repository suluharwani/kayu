<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Laporan Transaksi</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                            <i class="fas fa-print mr-1"></i> Cetak
                        </button>
                        <a href="/laporan/print/transaksi?<?= $_SERVER['QUERY_STRING'] ?>" class="btn btn-sm btn-success" target="_blank">
                            <i class="fas fa-file-pdf mr-1"></i> PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="get" action="/laporan/transaksi">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label>Tanggal Mulai</label>
                                <input type="date" class="form-control" name="start_date" value="<?= $start_date ?>">
                            </div>
                            <div class="col-md-3">
                                <label>Tanggal Akhir</label>
                                <input type="date" class="form-control" name="end_date" value="<?= $end_date ?>">
                            </div>
                            <div class="col-md-3">
                                <label>Jenis Transaksi</label>
                                <select class="form-control" name="jenis">
                                    <option value="all" <?= $jenis == 'all' ? 'selected' : '' ?>>Semua</option>
                                    <option value="masuk" <?= $jenis == 'masuk' ? 'selected' : '' ?>>Masuk</option>
                                    <option value="keluar" <?= $jenis == 'keluar' ? 'selected' : '' ?>>Keluar</option>
                                    <option value="mutasi" <?= $jenis == 'mutasi' ? 'selected' : '' ?>>Mutasi</option>
                                </select>
                            </div>
                            <div class="col-md-3">
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
                                    <th>Kode Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Gudang Asal</th>
                                    <th>Gudang Tujuan</th>
                                    <th>Operator</th>
                                    <th>Detail</th>
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
                                    <td><?= $trx['operator'] ?></td>
                                    <td>
                                        <a href="/transaksi/detail/<?= $trx['id_transaksi'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
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