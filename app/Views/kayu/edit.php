<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Data Kayu - <?= $kayu['kode_kayu'] ?></h3>
                    <div class="card-tools">
                        <a href="/kayu" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if(session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger">
                            <ul>
                                <?php foreach(session()->getFlashdata('errors') as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="/kayu/update/<?= $kayu['id_kayu'] ?>" method="post">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="_method" value="PUT">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kode_kayu">Kode Kayu</label>
                                    <input type="text" class="form-control" id="kode_kayu" name="kode_kayu" 
                                        value="<?= old('kode_kayu', $kayu['kode_kayu']) ?>" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="id_jenis">Jenis Kayu</label>
                                    <select class="form-control" id="id_jenis" name="id_jenis" required>
                                        <option value="">-- Pilih Jenis Kayu --</option>
                                        <?php foreach($jenis_kayu as $jk): ?>
                                            <option value="<?= $jk['id_jenis'] ?>" 
                                                <?= old('id_jenis', $kayu['id_jenis']) == $jk['id_jenis'] ? 'selected' : '' ?>>
                                                <?= $jk['nama_jenis'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="panjang">Panjang (cm)</label>
                                    <input type="number" step="0.01" class="form-control" id="panjang" name="panjang" 
                                        value="<?= old('panjang', $kayu['panjang']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lebar">Lebar (cm)</label>
                                    <input type="number" step="0.01" class="form-control" id="lebar" name="lebar" 
                                        value="<?= old('lebar', $kayu['lebar']) ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="tebal">Tebal (cm)</label>
                                    <input type="number" step="0.01" class="form-control" id="tebal" name="tebal" 
                                        value="<?= old('tebal', $kayu['tebal']) ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="volume">Volume (m<sup>3</sup>)</label>
                                    <input type="text" class="form-control" id="volume" name="volume" 
                                        value="<?= old('volume', $kayu['volume']) ?>" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="grade">Grade</label>
                                    <input type="text" class="form-control" id="grade" name="grade" 
                                        value="<?= old('grade', $kayu['grade']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="kualitas">Kualitas</label>
                                    <select class="form-control" id="kualitas" name="kualitas" required>
                                        <option value="A" <?= old('kualitas', $kayu['kualitas']) == 'A' ? 'selected' : '' ?>>A (Terbaik)</option>
                                        <option value="B" <?= old('kualitas', $kayu['kualitas']) == 'B' ? 'selected' : '' ?>>B (Baik)</option>
                                        <option value="C" <?= old('kualitas', $kayu['kualitas']) == 'C' ? 'selected' : '' ?>>C (Cukup)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="barcode">Barcode</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="barcode" name="barcode" 
                                            value="<?= old('barcode', $kayu['barcode']) ?>" readonly>
                                        <div class="input-group-append">
                                            <a href="/kayu/barcode/<?= $kayu['id_kayu'] ?>" class="btn btn-outline-secondary" target="_blank">
                                                <i class="fas fa-barcode"></i> Lihat
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Simpan Perubahan
                            </button>
                            <a href="/kayu" class="btn btn-secondary ml-2">
                                <i class="fas fa-times mr-1"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
$(document).ready(function() {
    // Hitung volume otomatis saat dimensi diubah
    function hitungVolume() {
        var panjang = parseFloat($('#panjang').val()) || 0;
        var lebar = parseFloat($('#lebar').val()) || 0;
        var tebal = parseFloat($('#tebal').val()) || 0;
        
        // Volume dalam meter kubik (panjang x lebar x tebal / 1,000,000)
        var volume = (panjang * lebar * tebal) / 1000000;
        
        $('#volume').val(volume.toFixed(4));
    }
    
    $('#panjang, #lebar, #tebal').on('input', hitungVolume);
    
    // Hitung volume saat pertama kali load
    hitungVolume();
    
    // Validasi form sebelum submit
    $('form').submit(function() {
        var isValid = true;
        
        // Validasi dimensi harus lebih dari 0
        $('input[name="panjang"], input[name="lebar"], input[name="tebal"]').each(function() {
            if (parseFloat($(this).val()) <= 0) {
                alert('Dimensi harus lebih besar dari 0');
                isValid = false;
                return false;
            }
        });
        
        return isValid;
    });
});
</script>
<?= $this->endSection(); ?>