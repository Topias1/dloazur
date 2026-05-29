<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Image\Image;

class OptimizeVitrineImages extends Command
{
    protected $signature = 'images:optimize {--force : Regenerate even if siblings are newer}';

    protected $description = 'Generate .webp and .avif siblings for vitrine .jpg assets (spatie/image GD driver)';

    public function handle(): int
    {
        $root = public_path('assets');
        $patterns = [
            $root . '/brand/photos/*.jpg',
            $root . '/brand/*.jpg',
            $root . '/blog/*.jpg',
        ];

        $jpgs = [];
        foreach ($patterns as $pattern) {
            foreach (glob($pattern) as $file) {
                $jpgs[$file] = true;
            }
        }
        $jpgs = array_keys($jpgs);

        if (empty($jpgs)) {
            $this->warn('No .jpg files found under ' . $root . '/assets.');
            return self::SUCCESS;
        }

        $this->line('Found ' . count($jpgs) . ' JPG(s) under public/assets.');

        $generated = 0;
        $skipped = 0;

        foreach ($jpgs as $jpg) {
            $webp = preg_replace('/\.jpg$/i', '.webp', $jpg);
            $avif = preg_replace('/\.jpg$/i', '.avif', $jpg);

            $this->processFormat($jpg, $webp, 'webp', $generated, $skipped);
            $this->processFormat($jpg, $avif, 'avif', $generated, $skipped);
        }

        $this->info("Done. Generated: {$generated}, Skipped (up-to-date): {$skipped}.");

        return self::SUCCESS;
    }

    private function processFormat(string $jpg, string $target, string $fmt, int &$generated, int &$skipped): void
    {
        $force = $this->option('force');
        $name = basename($target);

        if (! $force && file_exists($target) && filemtime($target) >= filemtime($jpg)) {
            $this->line("  skip  {$name} (up-to-date)");
            $skipped++;

            return;
        }

        Image::load($jpg)->save($target);
        $this->line("  <info>wrote</info> {$name}");
        $generated++;
    }
}
