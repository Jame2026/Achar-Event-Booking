<?php

use App\Models\Event;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\File;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('images:backfill-cloudinary {--dry-run : Show what would change without writing}', function () {
    $disk = (string) config('media.event_image_disk', 'public');
    if ($disk !== 'cloudinary') {
        $this->error("EVENT_IMAGE_DISK must be 'cloudinary' before running this command. Current: {$disk}");

        return self::FAILURE;
    }

    $directory = trim((string) config('media.event_image_directory', 'services'), '/');
    $events = Event::query()
        ->whereNotNull('image_url')
        ->where('image_url', '!=', '')
        ->get(['id', 'image_url']);

    $dryRun = (bool) $this->option('dry-run');
    $migrated = 0;
    $missing = 0;
    $skipped = 0;
    $failed = 0;

    foreach ($events as $event) {
        $originalUrl = (string) $event->image_url;

        if (Str::contains($originalUrl, ['res.cloudinary.com', 'cloudinary.com'])) {
            $skipped++;
            continue;
        }

        $path = (string) parse_url($originalUrl, PHP_URL_PATH);
        if ($path === '') {
            $skipped++;
            continue;
        }

        $localPath = null;
        if (Str::startsWith($path, '/uploads/')) {
            $localPath = public_path(ltrim($path, '/'));
        } elseif (Str::startsWith($path, '/storage/')) {
            $localPath = storage_path('app/public/'.Str::after($path, '/storage/'));
        }

        if (! $localPath || ! is_file($localPath)) {
            $missing++;
            continue;
        }

        if ($dryRun) {
            $this->line("Would migrate event #{$event->id}: {$originalUrl}");
            $migrated++;
            continue;
        }

        try {
            $publicId = Str::uuid()->toString();
            $storedPath = Storage::disk($disk)->putFileAs(
                $directory,
                new File($localPath),
                $publicId,
                ['visibility' => 'public']
            );

            if (! is_string($storedPath) || $storedPath === '') {
                $failed++;
                $this->warn("Failed to upload event #{$event->id}: {$originalUrl}");
                continue;
            }

            $event->image_url = Storage::disk($disk)->url($storedPath);
            $event->save();
            $migrated++;
        } catch (\Throwable $e) {
            $failed++;
            $this->warn("Error migrating event #{$event->id}: {$e->getMessage()}");
        }
    }

    $this->newLine();
    $this->info("Migrated: {$migrated}");
    $this->info("Skipped (already cloud/unsupported URL): {$skipped}");
    $this->info("Missing local files: {$missing}");
    $this->info("Failed uploads: {$failed}");

    return self::SUCCESS;
})->purpose('Backfill existing local event images to Cloudinary and update image_url');
