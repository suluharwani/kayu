<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Laporan Mutasi Antar Gudang</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                            <i class="fas fa-print mr-1"></i> Cetak
                        </button>
                                                <a href="/laporan/print/mutasi?<?= $_SERVER['QUERY_STRING'] ?>" class="btn btn-sm btn-success" target="_blank">
                            <i class="fas fa-file-pdf mr-1"></i> PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="get" action="/laporan/mutasi">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label>Tanggal Mulai</label>
                                <input type="date" class="form-control" name="start_date" value="<?= $start_date ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Tanggal Akhir</label>
                                <input type="date" class="form-control" name="end_date" value="<?= $end_date ?>">
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
                                    <th>Kode Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Gudang Asal</th>
                                    <th>Gudang Tujuan</th>
                                    <th>Keterangan</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php foreach($mutasi as $m): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= $m['kode_transaksi'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($m['tanggal_transaksi'])) ?></td>
                                    <td><?= $m['gudang_asal'] ?></td>
                                    <td><?= $m['gudang_tujuan'] ?></td>
                                    <td><?= $m['keterangan'] ?? '-' ?></td>
                                    <td>
                                        <a href="/transaksi/detail/<?= $m['id_transaksi'] ?>" class="btn btn-sm btn-info">
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