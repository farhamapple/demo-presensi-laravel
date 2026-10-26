<?php

use App\Services\GeofencingService;

beforeEach(function () {
    $this->geofencingService = new GeofencingService;
    // Polygon persegi 4 titik di area Monas Jakarta
    $this->samplePolygon = [
        ['lat' => -6.1750, 'lng' => 106.8260],
        ['lat' => -6.1750, 'lng' => 106.8280],
        ['lat' => -6.1770, 'lng' => 106.8280],
        ['lat' => -6.1770, 'lng' => 106.8260],
    ];
});

test('detects point correctly inside polygon', function () {
    // Titik tepat di tengah polygon
    $insideLat = -6.1760;
    $insideLng = 106.8270;

    $result = $this->geofencingService->isPointInPolygon($insideLat, $insideLng, $this->samplePolygon);

    expect($result)->toBeTrue();
});

test('detects point correctly outside polygon', function () {
    // Titik jauh di utara
    $outsideLat = -6.1740;
    $outsideLng = 106.8270;

    $result = $this->geofencingService->isPointInPolygon($outsideLat, $outsideLng, $this->samplePolygon);

    expect($result)->toBeFalse();

    // Titik di timur
    $eastLat = -6.1760;
    $eastLng = 106.8300;

    expect($this->geofencingService->isPointInPolygon($eastLat, $eastLng, $this->samplePolygon))->toBeFalse();
});

test('returns false for polygon with less than 3 vertices', function () {
    $invalidPolygon = [
        ['lat' => -6.1750, 'lng' => 106.8260],
        ['lat' => -6.1750, 'lng' => 106.8280],
    ];

    expect($this->geofencingService->isPointInPolygon(-6.1750, 106.8270, $invalidPolygon))->toBeFalse()
        ->and($this->geofencingService->validatePolygon($invalidPolygon))->toBeFalse();
});

test('validates valid polygon structure', function () {
    expect($this->geofencingService->validatePolygon($this->samplePolygon))->toBeTrue();

    $invalidCoords = [
        ['lat' => 'not-a-number', 'lng' => 106.8],
        ['lat' => -6.1, 'lng' => 106.8],
        ['lat' => -6.2, 'lng' => 106.8],
    ];
    expect($this->geofencingService->validatePolygon($invalidCoords))->toBeFalse();
});

test('calculates centroid correctly', function () {
    $centroid = $this->geofencingService->calculateCentroid($this->samplePolygon);

    expect($centroid['lat'])->toBe(-6.176)
        ->and($centroid['lng'])->toBe(106.827);
});

test('calculates distance in meters between two coordinates', function () {
    // Jarak antara Monas dan Bundaran HI (~2.3 km = ~2300 m)
    $monasLat = -6.175392;
    $monasLng = 106.827153;

    $hiLat = -6.195000;
    $hiLng = 106.823000;

    $distance = $this->geofencingService->calculateDistanceInMeters($monasLat, $monasLng, $hiLat, $hiLng);

    expect($distance)->toBeGreaterThan(2000)
        ->and($distance)->toBeLessThan(2500);
});
