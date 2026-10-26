<?php

test('the application returns a successful response and matches PRD design specifications', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('SiAbsen');
    $response->assertSee('Bappenas');
    $response->assertSee('admin/login');
    $response->assertSee('Total Pegawai');
    $response->assertSee('Sudah Absen');
    $response->assertSee('Belum Absen');
    $response->assertSee('Terlambat');
    $response->assertSee('Lokasi Aktif');
    $response->assertSee('Demo', false);
    $response->assertSee('Pembelajaran', false);
});
