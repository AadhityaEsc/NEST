<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Laravel 12 uses routes/console.php; the uploaded Kernel.php snippet is obsolete.
Schedule::command('listings:clean')->everyMinute()->withoutOverlapping();
