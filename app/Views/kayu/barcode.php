
<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container">
    <div class="row">
        <div class="col text-center">
            <h2 class="mt-2">Barcode Kayu</h2>
            <h4><?= $kayu['kode_kayu']; ?></h4>
            
            <div class="my-4">
                <img src="/barcode/generate/<?= $kayu['barcode']; ?>" alt="Barcode" class="img-fluid">
            </div>
            
            <div class="mb-4">
                <p><strong>Jenis:</strong> <?= $kayu['nama_jenis']; ?></p>
                <p><strong>Dimensi:</strong> <?= $kayu['panjang']; ?> x <?= $kayu['lebar']; ?> x <?= $kayu['tebal']; ?> cm</p>
                <p><strong>Volume:</strong> <?= number_format($kayu['volume'], 4); ?> m3</p>
                <p><strong>Grade:</strong> <?= $kayu['grade']; ?></p>
                <p><strong>Kualitas:</strong> <?= $kayu['kualitas']; ?></p>
            </div>
            
            <a href="/kayu/print-barcode/<?= $kayu['id_kayu']; ?>" class="btn btn-primary" target="_blank">
                <i class="fas fa-print mr-1"></i> Cetak Barcode
            </a>
            <a href="/kayu" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>