<?php
// Controller Export: unduh data sebagai CSV (bisa dibuka di Excel).
// Tanpa library tambahan — memakai fungsi bawaan PHP.
// Route: /export/guru, /export/sekolah, /export/usulan

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Sekolah;
use App\Models\UsulanPenempatan;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    // Unduh data guru (mengikuti scope peran yang sedang login)
    public function guru()
    {
        $data = $this->queryGuruTerbatas()->get();
        return $this->csv('data-guru.csv',
            ['NIP', 'Nama', 'Mapel', 'Jenjang', 'Kecamatan', 'Domisili', 'Sekolah Saat Ini'],
            $data->map(fn ($g) => [
                $g->nip, $g->nama, $g->mapel, $g->jenjang,
                $g->kecamatan, $g->domisili_alamat, $g->sekolah_saat_ini,
            ]));
    }

    // Unduh data sekolah (mengikuti scope peran)
    public function sekolah()
    {
        $user = auth()->user();
        $query = Sekolah::with('kebutuhan')->orderBy('nama');
        if ($user->role === 'kepala_sekolah') {
            $query->where('id', $user->sekolah_id);
        } elseif ($user->role === 'ketua_pgri') {
            $query->where('kecamatan', $user->kecamatan);
        }
        $data = $query->get();

        return $this->csv('data-sekolah.csv',
            ['NPSN', 'Nama', 'Alamat', 'Kecamatan', 'Lat', 'Lng', 'Kebutuhan'],
            $data->map(fn ($s) => [
                $s->npsn, $s->nama, $s->alamat, $s->kecamatan,
                $s->lat, $s->lng,
                $s->kebutuhan->map(fn ($k) => "{$k->mapel} ({$k->jumlah})")->join('; '),
            ]));
    }

    // Unduh data usulan penempatan
    public function usulan()
    {
        $data = UsulanPenempatan::with(['guru', 'sekolah'])
            ->orderByDesc('created_at')->get();
        return $this->csv('data-usulan.csv',
            ['ID', 'Guru', 'NIP', 'Sekolah Tujuan', 'Alasan', 'Status', 'Diajukan Pada'],
            $data->map(fn ($u) => [
                $u->id, $u->guru?->nama, $u->guru?->nip,
                $u->sekolah?->nama, $u->alasan,
                UsulanPenempatan::STATUS[$u->status] ?? $u->status,
                $u->created_at?->format('d-m-Y H:i'),
            ]));
    }

    // Query guru dengan batasan scope peran (sama seperti GuruController)
    private function queryGuruTerbatas()
    {
        $user = auth()->user();
        $query = Guru::orderBy('nama');
        if ($user->role === 'guru') {
            $query->where('id', $user->guru_id);
        } elseif ($user->role === 'kepala_sekolah') {
            $query->where('sekolah_id', $user->sekolah_id);
        } elseif ($user->role === 'ketua_pgri') {
            $query->where('kecamatan', $user->kecamatan);
        }
        return $query;
    }

    // Kirim CSV ke browser sebagai file unduhan
    private function csv(string $namaFile, array $header, $baris): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($header, $baris) {
            $out = fopen('php://output', 'w');
            // BOM agar Excel Windows membaca UTF-8 dengan benar
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $header, ';');
            foreach ($baris as $b) {
                fputcsv($out, $b, ';');
            }
            fclose($out);
        });
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set(
            'Content-Disposition', "attachment; filename=\"{$namaFile}\"");
        return $response;
    }
}
