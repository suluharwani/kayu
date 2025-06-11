<?= $this->extend('layout/auth_template'); ?>

<?= $this->section('content'); ?>
<div class="register-box">
    <div class="register-logo">
        <h1>Registrasi Akun Baru</h1>
    </div>
    <div class="card">
        <div class="card-body register-card-body">
            <p class="login-box-msg">Silakan isi form untuk mendaftar</p>

            <?php if(session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach(session()->getFlashdata('errors') as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="/auth/attemptRegister" method="post">
                <?= csrf_field(); ?>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="username" placeholder="Username" value="<?= old('username'); ?>">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" name="pass_confirm" placeholder="Konfirmasi Password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="nama_lengkap" placeholder="Nama Lengkap" value="<?= old('nama_lengkap'); ?>">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user-tag"></span>
                        </div>
                    </div>
                </div>
                <?php if(session()->get('role') === 'admin'): ?>
                <div class="input-group mb-3">
                    <select class="form-control" name="role">
                        <option value="gudang">Staff Gudang</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                <?php endif; ?>
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="agreeTerms" name="terms" value="agree">
                            <label for="agreeTerms">
                                Saya setuju dengan <a href="#">syarat dan ketentuan</a>
                            </label>
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Daftar</button>
                    </div>
                </div>
            </form>

            <a href="/login" class="text-center">Sudah punya akun? Login disini</a>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>