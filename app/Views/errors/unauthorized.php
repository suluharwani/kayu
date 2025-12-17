<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6 text-center">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h3><i class="fas fa-ban"></i> Akses Ditolak</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <h4 class="alert-heading">Anda tidak memiliki izin!</h4>
                        <p>Role Anda (<strong><?= session()->get('role') ?? 'Guest' ?></strong>) tidak memiliki akses ke halaman ini.</p>
                        <hr>
                        <p class="mb-0">Silakan hubungi administrator jika Anda membutuhkan akses.</p>
                    </div>
                    <a href="/dashboard" class="btn btn-primary">
                        <i class="fas fa-home"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>