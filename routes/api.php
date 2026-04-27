<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CrawlerController;

Route::prefix('api/crawler')->group(function () {
    Route::post('/crawl', [CrawlerController::class, 'crawl'])->name('crawler.crawl');
    Route::post('/crawl-and-save', [CrawlerController::class, 'crawlAndSave'])->name('crawler.crawl-and-save');
    Route::get('/files', [CrawlerController::class, 'listCrawledFiles'])->name('crawler.list');
    Route::get('/download/{filename}', [CrawlerController::class, 'downloadFile'])->name('crawler.download');
    Route::get('/view/{filename}', [CrawlerController::class, 'viewFile'])->name('crawler.view');
});
