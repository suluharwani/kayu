<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container">
    <div class="row">
        <div class="col">
            <h2 class="mt-2">Transaksi Masuk Gudang</h2>
            <form id="formTransaksi" action="/transaksi/saveMasuk" method="post">
                <?= csrf_field(); ?>
                
                <!-- Input Kode Transaksi untuk mengambil data -->
                <div class="form-group row">
                    <label for="inputKodeTransaksi" class="col-sm-2 col-form-label">Kode Transaksi (Opsional)</label>
                    <div class="col-sm-10">
                        <div class="input-group">
                            <input type="text" class="form-control" id="inputKodeTransaksi" 
                                   placeholder="Masukkan kode transaksi (TM...) untuk mengambil item" autocomplete="off">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary" id="btnLoadTransaksi">
                                    Muat Transaksi
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Masukkan kode transaksi masuk (TM...) untuk mengambil item dari transaksi sebelumnya
                        </small>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="id_gudang" class="col-sm-2 col-form-label">Gudang Tujuan</label>
                    <div class="col-sm-10">
                        <select class="form-control" id="id_gudang" name="id_gudang" required>
                            <option value="">-- Pilih Gudang --</option>
                            <?php foreach($gudang as $g) : ?>
                                <option value="<?= $g['id_gudang']; ?>"><?= $g['nama_gudang']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="tanggal" class="col-sm-2 col-form-label">Tanggal</label>
                    <div class="col-sm-10">
                        <input type="datetime-local" class="form-control" id="tanggal" name="tanggal" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="keterangan" class="col-sm-2 col-form-label">Keterangan</label>
                    <div class="col-sm-10">
                        <textarea class="form-control" id="keterangan" name="keterangan"></textarea>
                    </div>
                </div>
                
                <h4 class="mt-4">Detail Barang</h4>
                <div class="alert alert-info">
                    <strong>Instruksi:</strong> 
                    <ul>
                        <li>Masukkan kode transaksi (TM...) untuk mengambil item dari transaksi sebelumnya</li>
                        <li>Atau masukkan kode kayu dan tekan <kbd>Enter</kbd> untuk menambahkan item. Quantity default = 1.</li>
                    </ul>
                </div>
                
                <div class="form-group row">
                    <label for="inputKode" class="col-sm-2 col-form-label">Kode Kayu</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="inputKode" placeholder="Masukkan kode kayu dan tekan Enter" autocomplete="off">
                    </div>
                </div>
                
                <div class="table-responsive mt-3">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th width="60%">Kode Kayu</th>
                                <th width="20%">Dimensi</th>
                                <th width="15%">Jumlah</th>
                                <th width="5%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTable">
                            <!-- Items akan ditambahkan di sini via JavaScript -->
                        </tbody>
                    </table>
                </div>
                
                <input type="hidden" id="items" name="items">
                
                <div class="form-group row mt-4">
                    <div class="col-sm-10 offset-sm-2">
                        <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                        <a href="/transaksi" class="btn btn-secondary">Kembali</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Format tanggal sekarang untuk input datetime-local
    let now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    document.getElementById('tanggal').value = now.toISOString().slice(0,16);
    
    // Cache untuk data kayu
    let kayuData = <?= json_encode($kayu); ?>;
    let kodeToIdMap = {};
    let addedItems = {}; // Untuk melacak item yang sudah ditambahkan
    
    // Membuat mapping kode kayu ke ID dan data lengkap
    kayuData.forEach(kayu => {
        kodeToIdMap[kayu.kode_kayu] = kayu.id_kayu;
    });
    
    // Handler untuk tombol muat transaksi
    $('#btnLoadTransaksi').click(function() {
        loadTransaksiItems();
    });
    
    // Handler untuk input kode transaksi (tekan enter)
    $('#inputKodeTransaksi').keydown(function(e) {
        if(e.key === 'Enter') {
            e.preventDefault();
            loadTransaksiItems();
        }
    });
    
    // Handler untuk input kode kayu
    $('#inputKode').keydown(function(e) {
        if(e.key === 'Enter') {
            e.preventDefault();
            processKodeInput();
        }
    });
    
    // Fungsi untuk memuat item dari transaksi
    function loadTransaksiItems() {
        const kodeTransaksi = $('#inputKodeTransaksi').val().trim();
        
        if(!kodeTransaksi) {
            alert('Harap masukkan kode transaksi!');
            return;
        }
        
        // Validasi format kode transaksi (harus dimulai dengan TM)
        if(!kodeTransaksi.startsWith('TM')) {
            alert('Kode transaksi harus dimulai dengan TM (Transaksi Masuk)');
            return;
        }
        
        // Tampilkan loading
        $('#btnLoadTransaksi').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memuat...');
        
        // AJAX request untuk mengambil data transaksi
        $.ajax({
            url: '/transaksi/getDetailByKode/' + kodeTransaksi,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#btnLoadTransaksi').html('Muat Transaksi');
                
                if(response.success && response.data) {
                    // Kosongkan item yang sudah ada
                    addedItems = {};
                    $('#itemsTable').empty();
                    
                    // Tambahkan item dari transaksi
                    response.data.detail.forEach(item => {
                        const kode = item.kode_kayu;
                        
                        addedItems[kode] = {
                            id_kayu: item.id_kayu,
                            kode_kayu: item.kode_kayu,
                            quantity: item.quantity,
                            data: {
                                panjang: item.panjang,
                                lebar: item.lebar,
                                tebal: item.tebal,
                                volume: item.volume
                            }
                        };
                        
                        addItemRow(kode);
                    });
                    
                    updateHiddenItemsField();
                    alert('Berhasil memuat ' + response.data.detail.length + ' item dari transaksi ' + kodeTransaksi);
                } else {
                    alert('Transaksi tidak ditemukan atau terjadi kesalahan: ' + (response.message || ''));
                }
            },
            error: function(xhr, status, error) {
                $('#btnLoadTransaksi').html('Muat Transaksi');
                alert('Error: ' + (xhr.responseJSON?.message || 'Gagal memuat transaksi'));
            }
        });
    }
    
    // Fungsi untuk memproses input kode kayu
    function processKodeInput() {
        const kode = $('#inputKode').val().trim();
        
        if(!kode) {
            alert('Harap masukkan kode kayu!');
            return;
        }
        
        // Cek apakah kode valid
        if(!kodeToIdMap[kode]) {
            alert('Kode kayu tidak valid: ' + kode);
            $('#inputKode').val('');
            return;
        }
        
        // Cek apakah item sudah ditambahkan
        if(addedItems[kode]) {
            // Jika sudah ada, tambahkan quantity
            addedItems[kode].quantity += 1;
            updateItemRow(kode);
        } else {
            // Jika belum, tambahkan item baru
            const kayu = kayuData.find(k => k.kode_kayu === kode);
            addedItems[kode] = {
                id_kayu: kayu.id_kayu,
                kode_kayu: kayu.kode_kayu,
                quantity: 1,
                data: kayu
            };
            addItemRow(kode);
        }
        
        // Reset input
        $('#inputKode').val('');
        updateHiddenItemsField();
    }
    
    // Fungsi untuk menambahkan baris item baru
    function addItemRow(kode) {
        const item = addedItems[kode];
        const row = `
            <tr id="row-${kode}">
                <td>
                    ${item.kode_kayu}
                    <input type="hidden" name="kode[]" value="${kode}">
                </td>
                <td>${item.data.panjang}x${item.data.lebar}x${item.data.tebal}cm</td>
                <td>
                    <div class="input-group">
                        <input type="number" class="form-control quantity-input" 
                               data-kode="${kode}" value="${item.quantity}" min="1">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary btn-sm btn-dec" data-kode="${kode}">-</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm btn-inc" data-kode="${kode}">+</button>
                        </div>
                    </div>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger btn-remove" data-kode="${kode}">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#itemsTable').append(row);
    }
    
    // Fungsi untuk memperbarui baris item yang sudah ada
    function updateItemRow(kode) {
        const item = addedItems[kode];
        $(`#row-${kode} .quantity-input`).val(item.quantity);
    }
    
    // Fungsi untuk menghapus item
    function removeItem(kode) {
        delete addedItems[kode];
        $(`#row-${kode}`).remove();
        updateHiddenItemsField();
    }
    
    // Handler untuk tombol hapus
    $(document).on('click', '.btn-remove', function() {
        const kode = $(this).data('kode');
        removeItem(kode);
    });
    
    // Handler untuk tombol increment
    $(document).on('click', '.btn-inc', function() {
        const kode = $(this).data('kode');
        addedItems[kode].quantity += 1;
        updateItemRow(kode);
        updateHiddenItemsField();
    });
    
    // Handler untuk tombol decrement
    $(document).on('click', '.btn-dec', function() {
        const kode = $(this).data('kode');
        if(addedItems[kode].quantity > 1) {
            addedItems[kode].quantity -= 1;
            updateItemRow(kode);
            updateHiddenItemsField();
        }
    });
    
    // Handler untuk input quantity manual
    $(document).on('change', '.quantity-input', function() {
        const kode = $(this).data('kode');
        const newQuantity = parseInt($(this).val()) || 1;
        addedItems[kode].quantity = newQuantity;
        updateHiddenItemsField();
    });
    
    // Fungsi untuk memperbarui hidden field
    function updateHiddenItemsField() {
        const itemsArray = Object.values(addedItems).map(item => ({
            id_kayu: item.id_kayu,
            quantity: item.quantity
        }));
        $('#items').val(JSON.stringify(itemsArray));
    }
    
    // Validasi sebelum submit
    $('#formTransaksi').submit(function(e) {
        if(Object.keys(addedItems).length === 0) {
            alert('Harap tambahkan minimal 1 item!');
            e.preventDefault();
            return false;
        }
        return true;
    });
    
    // Fokus ke input kode transaksi saat halaman dimuat
    $('#inputKodeTransaksi').focus();
});
</script>

<style>
#inputKode, #inputKodeTransaksi {
    font-weight: bold;
}

.quantity-input {
    text-align: center;
}

.btn-inc, .btn-dec {
    width: 30px;
}

.input-group {
    max-width: 150px;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}
</style>

<?= $this->endSection(); ?>