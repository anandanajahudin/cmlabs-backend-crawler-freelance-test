<?php

namespace App\Http\Controllers;

use App\Services\WebCrawlerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class CrawlerController extends Controller
{
    protected $crawlerService;

    public function __construct(WebCrawlerService $crawlerService)
    {
        $this->crawlerService = $crawlerService;
    }

    /**
     * Crawl a website and return HTML content
     */
    public function crawl(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'url' => 'required|url'
            ]);

            $url = $request->input('url');

            $html = $this->crawlerService->crawl($url);

            return response()->json([
                'success'     => true,
                'url'         => $url,
                'message'     => 'Website crawled successfully',
                'html_length' => strlen($html),
                'page_title'  => $this->extractTitle($html),
                'links_count' => $this->countLinks($html),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 400);
        }
    }

    private function extractTitle(string $html): string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
            return trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES, 'UTF-8'));
        }
        return '';
    }

    private function countLinks(string $html): int
    {
        return preg_match_all('/<a\s[^>]*href=/i', $html, $m);
    }

    /**
     * Crawl website and save to HTML file
     */
    public function crawlAndSave(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'url' => 'required|url',
                'filename' => 'nullable|string'
            ]);

            $url = $request->input('url');
            $filename = $request->input('filename', '');

            $html     = $this->crawlerService->crawl($url);
            $filePath = $this->crawlerService->saveHtmlFile($url, $filename);

            return response()->json([
                'success'     => true,
                'url'         => $url,
                'message'     => 'Website crawled and saved successfully',
                'html_length' => strlen($html),
                'page_title'  => $this->extractTitle($html),
                'links_count' => $this->countLinks($html),
                'file_path'   => $filePath,
                'file_name'   => basename($filePath),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get list of crawled HTML files
     */
    public function listCrawledFiles(): JsonResponse
    {
        try {
            $storagePath = storage_path('app/crawled-html');

            if (!is_dir($storagePath)) {
                return response()->json([
                    'success' => true,
                    'files' => [],
                    'message' => 'No crawled files found'
                ], 200);
            }

            $files = array_diff(scandir($storagePath), ['.', '..']);
            $fileDetails = [];

            foreach ($files as $file) {
                $filePath = $storagePath . '/' . $file;
                if (is_file($filePath)) {
                    $fileDetails[] = [
                        'name' => $file,
                        'size' => filesize($filePath),
                        'size_mb' => round(filesize($filePath) / 1024 / 1024, 2),
                        'created_at' => date('Y-m-d H:i:s', filectime($filePath)),
                        'url' => route('crawler.download', ['filename' => $file])
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'files' => $fileDetails,
                'count' => count($fileDetails)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Download a crawled HTML file
     */
    public function downloadFile(string $filename): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        try {
            $storagePath = storage_path('app/crawled-html');
            $filePath = $storagePath . '/' . $filename;

            if (!file_exists($filePath)) {
                abort(404, 'File not found');
            }

            return response()->download($filePath, $filename);
        } catch (Exception $e) {
            abort(400, $e->getMessage());
        }
    }

    /**
     * View crawled HTML file in browser
     */
    public function viewFile(string $filename)
    {
        try {
            $storagePath = storage_path('app/crawled-html');
            $filePath = $storagePath . '/' . $filename;

            if (!file_exists($filePath)) {
                abort(404, 'File not found');
            }

            $html = file_get_contents($filePath);
            return response($html)->header('Content-Type', 'text/html; charset=utf-8');
        } catch (Exception $e) {
            abort(400, $e->getMessage());
        }
    }

    /**
     * Delete a crawled HTML file
     */
    public function deleteFile(string $filename): JsonResponse
    {
        try {
            $storagePath = storage_path('app/crawled-html');
            $filePath    = realpath($storagePath . '/' . $filename);

            if ($filePath === false || !str_starts_with($filePath, realpath($storagePath))) {
                return response()->json(['success' => false, 'error' => 'Invalid filename'], 400);
            }

            if (!file_exists($filePath)) {
                return response()->json(['success' => false, 'error' => 'File not found'], 404);
            }

            unlink($filePath);

            return response()->json(['success' => true, 'message' => "File '{$filename}' deleted"]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
