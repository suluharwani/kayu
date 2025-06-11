<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Jenis Kayu</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalTambah">
                            <i class="fas fa-plus mr-1"></i> Tambah Jenis Kayu
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
                        <table class="table table-bordered table-hover" id="tableJenisKayu">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Kode</th>
                                    <th>Nama Jenis</th>
                                    <th>Kategori</th>
                                    <th>Harga/m3</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php foreach($jenis_kayu as $jk): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= $jk['kode_jenis']; ?></td>
                                    <td><?= $jk['nama_jenis']; ?></td>
                                    <td><?= $jk['kategori']; ?></td>
                                    <td>Rp <?= number_format($jk['harga_per_volume'], 0, ',', '.'); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning btn-edit" data-id="<?= $jk['id_jenis']; ?>"
                                            data-nama="<?= $jk['nama_jenis']; ?>"
                                            data-kategori="<?= $jk['kategori']; ?>"
                                            data-deskripsi="<?= $jk['deskripsi']; ?>"
                                            data-harga="<?= $jk['harga_per_volume']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="/jenis-kayu/delete/<?= $jk['id_jenis']; ?>" method="post" class="d-inline">
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
                <h5 class="modal-title" id="modalTambahLabel">Tambah Jenis Kayu</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/jenis-kayu/create" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nama_jenis">Nama Jenis Kayu</label>
                        <input type="text" class="form-control" id="nama_jenis" name="nama_jenis" required>
                    </div>
                    <div class="form-group">
                        <label for="kategori">Kategori</label>
                        <select class="form-control" id="kategori" name="kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Kayu Solid">Kayu Solid</option>
                            <option value="Kayu Lapis">Kayu Lapis</option>
                            <option value="Kayu Olahan">Kayu Olahan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="harga_per_volume">Harga per m3</label>
                        <input type="number" class="form-control" id="harga_per_volume" name="harga_per_volume">
                    </div>
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
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
                <h5 class="modal-title" id="modalEditLabel">Edit Jenis Kayu</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/jenis-kayu/update" method="post">
                <?= csrf_field(); ?>
                <input type="hidden" name="id_jenis" id="edit_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_nama_jenis">Nama Jenis Kayu</label>
                        <input type="text" class="form-control" id="edit_nama_jenis" name="nama_jenis" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_kategori">Kategori</label>
                        <select class="form-control" id="edit_kategori" name="kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Kayu Solid">Kayu Solid</option>
                            <option value="Kayu Lapis">Kayu Lapis</option>
                            <option value="Kayu Olahan">Kayu Olahan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_harga_per_volume">Harga per m3</label>
                        <input type="number" class="form-control" id="edit_harga_per_volume" name="harga_per_volume">
                    </div>
                    <div class="form-group">
                        <label for="edit_deskripsi">Deskripsi</label>
                        <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3"></textarea>
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
        $('#edit_nama_jenis').val($(this).data('nama'));
        $('#edit_kategori').val($(this).data('kategori'));
        $('#edit_harga_per_volume').val($(this).data('harga'));
        $('#edit_deskripsi').val($(this).data('deskripsi'));
        $('#modalEdit').modal('show');
    });

    // Initialize DataTable
    $('#tableJenisKayu').DataTable();
});
</script>
<?= $this->endSection(); ?>