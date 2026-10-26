<?php

test('the application returns a successful response and contains admin login button with demo notice', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('SiAbsen');
    $response->assertSee('admin/login');
    $response->assertSee('Demo', false);
    $response->assertSee('Pembelajaran', false);
});
