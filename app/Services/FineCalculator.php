<?php

namespace App\Services;

use App\Models\ReturnDeadline;
use Carbon\Carbon;

class FineCalculator
{
    /**
     * Service untuk menghitung denda dengan konsisten
     */

    const FINE_RUSAK = 70000;        // Rp 70.000 per buku rusak
    const FINE_HILANG = 70000;       // Rp 70.000 per buku hilang
    const FINE_TERLAMBAT_PER_HARI = 5000; // Rp 5.000 per hari (setelah 7 hari)
    const GRACE_PERIOD_DAYS = 7;     // Grace period: 7 hari

    /**
     * Hitung denda rusak
     */
    public static function calculateRusakFine($jumlah = 1)
    {
        return self::FINE_RUSAK * $jumlah;
    }

    /**
     * Hitung denda hilang
     */
    public static function calculateHilangFine($jumlah = 1)
    {
        return self::FINE_HILANG * $jumlah;
    }

    /**
     * Hitung denda keterlambatan
     */
    public static function calculateLateFine($tanggalKembaliAktual, $deadline = null)
    {
        if (!$deadline) {
            $deadline = ReturnDeadline::getActiveDeadline();
        }

        if (!$deadline) {
            return 0; // Tidak ada deadline, tidak ada denda terlambat
        }

        $tanggalKembali = Carbon::parse($tanggalKembaliAktual);
        $deadlineDate = Carbon::parse($deadline->deadline_date);

        // Cek apakah terlambat
        if ($tanggalKembali->lessThanOrEqualTo($deadlineDate)) {
            return 0; // Tidak terlambat
        }

        // Hitung hari terlambat
        $hariTerlambat = $tanggalKembali->diffInDays($deadlineDate);

        // Hanya hitung denda setelah grace period
        if ($hariTerlambat <= self::GRACE_PERIOD_DAYS) {
            return 0;
        }

        $hariTerhitung = $hariTerlambat - self::GRACE_PERIOD_DAYS;
        return self::FINE_TERLAMBAT_PER_HARI * $hariTerhitung;
    }

    /**
     * Hitung total denda
     */
    public static function calculateTotalFine($rusak = 0, $hilang = 0, $tanggalKembaliAktual = null)
    {
        $total = 0;

        $total += self::calculateRusakFine($rusak);
        $total += self::calculateHilangFine($hilang);

        if ($tanggalKembaliAktual) {
            $total += self::calculateLateFine($tanggalKembaliAktual);
        }

        return $total;
    }

    /**
     * Format nominal ke Rupiah
     */
    public static function format($nominal)
    {
        return 'Rp ' . number_format($nominal, 0, ',', '.');
    }
}