<?php

test('the home route redirects guests to the login page', function () {
    // '/' está tras middleware auth y redirige a dashboard; un invitado
    // termina en /login. (Antes este test del starter esperaba 200, algo
    // imposible ya que la app no tiene una home pública.)
    $response = $this->get('/');

    $response->assertRedirect('/login');
});
