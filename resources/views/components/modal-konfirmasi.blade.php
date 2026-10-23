{{-- Modal konfirmasi global — pengganti confirm()/prompt() bawaan browser.
     • Form biasa: beri atribut data-konfirmasi pada <form>, plus opsional
       data-judul, data-pesan, data-tombol, data-warna (kelas tombol OK).
     • Varian alasan (pengganti prompt): tambah data-alasan="nama_input"
       dan data-alasan-label — modal menampilkan textarea wajib; teksnya
       ditulis ke input tersembunyi bernama itu sebelum form dikirim.
     • Tombol Livewire/JS: panggil konfirmasiModal({...}) → Promise bernilai
       true (ya) / false (batal) / string (teks alasan). Pesan dinamis yang
       aman dari tanda kutip: taruh di atribut data-pesan elemen lalu panggil
       konfirmasiDariData($el, {...}). --}}
<dialog id="modal-konfirmasi" class="modal">
    <div class="modal-box">
        <h3 id="mk-judul" class="text-lg font-bold">Konfirmasi</h3>
        <p id="mk-pesan" class="whitespace-pre-line py-3 text-sm leading-relaxed"></p>
        <div id="mk-alasan-bungkus" class="hidden">
            <label id="mk-alasan-label" class="mb-1 block cursor-pointer text-sm font-semibold" for="mk-alasan">Alasan (wajib)</label>
            <textarea id="mk-alasan" rows="3" class="textarea w-full"></textarea>
            <p id="mk-alasan-error" class="mt-1 hidden text-sm text-error">Alasan wajib diisi.</p>
        </div>
        <div class="modal-action">
            <button type="button" id="mk-batal" class="btn">Batal</button>
            <button type="button" id="mk-ok" class="btn btn-primary">Ya, lanjutkan</button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button aria-label="Tutup">Tutup</button></form>
</dialog>
<script>
(function () {
    var dlg = document.getElementById('modal-konfirmasi');
    if (!dlg || dlg.dataset.siap) return;
    dlg.dataset.siap = '1';
    var $ = function (id) { return document.getElementById(id); };
    var resolver = null;
    function selesai(hasil) {
        if (resolver) { var r = resolver; resolver = null; r(hasil); }
        if (dlg.open) dlg.close();
    }
    window.konfirmasiModal = function (o) {
        o = o || {};
        $('mk-judul').textContent = o.judul || 'Konfirmasi';
        $('mk-pesan').textContent = o.pesan || 'Lanjutkan tindakan ini?';
        var ok = $('mk-ok');
        ok.textContent = o.tombol || 'Ya, lanjutkan';
        ok.className = 'btn ' + (o.warna || 'btn-primary');
        var bungkus = $('mk-alasan-bungkus');
        if (o.alasan) {
            bungkus.classList.remove('hidden');
            $('mk-alasan-label').textContent = o.alasanLabel || 'Alasan (wajib)';
            $('mk-alasan').value = '';
            $('mk-alasan-error').classList.add('hidden');
        } else {
            bungkus.classList.add('hidden');
        }
        return new Promise(function (res) {
            resolver = res;
            dlg.showModal();
            if (o.alasan) setTimeout(function () { $('mk-alasan').focus(); }, 60);
        });
    };
    window.konfirmasiDariData = function (el, o) {
        o = o || {};
        if (el.dataset.pesan) o.pesan = el.dataset.pesan;
        return window.konfirmasiModal(o);
    };
    $('mk-batal').addEventListener('click', function () { selesai(false); });
    dlg.addEventListener('close', function () {
        if (resolver) { var r = resolver; resolver = null; r(false); }
    });
    $('mk-ok').addEventListener('click', function () {
        if (!$('mk-alasan-bungkus').classList.contains('hidden')) {
            var v = $('mk-alasan').value.trim();
            if (!v) { $('mk-alasan-error').classList.remove('hidden'); $('mk-alasan').focus(); return; }
            selesai(v);
        } else {
            selesai(true);
        }
    });
    document.addEventListener('submit', function (e) {
        var f = e.target;
        if (!f.matches || !f.matches('form[data-konfirmasi]')) return;
        e.preventDefault();
        var namaAlasan = f.dataset.alasan;
        window.konfirmasiModal({
            judul: f.dataset.judul, pesan: f.dataset.pesan,
            tombol: f.dataset.tombol, warna: f.dataset.warna,
            alasan: !!namaAlasan, alasanLabel: f.dataset.alasanLabel
        }).then(function (v) {
            if (namaAlasan) {
                if (typeof v !== 'string' || !v) return;
                var h = f.querySelector('[name="' + namaAlasan + '"]');
                if (h) h.value = v;
            } else if (!v) return;
            f.submit();
        });
    }, true);
})();
</script>
