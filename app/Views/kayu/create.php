<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tambah Data Kayu (Tanpa Stock)</h3>
                </div>
                <div class="card-body">
                    <form action="/kayu/store" method="post">
                        <?= csrf_field(); ?>
                        
                        <div class="form-group row">
                            <label for="id_jenis" class="col-sm-2 col-form-label">Jenis Kayu</label>
                            <div class="col-sm-10">
                                <select class="form-control" id="id_jenis" name="id_jenis" required>
                                    <option value="">-- Pilih Jenis Kayu --</option>
                                    <?php foreach($jenis_kayu as $jk): ?>
                                        <option value="<?= $jk['id_jenis']; ?>"><?= $jk['kode_jenis']; ?> - <?= $jk['nama_jenis']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Dimensi (cm)</label>
                            <div class="col-sm-10">
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="number" step="0.01" class="form-control" name="panjang" placeholder="Panjang" required>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" step="0.01" class="form-control" name="lebar" placeholder="Lebar" required>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" step="0.01" class="form-control" name="tebal" placeholder="Tebal" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="grade" class="col-sm-2 col-form-label">Part</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="grade" name="grade" required>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="kualitas" class="col-sm-2 col-form-label">Kualitas</label>
                            <div class="col-sm-10">
                                <select class="form-control" id="kualitas" name="kualitas" required>
                                    <option value="A">A (Terbaik)</option>
                                    <option value="B">B (Baik)</option>
                                    <option value="C">C (Cukup)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <div class="col-sm-10 offset-sm-2">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="/kayu" class="btn btn-secondary">Kembali</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>