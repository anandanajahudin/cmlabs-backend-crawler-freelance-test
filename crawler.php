<?php

/**
 * Simple Web Crawler Script
 * Crawl websites dan save HTML ke file
 */

class SimpleWebCrawler
{
    protected $url;
    protected $htmlContent;
    protected $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';

    public function crawl($url)
    {
        $this->url = $url;

        // Use curl to fetch the website
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_ENCODING => 'gzip, deflate',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate',
                'DNT: 1',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1'
            ]
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            throw new Exception("Curl error: $error");
        }

        if ($httpCode !== 200) {
            throw new Exception("HTTP error: $httpCode");
        }

        if (empty($response)) {
            throw new Exception("Empty response from server");
        }

        $this->htmlContent = $response;
        return $response;
    }

    public function saveToFile($filename = null)
    {
        if (empty($this->htmlContent)) {
            throw new Exception("No HTML content to save");
        }

        // Create storage directory
        $storageDir = __DIR__ . '/storage/crawled-html';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        // Generate filename
        if (empty($filename)) {
            $parsed = parse_url($this->url);
            $domain = str_replace('www.', '', $parsed['host'] ?? 'unknown');
            $filename = sanitize_filename($domain) . '_' . date('Y-m-d_H-i-s') . '.html';
        }

        $filePath = $storageDir . '/' . $filename;

        if (file_put_contents($filePath, $this->htmlContent) === false) {
            throw new Exception("Failed to write file: $filePath");
        }

        return $filePath;
    }

    public function getHtmlContent()
    {
        return $this->htmlContent;
    }
}

function sanitize_filename($filename)
{
    return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
}

// Main execution
if (php_sapi_name() === 'cli') {
    if ($argc < 2) {
        echo "Usage: php crawler.php <url> [filename]\n";
        echo "Example: php crawler.php https://example.com example.html\n";
        exit(1);
    }

    $url = $argv[1];
    $filename = $argv[2] ?? null;

    try {
        $crawler = new SimpleWebCrawler();

        echo "🔍 Crawling: $url\n";
        echo str_repeat("-", 50) . "\n";

        $html = $crawler->crawl($url);
        echo "✓ Content fetched: " . strlen($html) . " bytes\n";

        $filePath = $crawler->saveToFile($filename);
        echo "✓ Saved to: $filePath\n";
        echo "✓ File size: " . filesize($filePath) . " bytes\n";

        echo str_repeat("-", 50) . "\n";
        echo "✓ Crawling completed successfully!\n";

    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
