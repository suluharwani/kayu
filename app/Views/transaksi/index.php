<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Transaksi</h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-plus mr-1"></i> Transaksi Baru
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" role="menu">
                                <a href="/transaksi/masuk" class="dropdown-item"><i class="fas fa-arrow-down mr-2"></i> Masuk Gudang</a>
                                <a href="/transaksi/keluar" class="dropdown-item"><i class="fas fa-arrow-up mr-2"></i> Keluar Gudang</a>
                                <a href="/transaksi/mutasi" class="dropdown-item"><i class="fas fa-exchange-alt mr-2"></i> Mutasi Gudang</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if(session()->getFlashdata('message')): ?>
                        <div class="alert alert-success">
                            <?= session()->getFlashdata('message'); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter_tanggal">Tanggal</label>
                                <input type="date" class="form-control" id="filter_tanggal" name="filter_tanggal">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter_jenis">Jenis Transaksi</label>
                                <select class="form-control" id="filter_jenis" name="filter_jenis">
                                    <option value="">Semua Jenis</option>
                                    <option value="masuk">Masuk Gudang</option>
                                    <option value="keluar">Keluar Gudang</option>
                                    <option value="mutasi">Mutasi Gudang</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter_gudang">Gudang</label>
                                <select class="form-control" id="filter_gudang" name="filter_gudang">
                                    <option value="">Semua Gudang</option>
                                    <?php foreach($gudang_list as $gudang): ?>
                                        <option value="<?= $gudang['id_gudang']; ?>"><?= $gudang['nama_gudang']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button class="btn btn-primary btn-block" id="btn-filter">
                                    <i class="fas fa-filter mr-1"></i> Filter
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="tableTransaksi">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Kode Transaksi</th>
                                    <th>Jenis</th>
                                    <th>Gudang Asal</th>
                                    <th>Gudang Tujuan</th>
                                    <th>Tanggal</th>
                                    <th>Operator</th>
                                    <th>Total Item</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php foreach($transaksi as $t): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= $t['kode_transaksi']; ?></td>
                                    <td>
                                        <?php 
                                        switch($t['jenis_transaksi']) {
                                            case 'masuk': 
                                                echo '<span class="badge badge-success">Masuk</span>';
                                                break;
                                            case 'keluar': 
                                                echo '<span class="badge badge-danger">Keluar</span>';
                                                break;
                                            case 'mutasi': 
                                                echo '<span class="badge badge-info">Mutasi</span>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td><?= $t['gudang_asal'] ?? '-'; ?></td>
                                    <td><?= $t['gudang_tujuan'] ?? '-'; ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($t['tanggal_transaksi'])); ?></td>
                                    <td><?= $t['operator']; ?></td>
                                    <td class="text-center">
                                        <?php 
                                        $total_item = array_reduce($t['detail'], function($carry, $item) {
                                            return $carry + $item['quantity'];
                                        }, 0);
                                        echo $total_item;
                                        ?>
                                    </td>
                                    <td>
                                        <a href="/transaksi/detail/<?= $t['id_transaksi']; ?>" class="btn btn-sm btn-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/transaksi/print/<?= $t['id_transaksi']; ?>" class="btn btn-sm btn-secondary" title="Cetak" target="_blank">
                                            <i class="fas fa-print"></i>
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

<?= $this->section('scripts'); ?>
<script>
$(document).ready(function() {
    // Inisialisasi DataTable
    var table = $('#tableTransaksi').DataTable({
        "order": [[5, "desc"]],
        "columnDefs": [
            { "orderable": false, "targets": [0, 8] }
        ]
    });
    
    // Filter tanggal
    $('#filter_tanggal').change(function() {
        table.columns(5).search(this.value).draw();
    });
    
    // Filter jenis transaksi
    $('#filter_jenis').change(function() {
        table.columns(2).search(this.value).draw();
    });
    
    // Filter gudang
    $('#filter_gudang').change(function() {
        var gudangId = this.value;
        table.columns([3, 4]).search(gudangId).draw();
    });
    
    // Reset filter
    $('#btn-reset').click(function() {
        $('#filter_tanggal, #filter_jenis, #filter_gudang').val('');
        table.search('').columns().search('').draw();
    });
    
    // Tooltip
    $('[title]').tooltip();
});
</script>
<?= $this->endSection(); ?>