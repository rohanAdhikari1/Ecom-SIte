<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('guests cannot view the profile page', function () {
    $this->get('/account/profile')->assertRedirect('/login');
});

test('a user can update their name, email, and phone', function () {
    $user = User::factory()->create(['name' => 'Old Name']);

    Livewire::actingAs($user)
        ->test('pages::account.profile')
        ->set('name', 'New Name')
        ->set('email', 'new@example.com')
        ->set('phone', '9812345678')
        ->call('updateProfile');

    expect($user->fresh()->name)->toBe('New Name');
    expect($user->fresh()->email)->toBe('new@example.com');
    expect($user->fresh()->phone)->toBe('9812345678');
});

test('a user cannot take another users email', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::account.profile')
        ->set('email', 'taken@example.com')
        ->call('updateProfile')
        ->assertHasErrors(['email']);
});

test('a user can change their password with the correct current password', function () {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);

    Livewire::actingAs($user)
        ->test('pages::account.profile')
        ->set('currentPassword', 'old-password')
        ->set('newPassword', 'brand-new-password')
        ->set('newPasswordConfirmation', 'brand-new-password')
        ->call('updatePassword')
        ->assertHasNoErrors();

    expect(Hash::check('brand-new-password', $user->fresh()->password))->toBeTrue();
});

test('changing password fails with wrong current password', function () {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);

    Livewire::actingAs($user)
        ->test('pages::account.profile')
        ->set('currentPassword', 'wrong-password')
        ->set('newPassword', 'brand-new-password')
        ->set('newPasswordConfirmation', 'brand-new-password')
        ->call('updatePassword')
        ->assertHasErrors(['currentPassword']);
});

test('changing password fails when confirmation does not match', function () {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);

    Livewire::actingAs($user)
        ->test('pages::account.profile')
        ->set('currentPassword', 'old-password')
        ->set('newPassword', 'brand-new-password')
        ->set('newPasswordConfirmation', 'does-not-match')
        ->call('updatePassword')
        ->assertHasErrors(['newPassword']);
});
