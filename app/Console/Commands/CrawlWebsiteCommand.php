<?php

namespace App\Console\Commands;

use App\Services\WebCrawlerService;
use Illuminate\Console\Command;
use Exception;

class CrawlWebsiteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawler:crawl
                            {url : The URL of the website to crawl}
                            {--filename= : Optional filename for the saved HTML}
                            {--no-save : Only crawl without saving to file}
                            {--urls=* : Multiple URLs to crawl}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl a website and save its HTML content';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $crawlerService = app(WebCrawlerService::class);

            $urls = $this->option('urls');

            // If no multiple URLs provided, use the single URL argument
            if (empty($urls)) {
                $urls = [$this->argument('url')];
            } else {
                $urls[] = $this->argument('url');
            }

            $noSave = $this->option('no-save');
            $filename = $this->option('filename');

            foreach ($urls as $url) {
                $this->info("Starting to crawl: $url");
                $this->line('');

                try {
                    $bar = $this->output->createProgressBar(3);
                    $bar->start();

                    // Crawl the URL
                    $bar->setMessage('Fetching website content...');
                    $html = $crawlerService->crawl($url);
                    $bar->advance();

                    $this->line('');
                    $this->info("✓ Successfully fetched content (" . strlen($html) . " bytes)");

                    // Save to file if not disabled
                    if (!$noSave) {
                        $bar->setMessage('Saving to HTML file...');
                        $filePath = $crawlerService->saveHtmlFile($url, $filename);
                        $bar->advance();

                        $this->line('');
                        $this->info("✓ Saved to: $filePath");
                    }

                    $bar->finish();
                    $this->line('');
                    $this->line('');

                } catch (Exception $e) {
                    $this->error("✗ Error crawling $url: " . $e->getMessage());
                    continue;
                }
            }

            $this->info('✓ Crawling completed!');
            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
