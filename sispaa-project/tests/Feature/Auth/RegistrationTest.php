<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'nombres' => 'Test',
        'apellidos' => 'User',
        // Cédula ecuatoriana válida (algoritmo módulo 10) y correo del
        // dominio institucional aceptado (@uleam.edu.ec / @live.uleam.edu.ec).
        'cedula' => '1710034065',
        'email' => 'test@uleam.edu.ec',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});