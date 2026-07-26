<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteUnverifiedUsersCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_reports_when_no_unverified_users_are_eligible(): void
    {
        User::factory()->create(); // verified, should be ignored
        User::factory()->unverified()->create([
            'created_at' => now()->subHours(12), // too recent
        ]);

        $this->artisan('users:delete-unverified')
            ->expectsOutput('No unverified users found that are older than 24 hours.')
            ->assertSuccessful();

        $this->assertDatabaseCount('users', 2);
    }

    #[Test]
    public function dry_run_lists_eligible_users_without_deleting(): void
    {
        $eligible = User::factory()->unverified()->create([
            'email' => 'old-unverified@example.com',
            'name' => 'Old Unverified',
            'created_at' => now()->subHours(25),
        ]);

        User::factory()->create([
            'created_at' => now()->subDays(3),
        ]);

        $this->artisan('users:delete-unverified', ['--dry-run' => true])
            ->expectsOutput('DRY RUN MODE - No users will be deleted')
            ->expectsOutput('Found 1 unverified user(s) that would be deleted:')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', ['id' => $eligible->id]);
        $this->assertDatabaseCount('users', 2);
    }

    #[Test]
    public function interactive_cancel_leaves_users_intact(): void
    {
        $eligible = User::factory()->unverified()->create([
            'created_at' => now()->subDays(2),
        ]);

        $this->artisan('users:delete-unverified')
            ->expectsConfirmation('Do you want to delete these users?', 'no')
            ->expectsOutput('Deletion cancelled.')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', ['id' => $eligible->id]);
    }

    #[Test]
    public function it_deletes_eligible_unverified_users_when_confirmed(): void
    {
        $eligible = User::factory()->unverified()->create([
            'email' => 'delete-me@example.com',
            'created_at' => now()->subDays(2),
        ]);
        $keep = User::factory()->unverified()->create([
            'created_at' => now()->subHours(6),
        ]);
        $verified = User::factory()->create([
            'created_at' => now()->subDays(5),
        ]);

        $this->artisan('users:delete-unverified')
            ->expectsConfirmation('Do you want to delete these users?', 'yes')
            ->expectsOutput('Successfully deleted 1 unverified user(s).')
            ->assertSuccessful();

        $this->assertDatabaseMissing('users', ['id' => $eligible->id]);
        $this->assertDatabaseHas('users', ['id' => $keep->id]);
        $this->assertDatabaseHas('users', ['id' => $verified->id]);
    }

    #[Test]
    public function non_interactive_run_deletes_without_confirmation(): void
    {
        $eligible = User::factory()->unverified()->create([
            'created_at' => now()->subDays(2),
        ]);

        // Scheduler / CI style: no TTY confirmation prompt.
        $this->artisan('users:delete-unverified', ['--no-interaction' => true])
            ->expectsOutput('Successfully deleted 1 unverified user(s).')
            ->assertSuccessful();

        $this->assertDatabaseMissing('users', ['id' => $eligible->id]);
    }

    #[Test]
    public function it_reports_when_an_individual_delete_fails(): void
    {
        $eligible = User::factory()->unverified()->create([
            'email' => 'fail-delete@example.com',
            'created_at' => now()->subDays(2),
        ]);

        User::deleting(function (User $user) {
            throw new \RuntimeException('forced delete failure');
        });

        $this->artisan('users:delete-unverified', ['--no-interaction' => true])
            ->expectsOutputToContain("Failed to delete user {$eligible->id} ({$eligible->email}): forced delete failure")
            ->expectsOutput('Successfully deleted 0 unverified user(s).')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', ['id' => $eligible->id]);
    }
}
