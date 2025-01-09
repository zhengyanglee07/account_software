<?php

it('enter dashboard without authenticated', function () {
    $response = $this->get('/dashboard');
        
    $response
        ->assertStatus(302)
        ->assertRedirect('/login');
});
