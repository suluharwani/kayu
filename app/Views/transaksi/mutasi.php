<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container">
    <div class="row">
        <div class="col">
            <h2 class="mt-2">Mutasi Antar Gudang</h2>
            <form id="formTransaksi" action="/transaksi/saveMutasi" method="post">
                <?= csrf_field(); ?>
                
                <!-- Input Kode Transaksi untuk mengambil data -->
                <div class="form-group row">
                    <label for="inputKodeTransaksi" class="col-sm-2 col-form-label">Kode Transaksi (Opsional)</label>
                    <div class="col-sm-10">
                        <div class="input-group">
                            <input type="text" class="form-control" id="inputKodeTransaksi" 
                                   placeholder="Masukkan kode transaksi (MT...) untuk mengambil item" autocomplete="off">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary" id="btnLoadTransaksi">
                                    Muat Transaksi
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Masukkan kode transaksi mutasi (MT...) untuk mengambil item dari transaksi sebelumnya
                        </small>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="id_gudang_asal" class="col-sm-2 col-form-label">Gudang Asal</label>
                    <div class="col-sm-10">
                        <select class="form-control" id="id_gudang_asal" name="id_gudang_asal" required>
                            <option value="">-- Pilih Gudang Asal --</option>
                            <?php foreach($gudang as $g): ?>
                                <option value="<?= $g['id_gudang']; ?>"><?= $g['kode_gudang']; ?> - <?= $g['nama_gudang']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="id_gudang_tujuan" class="col-sm-2 col-form-label">Gudang Tujuan</label>
                    <div class="col-sm-10">
                        <select class="form-control" id="id_gudang_tujuan" name="id_gudang_tujuan" required>
                            <option value="">-- Pilih Gudang Tujuan --</option>
                            <?php foreach($gudang as $g): ?>
                                <option value="<?= $g['id_gudang']; ?>"><?= $g['kode_gudang']; ?> - <?= $g['nama_gudang']; ?></option>
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
                        <li>Pilih gudang asal terlebih dahulu untuk melihat stok tersedia</li>
                        <li>Masukkan kode kayu dan tekan <kbd>Enter</kbd> untuk menambahkan item. Quantity default = 1.</li>
                        <li>Quantity tidak boleh melebihi stok yang tersedia di gudang asal</li>
                        <li>Gudang tujuan harus berbeda dengan gudang asal</li>
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
                                <th width="45%">Kode Kayu</th>
                                <th width="20%">Dimensi</th>
                                <th width="15%">Stok Tersedia</th>
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
                        <button type="submit" class="btn btn-primary">Simpan Mutasi</button>
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
    let currentGudangAsal = '';
    
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
    
    // Handler untuk perubahan gudang asal
    $('#id_gudang_asal').change(function() {
        currentGudangAsal = $(this).val();
        // Reset items jika gudang berubah
        if (Object.keys(addedItems).length > 0) {
            if (confirm('Mengubah gudang asal akan menghapus semua item yang sudah ditambahkan. Lanjutkan?')) {
                addedItems = {};
                $('#itemsTable').empty();
                updateHiddenItemsField();
            } else {
                $(this).val(currentGudangAsal);
                return;
            }
        }
    });
    
    // Validasi gudang tujuan berbeda dengan asal
    $('#id_gudang_tujuan').change(function() {
        if ($(this).val() === $('#id_gudang_asal').val()) {
            alert('Gudang tujuan harus berbeda dengan gudang asal!');
            $(this).val('');
        }
    });
    
    // Fungsi untuk memuat item dari transaksi
    function loadTransaksiItems() {
        const kodeTransaksi = $('#inputKodeTransaksi').val().trim();
        
        if(!kodeTransaksi) {
            alert('Harap masukkan kode transaksi!');
            return;
        }
        
        // Validasi format kode transaksi (harus dimulai dengan MT)
        if(!kodeTransaksi.startsWith('MT')) {
            alert('Kode transaksi harus dimulai dengan MT (Mutasi)');
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
        
        if(!currentGudangAsal) {
            alert('Harap pilih gudang asal terlebih dahulu!');
            $('#id_gudang_asal').focus();
            return;
        }
        
        // Cek apakah kode valid
        if(!kodeToIdMap[kode]) {
            alert('Kode kayu tidak valid: ' + kode);
            $('#inputKode').val('');
            return;
        }
        
        // Cek stok tersedia
        const stock = getStock(kodeToIdMap[kode], currentGudangAsal);
        if(stock <= 0) {
            alert('Stok tidak tersedia untuk kayu: ' + kode);
            $('#inputKode').val('');
            return;
        }
        
        // Cek apakah item sudah ditambahkan
        if(addedItems[kode]) {
            // Jika sudah ada, tambahkan quantity jika masih mencukupi
            if(addedItems[kode].quantity + 1 > stock) {
                alert('Quantity melebihi stok tersedia! Stok: ' + stock);
                return;
            }
            addedItems[kode].quantity += 1;
            updateItemRow(kode);
        } else {
            // Jika belum, tambahkan item baru
            const kayu = kayuData.find(k => k.kode_kayu === kode);
            addedItems[kode] = {
                id_kayu: kayu.id_kayu,
                kode_kayu: kayu.kode_kayu,
                quantity: 1,
                stock: stock,
                data: kayu
            };
            addItemRow(kode);
        }
        
        // Reset input
        $('#inputKode').val('');
        updateHiddenItemsField();
    }
    
  // Fungsi untuk mendapatkan stok dari server
function getStock(idKayu, idGudang) {
    // Menggunakan AJAX untuk mendapatkan stok real-time dari server
    let stockValue = 0;
    
    // Lakukan AJAX call synchronous untuk simplicity
    $.ajax({
        url: '/transaksi/getStock/' + idKayu + '/' + idGudang,
        type: 'GET',
        dataType: 'json',
        async: false, // Synchronous untuk mendapatkan nilai return
        success: function(response) {
            if(response.success) {
                stockValue = response.data.quantity;
            } else {
                alert('Error mendapatkan stok: ' + response.message);
                stockValue = 0;
            }
        },
        error: function(xhr, status, error) {
            alert('Error: Gagal mendapatkan data stok');
            stockValue = 0;
        }
    });
    
    return stockValue;
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
                <td>${item.stock}</td>
                <td>
                    <div class="input-group">
                        <input type="number" class="form-control quantity-input" 
                               data-kode="${kode}" value="${item.quantity}" min="1" max="${item.stock}">
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
        $(`#row-${kode} .quantity-input`).val(item.quantity).attr('max', item.stock);
        $(`#row-${kode} td:nth-child(3)`).text(item.stock);
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
        if(addedItems[kode].quantity < addedItems[kode].stock) {
            addedItems[kode].quantity += 1;
            updateItemRow(kode);
            updateHiddenItemsField();
        } else {
            alert('Tidak dapat menambah quantity, melebihi stok tersedia!');
        }
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
        
        if(newQuantity > addedItems[kode].stock) {
            alert('Quantity melebihi stok tersedia! Stok: ' + addedItems[kode].stock);
            $(this).val(addedItems[kode].quantity);
            return;
        }
        
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
        
        if(!currentGudangAsal) {
            alert('Harap pilih gudang asal!');
            e.preventDefault();
            return false;
        }
        
        if(!$('#id_gudang_tujuan').val()) {
            alert('Harap pilih gudang tujuan!');
            e.preventDefault();
            return false;
        }
        
        if($('#id_gudang_asal').val() === $('#id_gudang_tujuan').val()) {
            alert('Gudang tujuan harus berbeda dengan gudang asal!');
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