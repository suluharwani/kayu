<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h2 class="mt-2">Transaksi Keluar Gudang</h2>
            <form id="formTransaksi" action="/transaksi/saveKeluar" method="post">
                <?= csrf_field(); ?>
                <div class="form-group row">
                    <label for="id_gudang" class="col-sm-2 col-form-label">Gudang Asal</label>
                    <div class="col-sm-10">
                        <select class="form-control" id="id_gudang" name="id_gudang" required>
                            <option value="">-- Pilih Gudang --</option>
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
                <div class="table-responsive">
                    <table class="table table-bordered" id="tableItems">
                        <thead>
                            <tr>
                                <th>Jenis Kayu</th>
                                <th>Stock Tersedia</th>
                                <th>Quantity</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyItems">
                            <!-- Items akan ditambahkan via JS -->
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-primary" id="btnAddItem">Tambah Item</button>
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

<!-- Modal Add Item -->
<div class="modal fade" id="modalAddItem" tabindex="-1" role="dialog" aria-labelledby="modalAddItemLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddItemLabel">Tambah Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="modal_id_kayu">Jenis Kayu</label>
                    <select class="form-control" id="modal_id_kayu">
                        <option value="">-- Pilih Jenis Kayu --</option>
                        <?php foreach($kayu as $k): ?>
                            <option value="<?= $k['id_kayu']; ?>">
                                <?= $k['kode_kayu']; ?> - <?= $k['nama_jenis']; ?> (<?= $k['panjang']; ?>x<?= $k['lebar']; ?>x<?= $k['tebal']; ?>cm)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="modal_stock">Stock Tersedia</label>
                    <input type="number" class="form-control" id="modal_stock" readonly>
                </div>
                <div class="form-group">
                    <label for="modal_quantity">Quantity</label>
                    <input type="number" class="form-control" id="modal_quantity" min="1" value="1">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSaveItem">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let items = [];
    let currentGudang = '';
    let kayuStock = <?= json_encode(array_reduce($kayu, function($carry, $item) {
        $carry[$item['id_kayu']] = $item;
        return $carry;
    }, [])) ?>;
    
    // Format tanggal sekarang untuk input datetime-local
    let now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    document.getElementById('tanggal').value = now.toISOString().slice(0,16);
    
    // Update stock saat gudang berubah
    $('#id_gudang').change(function() {
        currentGudang = $(this).val();
        renderItems();
    });
    
    // Update stock info saat pilih kayu
    $('#modal_id_kayu').change(function() {
        const idKayu = $(this).val();
        if (!idKayu || !currentGudang) {
            $('#modal_stock').val(0);
            return;
        }
        
        // Cari stock dari data yang sudah diload
        const stock = getStock(idKayu, currentGudang);
        $('#modal_stock').val(stock);
        $('#modal_quantity').attr('max', stock);
    });
    
    // Buka modal tambah item
    $('#btnAddItem').click(function() {
        if (!$('#id_gudang').val()) {
            alert('Pilih gudang terlebih dahulu');
            return;
        }
        $('#modalAddItem').modal('show');
    });
    
    // Simpan item ke tabel
    $('#btnSaveItem').click(function() {
        const id_kayu = $('#modal_id_kayu').val();
        const quantity = parseInt($('#modal_quantity').val());
        const stock = parseInt($('#modal_stock').val());
        
        if (!id_kayu || !quantity) {
            alert('Harap pilih jenis kayu dan isi quantity!');
            return;
        }
        
        if (quantity > stock) {
            alert('Quantity melebihi stock tersedia!');
            return;
        }
        
        // Cek apakah item sudah ada
        const existingItem = items.find(item => item.id_kayu == id_kayu);
        if (existingItem) {
            if ((existingItem.quantity + quantity) > stock) {
                alert('Total quantity melebihi stock tersedia!');
                return;
            }
            existingItem.quantity += quantity;
        } else {
            const selectedKayu = kayuStock[id_kayu];
            
            items.push({
                id_kayu: id_kayu,
                kode_kayu: selectedKayu.kode_kayu,
                nama_jenis: selectedKayu.nama_jenis,
                stock: stock,
                quantity: quantity
            });
        }
        
        renderItems();
        $('#modalAddItem').modal('hide');
        resetModal();
    });
    
    // Hapus item
    $(document).on('click', '.btnDeleteItem', function() {
        const index = $(this).data('index');
        items.splice(index, 1);
        renderItems();
    });
    
    // Render items ke tabel
    function renderItems() {
        let html = '';
        items.forEach((item, index) => {
            // Update stock terbaru
            item.stock = getStock(item.id_kayu, currentGudang);
            
            html += `
                <tr>
                    <td>${item.kode_kayu} - ${item.nama_jenis}</td>
                    <td>${item.stock}</td>
                    <td>${item.quantity}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger btnDeleteItem" data-index="${index}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        $('#tbodyItems').html(html);
        $('#items').val(JSON.stringify(items));
    }
    
    // Reset modal
    function resetModal() {
        $('#modal_id_kayu').val('');
        $('#modal_stock').val('');
        $('#modal_quantity').val('1');
    }
    
    // Get stock dari data yang sudah diload
    function getStock(idKayu, idGudang) {
        // Di implementasi nyata, ini akan query ke database
        // Untuk demo, kita return nilai random
        return Math.floor(Math.random() * 100) + 1;
        

    }
    
    // Validasi sebelum submit
    $('#formTransaksi').submit(function(e) {
        if (items.length === 0) {
            alert('Harap tambahkan minimal 1 item!');
            e.preventDefault();
            return false;
        }
        return true;
    });
});
</script>
<?= $this->endSection(); ?>