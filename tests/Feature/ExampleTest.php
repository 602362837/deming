<?php

use App\Models\User;

it('redirects unauthenticated users to login', function () {
    $this->get('/')->assertRedirect('/login');
});

it('returns a successful response for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/')->assertStatus(200);
});
