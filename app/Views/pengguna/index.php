<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Pengguna</h3>
                    <div class="card-tools">
                        <a href="/pengguna/create" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus mr-1"></i> Tambah Pengguna
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if(session()->getFlashdata('message')): ?>
                        <div class="alert alert-success">
                            <?= session()->getFlashdata('message'); ?>
                        </div>
                    <?php endif; ?>
                    <?php if(session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('errors'); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="tablePengguna">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Username</th>
                                    <th>Nama Lengkap</th>
                                    <th>Role</th>
                                    <th>Terakhir Login</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php foreach($users as $user): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= $user['username']; ?></td>
                                    <td><?= $user['nama_lengkap']; ?></td>
                                    <td>
                                        <?php 
                                        switch($user['role']) {
                                            case 'admin': echo '<span class="badge badge-danger">Admin</span>'; break;
                                            case 'gudang': echo '<span class="badge badge-primary">Gudang</span>'; break;
                                            case 'manager': echo '<span class="badge badge-success">Manager</span>'; break;
                                        }
                                        ?>
                                    </td>
                                    <td><?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Belum pernah'; ?></td>
                                    <td>
                                        <a href="/pengguna/edit/<?= $user['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="/pengguna/delete/<?= $user['id']; ?>" method="post" class="d-inline">
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
    $('#tablePengguna').DataTable();
});
</script>
<?= $this->endSection(); ?>