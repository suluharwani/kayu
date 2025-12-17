<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Pengguna</h3>
                </div>
                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="card-body">
                    <form action="/pengguna/update/<?= $user['id']; ?>" method="post">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="_method" value="PUT">

                        <div class="form-group row">
                            <label for="username" class="col-sm-2 col-form-label">Username</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="username" name="username" value="<?= $user['username']; ?>" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-sm-2 col-form-label">Password</label>
                            <div class="col-sm-10">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Kosongkan jika tidak ingin diubah">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="pass_confirm" class="col-sm-2 col-form-label">Konfirmasi Password</label>
                            <div class="col-sm-10">
                                <input type="password" class="form-control" id="pass_confirm" name="pass_confirm" placeholder="Kosongkan jika tidak ingin diubah">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="nama_lengkap" class="col-sm-2 col-form-label">Nama Lengkap</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= $user['nama_lengkap']; ?>" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="role" class="col-sm-2 col-form-label">Role</label>
                            <div class="col-sm-10">
                                <select class="form-control" id="role" name="role" required>
                                    <option value="admin" <?= ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    <option value="gudang" <?= ($user['role'] == 'gudang') ? 'selected' : ''; ?>>Staff Gudang</option>
                                    <option value="manager" <?= ($user['role'] == 'manager') ? 'selected' : ''; ?>>Manager</option>
                                </select>
                            </div>
                        </div>
                        <!-- Tambahkan setelah field role -->
                        <div class="form-group row">
                            <label for="status" class="col-sm-2 col-form-label">Status</label>
                            <div class="col-sm-10">
                                <select class="form-control" id="status" name="status" required>
                                    <option value="active" <?= ($user['status'] == 'active') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="inactive" <?= ($user['status'] == 'inactive') ? 'selected' : ''; ?>>Nonaktif</option>
                                    <option value="pending" <?= ($user['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-10 offset-sm-2">
                                <button type="submit" class="btn btn-primary">Update</button>
                                <a href="/pengguna" class="btn btn-secondary">Kembali</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>