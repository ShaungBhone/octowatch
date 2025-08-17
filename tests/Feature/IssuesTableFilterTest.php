<?php

use App\Filament\Resources\Octo\Issues\Pages\ManageIssues;
use App\Models\Octo\Connection;
use App\Models\Octo\Issues;
use App\Models\Octo\Repository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->connection = Connection::factory()->create([
        'user_id' => $this->user->id,
    ]);
    
    $this->repo1 = Repository::factory()->create([
        'octo_connection_id' => $this->connection->id,
        'name' => 'First Repository',
    ]);
    
    $this->repo2 = Repository::factory()->create([
        'octo_connection_id' => $this->connection->id,
        'name' => 'Second Repository',
    ]);
    
    $this->issue1 = Issues::factory()->create([
        'octo_repository_id' => $this->repo1->id,
        'title' => 'Issue in first repo',
    ]);
    
    $this->issue2 = Issues::factory()->create([
        'octo_repository_id' => $this->repo2->id,
        'title' => 'Issue in second repo',
    ]);
    
    $this->issue3 = Issues::factory()->create([
        'octo_repository_id' => $this->repo1->id,
        'title' => 'Another issue in first repo',
    ]);
});

it('can filter issues by repository', function () {
    $this->actingAs($this->user);
    
    Livewire::test(ManageIssues::class)
        ->assertCanSeeTableRecords([$this->issue1, $this->issue2, $this->issue3])
        ->filterTable('repository', $this->repo1->id)
        ->assertCanSeeTableRecords([$this->issue1, $this->issue3])
        ->assertCanNotSeeTableRecords([$this->issue2]);
});

it('shows all issues when no repository filter is applied', function () {
    $this->actingAs($this->user);
    
    Livewire::test(ManageIssues::class)
        ->assertCanSeeTableRecords([$this->issue1, $this->issue2, $this->issue3]);
});

it('can reset repository filter', function () {
    $this->actingAs($this->user);
    
    Livewire::test(ManageIssues::class)
        ->filterTable('repository', $this->repo1->id)
        ->assertCanSeeTableRecords([$this->issue1, $this->issue3])
        ->assertCanNotSeeTableRecords([$this->issue2])
        ->resetTableFilters()
        ->assertCanSeeTableRecords([$this->issue1, $this->issue2, $this->issue3]);
});

it('shows empty state when filtering by repository with no issues', function () {
    $this->actingAs($this->user);
    
    $emptyRepo = Repository::factory()->create([
        'octo_connection_id' => $this->connection->id,
        'name' => 'Empty Repository',
    ]);
    
    Livewire::test(ManageIssues::class)
        ->filterTable('repository', $emptyRepo->id)
        ->assertCanNotSeeTableRecords([$this->issue1, $this->issue2, $this->issue3]);
});
