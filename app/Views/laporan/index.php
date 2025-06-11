<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Menu Laporan</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Laporan Transaksi</h3>
                                </div>
                                <div class="card-body text-center">
                                    <p>Laporan seluruh transaksi masuk, keluar, dan mutasi</p>
                                    <a href="/laporan/transaksi" class="btn btn-primary">
                                        <i class="fas fa-file-alt mr-1"></i> Buka Laporan
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-success">
                                <div class="card-header">
                                    <h3 class="card-title">Laporan Stock</h3>
                                </div>
                                <div class="card-body text-center">
                                    <p>Laporan stock kayu per gudang dan kategori</p>
                                    <a href="/laporan/stock" class="btn btn-success">
                                        <i class="fas fa-boxes mr-1"></i> Buka Laporan
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-info">
                                <div class="card-header">
                                    <h3 class="card-title">Laporan Mutasi</h3>
                                </div>
                                <div class="card-body text-center">
                                    <p>Laporan mutasi antar gudang</p>
                                    <a href="/laporan/mutasi" class="btn btn-info">
                                        <i class="fas fa-exchange-alt mr-1"></i> Buka Laporan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>