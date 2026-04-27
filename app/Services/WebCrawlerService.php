<?php

namespace App\Services;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class WebCrawlerService
{
    protected $client;
    protected $baseUrl;
    protected $htmlContent;
    protected $crawledUrls = [];
    protected $linksToCrawl = [];

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);
    }

    /**
     * Crawl a website and return HTML content
     *
     * @param string $url
     * @return string HTML content
     */
    public function crawl(string $url): string
    {
        try {
            $this->baseUrl = $url;

            // Use curl with JavaScript rendering capabilities
            $html = $this->fetchWithCurl($url);

            if (empty($html)) {
                throw new Exception("Failed to fetch content from {$url}");
            }

            $this->htmlContent = $html;
            return $html;
        } catch (Exception $e) {
            throw new Exception("Error crawling URL: " . $e->getMessage());
        }
    }

    /**
     * Fetch URL content using curl with JavaScript rendering support
     */
    protected function fetchWithCurl(string $url): string
    {
        try {
            // First, try standard curl request
            $response = $this->client->get($url, [
                'allow_redirects' => true,
            ]);

            $html = (string) $response->getBody();

            // If content seems to be JavaScript-based (SPA), try to get it rendered
            if ($this->isLikelyJavaScriptRender($html)) {
                $html = $this->renderWithBrowser($url);
            }

            return $html;
        } catch (Exception $e) {
            // Fallback: try renderWithBrowser
            return $this->renderWithBrowser($url);
        }
    }

    /**
     * Check if HTML looks like it needs JavaScript rendering
     */
    protected function isLikelyJavaScriptRender(string $html): bool
    {
        // Check for common SPA/PWA indicators
        $indicators = [
            '<div id="app">' => 1,
            '<div id="root">' => 1,
            '<div id="__next">' => 1,
            'react' => 0.5,
            'vue' => 0.5,
            'angular' => 0.5,
            '__NEXT_DATA__' => 2,
            '__NUXT_DATA__' => 2,
        ];

        $score = 0;
        foreach ($indicators as $indicator => $weight) {
            if (stripos($html, $indicator) !== false) {
                $score += $weight;
            }
        }

        return $score >= 1;
    }

    /**
     * Render URL using a headless browser approach
     * This uses curl with a headless Chrome/Chromium endpoint if available
     */
    protected function renderWithBrowser(string $url): string
    {
        // Try to use local Chrome/Chromium if available
        $chromeOptions = $this->getChromeExecutablePath();

        if (!empty($chromeOptions)) {
            return $this->renderWithChrome($url, $chromeOptions);
        }

        // Fallback: basic curl request
        try {
            $response = $this->client->get($url);
            return (string) $response->getBody();
        } catch (Exception $e) {
            throw new Exception("Failed to render URL: " . $e->getMessage());
        }
    }

    /**
     * Render using Chrome/Chromium headless mode
     */
    protected function renderWithChrome(string $url, string $chromePath): string
    {
        $tempHtmlFile = tempnam(sys_get_temp_dir(), 'chrome_render_');

        try {
            // Create a command to run Chrome headless and save to temp file
            $escapedUrl = escapeshellarg($url);

            $command = "{$chromePath} " .
                "--headless=new " .
                "--disable-gpu " .
                "--dump-dom " .
                "$escapedUrl > " .
                escapeshellarg($tempHtmlFile) .
                " 2>&1";

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($tempHtmlFile)) {
                $html = file_get_contents($tempHtmlFile);
                unlink($tempHtmlFile);
                return $html;
            }

            // If Chrome rendering failed, return basic curl
            unlink($tempHtmlFile);
            $response = $this->client->get($url);
            return (string) $response->getBody();
        } catch (Exception $e) {
            if (file_exists($tempHtmlFile)) {
                unlink($tempHtmlFile);
            }

            // Fallback to basic curl
            $response = $this->client->get($url);
            return (string) $response->getBody();
        }
    }

    /**
     * Get path to Chrome/Chromium executable
     */
    protected function getChromeExecutablePath(): string
    {
        // Common paths for Chrome/Chromium on Windows
        $possiblePaths = [
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files\\Chromium\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Chromium\\Application\\chrome.exe',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return '';
    }

    /**
     * Save HTML content to a file
     */
    public function saveHtmlFile(string $url, string $filename): string
    {
        try {
            // Create storage path if it doesn't exist
            $storagePath = storage_path('app/crawled-html');
            if (!File::exists($storagePath)) {
                File::makeDirectory($storagePath, 0755, true);
            }

            // Generate filename if not provided
            if (empty($filename)) {
                $filename = $this->generateFilename($url);
            }

            $filePath = $storagePath . '/' . $filename;

            // Save the HTML content
            File::put($filePath, $this->htmlContent);

            return $filePath;
        } catch (Exception $e) {
            throw new Exception("Error saving HTML file: " . $e->getMessage());
        }
    }

    /**
     * Generate a filename from URL
     */
    protected function generateFilename(string $url): string
    {
        $parsed = parse_url($url);
        $domain = $parsed['host'] ?? 'unknown';
        $domain = str_replace('www.', '', $domain);

        $timestamp = now()->format('Y-m-d_H-i-s');
        return sanitize_filename($domain) . '_' . $timestamp . '.html';
    }

    /**
     * Get the currently crawled HTML content
     */
    public function getHtmlContent(): string
    {
        return $this->htmlContent;
    }

    /**
     * Get the base URL
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}

/**
 * Helper function to sanitize filename
 */
function sanitize_filename($filename)
{
    return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
}
