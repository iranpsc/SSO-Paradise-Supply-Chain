<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class DeleteUnverifiedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:delete-unverified {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete users that were created more than 24 hours ago and have not verified their email address';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        // Calculate the cutoff time (24 hours ago)
        $cutoffTime = Carbon::now()->subHours(24);

        // Find users that:
        // 1. Have not verified their email (email_verified_at is null)
        // 2. Were created more than 24 hours ago
        $unverifiedUsers = User::whereNull('email_verified_at')
            ->where('created_at', '<', $cutoffTime)
            ->get();

        $count = $unverifiedUsers->count();

        if ($count === 0) {
            $this->info('No unverified users found that are older than 24 hours.');
            return Command::SUCCESS;
        }

        if ($isDryRun) {
            $this->warn("DRY RUN MODE - No users will be deleted");
            $this->line('');
            $this->info("Found {$count} unverified user(s) that would be deleted:");
            $this->line('');

            $this->table(
                ['ID', 'Email', 'Name', 'Created At'],
                $unverifiedUsers->map(function ($user) {
                    return [
                        $user->id,
                        $user->email,
                        $user->name,
                        $user->created_at->format('Y-m-d H:i:s'),
                    ];
                })->toArray()
            );

            return Command::SUCCESS;
        }

        // Show what will be deleted
        $this->info("Found {$count} unverified user(s) to delete:");
        $this->line('');

        $this->table(
            ['ID', 'Email', 'Name', 'Created At'],
            $unverifiedUsers->map(function ($user) {
                return [
                    $user->id,
                    $user->email,
                    $user->name,
                    $user->created_at->format('Y-m-d H:i:s'),
                ];
            })->toArray()
        );

        // Confirm deletion (skip if running non-interactively, e.g., from scheduler)
        if ($this->input->isInteractive() && !$this->confirm('Do you want to delete these users?', true)) {
            $this->info('Deletion cancelled.');
            return Command::SUCCESS;
        }

        // Delete users
        $deletedCount = 0;
        foreach ($unverifiedUsers as $user) {
            try {
                $user->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                $this->error("Failed to delete user {$user->id} ({$user->email}): {$e->getMessage()}");
            }
        }

        $this->info("Successfully deleted {$deletedCount} unverified user(s).");

        return Command::SUCCESS;
    }
}

