<?php

namespace App\Services;

class GeofencingService
{
    /**
     * Check if a GPS coordinate point (lat, lng) is inside a polygon using Ray-Casting algorithm.
     *
     * @param  float  $latitude  Point latitude
     * @param  float  $longitude  Point longitude
     * @param  array<int, array{lat: float|int|string, lng: float|int|string}>  $polygon  Array of polygon vertices
     */
    public function isPointInPolygon(float $latitude, float $longitude, array $polygon): bool
    {
        $normalizedPolygon = $this->normalizePolygon($polygon);
        $totalVertices = count($normalizedPolygon);

        if ($totalVertices < 3) {
            return false;
        }

        $inside = false;

        for ($i = 0, $j = $totalVertices - 1; $i < $totalVertices; $j = $i++) {
            $latI = $normalizedPolygon[$i]['lat'];
            $lngI = $normalizedPolygon[$i]['lng'];
            $latJ = $normalizedPolygon[$j]['lat'];
            $lngJ = $normalizedPolygon[$j]['lng'];

            // Check if horizontal ray from point crosses the edge between vertex i and vertex j
            $isBetweenLatitudes = ($latI > $latitude) !== ($latJ > $latitude);

            if ($isBetweenLatitudes) {
                $crossLongitude = (($lngJ - $lngI) * ($latitude - $latI) / ($latJ - $latI)) + $lngI;

                if ($longitude < $crossLongitude) {
                    $inside = ! $inside;
                }
            }
        }

        return $inside;
    }

    /**
     * Validate whether polygon has at least 3 valid coordinate vertices.
     *
     * @param  mixed  $polygon
     */
    public function validatePolygon($polygon): bool
    {
        if (! is_array($polygon) || count($polygon) < 3) {
            return false;
        }

        foreach ($polygon as $vertex) {
            if (! is_array($vertex)) {
                return false;
            }

            $lat = $vertex['lat'] ?? $vertex['latitude'] ?? null;
            $lng = $vertex['lng'] ?? $vertex['longitude'] ?? null;

            if ($lat === null || $lng === null || ! is_numeric($lat) || ! is_numeric($lng)) {
                return false;
            }

            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate centroid (center point) of a polygon.
     *
     * @param  array<int, array<string, mixed>>  $polygon
     * @return array{lat: float, lng: float}
     */
    public function calculateCentroid(array $polygon): array
    {
        $normalized = $this->normalizePolygon($polygon);
        $count = count($normalized);

        if ($count === 0) {
            return ['lat' => 0.0, 'lng' => 0.0];
        }

        $sumLat = 0.0;
        $sumLng = 0.0;

        foreach ($normalized as $vertex) {
            $sumLat += $vertex['lat'];
            $sumLng += $vertex['lng'];
        }

        return [
            'lat' => round($sumLat / $count, 7),
            'lng' => round($sumLng / $count, 7),
        ];
    }

    /**
     * Calculate great-circle distance between two coordinates in meters (Haversine formula).
     */
    public function calculateDistanceInMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // in meters

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lngDelta / 2) * sin($lngDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * Normalize polygon vertex array keys to uniform ['lat' => float, 'lng' => float].
     *
     * @param  array<int, mixed>  $polygon
     * @return array<int, array{lat: float, lng: float}>
     */
    public function normalizePolygon(array $polygon): array
    {
        $normalized = [];

        foreach ($polygon as $vertex) {
            if (! is_array($vertex)) {
                continue;
            }

            $lat = $vertex['lat'] ?? $vertex['latitude'] ?? null;
            $lng = $vertex['lng'] ?? $vertex['longitude'] ?? null;

            if ($lat !== null && $lng !== null && is_numeric($lat) && is_numeric($lng)) {
                $normalized[] = [
                    'lat' => (float) $lat,
                    'lng' => (float) $lng,
                ];
            }
        }

        return $normalized;
    }
}
