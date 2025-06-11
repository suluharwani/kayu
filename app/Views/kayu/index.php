<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Kayu</h3>
                    <div class="card-tools">
                        <a href="/kayu/create" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus mr-1"></i> Tambah Kayu
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if(session()->getFlashdata('message')): ?>
                        <div class="alert alert-success">
                            <?= session()->getFlashdata('message'); ?>
                        </div>
                    <?php endif; ?>
                    <?php if(session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error'); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="tableKayu">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Kode Kayu</th>
                                    <th>Jenis Kayu</th>
                                    <th>Dimensi (cm)</th>
                                    <th>Volume (m3)</th>
                                    <th>Part</th>
                                    <th>Kualitas</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php foreach($kayu as $k): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= $k['kode_kayu']; ?></td>
                                    <td><?= $k['nama_jenis']; ?></td>
                                    <td><?= $k['panjang']; ?> x <?= $k['lebar']; ?> x <?= $k['tebal']; ?></td>
                                    <td><?= number_format($k['volume'], 4); ?></td>
                                    <td><?= $k['grade']; ?></td>
                                    <td><?= $k['kualitas']; ?></td>
                                    <td>
                                        <a href="/kayu/edit/<?= $k['id_kayu']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="/kayu/barcode/<?= $k['id_kayu']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-barcode"></i>
                                        </a>
                                        <a href="/kayu/print-qrcode/<?= $k['id_kayu']; ?>" class="btn btn-sm btn-success" target="_blank">
    <i class="fas fa-qrcode"></i> QR
</a>
                                        <form action="/kayu/delete/<?= $k['id_kayu']; ?>" method="post" class="d-inline">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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

<?= $this->section('scripts'); ?>
<script>
$(document).ready(function() {
    $('#tableKayu').DataTable();
});
</script>
<?= $this->endSection(); ?>