<?php

use Devdojo\Changelog\Http\Controllers\ChangelogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('changelog/read', [ChangelogController::class, 'read'])->name('changelog.read');
});
