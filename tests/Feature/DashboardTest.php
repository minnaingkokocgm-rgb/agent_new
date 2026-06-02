<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get('/admin/events');
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the events page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/admin/events');
    $response->assertOk();
});
