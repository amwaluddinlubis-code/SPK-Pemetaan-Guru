// JavaScript utama aplikasi.
// - './bootstrap': konfigurasi axios & echo bawaan Laravel (boleh dikosongkan jika tidak dipakai)
// - Alpine.js otomatis disertakan oleh Livewire 3, jadi tidak perlu diimpor manual.

import './bootstrap';

// ===== Sistem tema: light / dark / system =====
// Pilihan disimpan di localStorage 'tema-ppa'. 'system' mengikuti
// prefers-color-scheme OS. Diterapkan sedini mungkin agar tidak flicker.
(function () {
    const KUNCI = 'tema-ppa';
    function terapkan(tema) {
        let efektif = tema;
        if (tema === 'system' || !tema) {
            efektif = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-theme', efektif);
    }
    // Terapkan saat load
    terapkan(localStorage.getItem(KUNCI) || 'system');
    // Ikuti perubahan OS bila mode system
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if ((localStorage.getItem(KUNCI) || 'system') === 'system') terapkan('system');
    });
    // Handler tombol pilihan tema (event delegation)
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-tema]');
        if (!btn) return;
        const tema = btn.getAttribute('data-tema');
        localStorage.setItem(KUNCI, tema);
        terapkan(tema);
    });
})();

