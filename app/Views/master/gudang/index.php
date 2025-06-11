<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Gudang</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalTambah">
                            <i class="fas fa-plus mr-1"></i> Tambah Gudang
                        </button>
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
                        <table class="table table-bordered table-hover" id="tableGudang">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Kode</th>
                                    <th>Nama Gudang</th>
                                    <th>Alamat</th>
                                    <th>Kapasitas (m3)</th>
                                    <th>Penanggung Jawab</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php foreach($gudang as $g): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= $g['kode_gudang']; ?></td>
                                    <td><?= $g['nama_gudang']; ?></td>
                                    <td><?= $g['alamat']; ?></td>
                                    <td><?= number_format($g['kapasitas']); ?></td>
                                    <td><?= $g['penanggung_jawab']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning btn-edit" data-id="<?= $g['id_gudang']; ?>"
                                            data-nama="<?= $g['nama_gudang']; ?>"
                                            data-alamat="<?= $g['alamat']; ?>"
                                            data-kapasitas="<?= $g['kapasitas']; ?>"
                                            data-pj="<?= $g['penanggung_jawab']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="/gudang/delete/<?= $g['id_gudang']; ?>" method="post" class="d-inline">
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

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahLabel">Tambah Gudang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/gudang/create" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nama_gudang">Nama Gudang</label>
                        <input type="text" class="form-control" id="nama_gudang" name="nama_gudang" required>
                    </div>
                    <div class="form-group">
                        <label for="alamat">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="kapasitas">Kapasitas (m3)</label>
                        <input type="number" class="form-control" id="kapasitas" name="kapasitas" required>
                    </div>
                    <div class="form-group">
                        <label for="penanggung_jawab">Penanggung Jawab</label>
                        <input type="text" class="form-control" id="penanggung_jawab" name="penanggung_jawab">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditLabel">Edit Gudang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/gudang/update" method="post">
                <?= csrf_field(); ?>
                <input type="hidden" name="id_gudang" id="edit_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_nama_gudang">Nama Gudang</label>
                        <input type="text" class="form-control" id="edit_nama_gudang" name="nama_gudang" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_alamat">Alamat</label>
                        <textarea class="form-control" id="edit_alamat" name="alamat" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_kapasitas">Kapasitas (m3)</label>
                        <input type="number" class="form-control" id="edit_kapasitas" name="kapasitas" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_penanggung_jawab">Penanggung Jawab</label>
                        <input type="text" class="form-control" id="edit_penanggung_jawab" name="penanggung_jawab">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle edit button click
    $('.btn-edit').click(function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_nama_gudang').val($(this).data('nama'));
        $('#edit_alamat').val($(this).data('alamat'));
        $('#edit_kapasitas').val($(this).data('kapasitas'));
        $('#edit_penanggung_jawab').val($(this).data('pj'));
        $('#modalEdit').modal('show');
    });

    // Initialize DataTable
    $('#tableGudang').DataTable();
});
</script>
<?= $this->endSection(); ?>