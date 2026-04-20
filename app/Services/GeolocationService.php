<?php

namespace App\Services;

class GeolocationService
{
    /**
     * Hitung jarak antara dua koordinat dalam meter menggunakan Haversine formula
     */
    public static function hitungJarak(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Validasi apakah koordinat dalam radius yang diizinkan
     */
    public static function dalamRadius(float $siswaLat, float $siswaLon, float $sekolahLat, float $sekolahLon, int $radiusMeter): bool
    {
        $jarak = self::hitungJarak($siswaLat, $siswaLon, $sekolahLat, $sekolahLon);
        return $jarak <= $radiusMeter;
    }
}