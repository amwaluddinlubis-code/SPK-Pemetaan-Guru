<?php

// Controller Kualitas Data (A): ringkasan read-only temuan kualitas data.
// Akses: kabid_ptk, kadis. Filter: dampak & jenis. Ekspor: tanpa PII berlebih.

namespace App\Http\Controllers;

use App\Services\PemeriksaKualitasData;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class KualitasDataController extends Controller
{
    public function index(Request $request, PemeriksaKualitasData $pemeriksa)
    {
        // Pemeriksaan penuh dijalankan SEKALI; daftar jenis diturunkan dari
        // hasil yang sama (sebelumnya periksa() dipanggil dua kali per muat).
        $semua = $pemeriksa->periksa();
        $daftarJenis = array_unique(array_column($semua, 'jenis'));
        sort($daftarJenis);

        $temuan = $semua;

        if ($request->filled('dampak')) {
            $temuan = array_filter($temuan, fn ($t) => $t['dampak'] === $request->dampak);
        }
        if ($request->filled('jenis')) {
            $temuan = array_filter($temuan, fn ($t) => $t['jenis'] === $request->jenis);
        }

        $ringkasan = [
            'tinggi' => count(array_filter($temuan, fn ($t) => $t['dampak'] === 'tinggi')),
            'sedang' => count(array_filter($temuan, fn ($t) => $t['dampak'] === 'sedang')),
            'rendah' => count(array_filter($temuan, fn ($t) => $t['dampak'] === 'rendah')),
        ];

        return view('kualitas-data.index', [
            'temuan' => array_values($temuan),
            'ringkasan' => $ringkasan,
            'daftarJenis' => $daftarJenis,
            'total' => count($temuan),
        ]);
    }

    public function export(PemeriksaKualitasData $pemeriksa)
    {
        $temuan = $pemeriksa->periksa();

        $spreadsheet = new Spreadsheet;
        $lembar = $spreadsheet->getActiveSheet();
        // Ekspor minim PII: tanpa NIP/alamat/koordinat — cukup jenis & tautan ID.
        $lembar->fromArray([['Entitas', 'ID', 'Nama', 'Jenis Temuan', 'Dampak', 'Pesan']], null, 'A1');
        $baris = 2;
        foreach ($temuan as $t) {
            $lembar->fromArray([[
                $t['entitas'], $t['id'], $t['nama'], $t['jenis'], $t['dampak'], $t['pesan'],
            ]], null, "A{$baris}");
            $baris++;
        }

        $namaFile = 'laporan-kualitas-data-'.now()->format('Ymd-His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $namaFile, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
