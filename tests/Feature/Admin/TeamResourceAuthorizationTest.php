<?php

use App\Filament\Resources\Team\Pages\EditTeamMember;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->staff = User::factory()->create();
    $this->staff->assignRole('staff');
});

test('admin can access every team page', function () {
    $this->actingAs($this->admin)->get('/crm/team')->assertOk();
    $this->actingAs($this->admin)->get('/crm/team/create')->assertOk();
    $this->actingAs($this->admin)->get("/crm/team/{$this->staff->id}/edit")->assertOk();
});

test('staff cannot access team pages by direct url', function () {
    $this->actingAs($this->staff)->get('/crm/team')->assertForbidden();
    $this->actingAs($this->staff)->get('/crm/team/create')->assertForbidden();
    $this->actingAs($this->staff)->get("/crm/team/{$this->admin->id}/edit")->assertForbidden();
});

test('staff cannot self-escalate role via a crafted team update request', function () {
    $this->actingAs($this->staff);

    Livewire\Livewire::test(EditTeamMember::class, ['record' => $this->staff->id])
        ->assertForbidden();

    expect($this->staff->fresh()->hasRole('admin'))->toBeFalse();
});
