<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;

class TextbookChunkService
{
    /**
     * Extracts plain text from a supported file and stores chunks in textbook_chunks table.
     * Supported: .docx, .txt. PDFs are skipped unless preprocessed elsewhere.
     */
    public function ingest(string $publicDiskPath, ?int $textbookId = null): void
    {
        if (!$publicDiskPath) return;
        if (!Storage::disk('public')->exists($publicDiskPath)) return;
        $fullPath = Storage::disk('public')->path($publicDiskPath);
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        $text = '';
        try {
            if ($ext === 'txt' || $ext === 'csv') {
                $text = @file_get_contents($fullPath) ?: '';
            } elseif ($ext === 'docx') {
                $text = $this->extractDocx($fullPath);
            } elseif ($ext === 'pdf') {
                $text = $this->extractPdf($fullPath);
            } else {
                // Unsupported here. Skip silently.
                return;
            }
        } catch (\Throwable $e) {
            return;
        }

        $text = trim(preg_replace('/\s+/', ' ', $text));
        if ($text === '') return;

        // Safety cap to avoid huge inserts
        $maxTotal = 16000;
        if (mb_strlen($text) > $maxTotal) {
            $text = mb_substr($text, 0, $maxTotal);
        }

        // Create chunks ~1200 chars each
        $chunks = [];
        $chunkSize = 1200;
        for ($i = 0, $idx = 0; $i < mb_strlen($text); $i += $chunkSize, $idx++) {
            $chunks[] = [
                'textbook_id' => $textbookId,
                'source_path' => $publicDiskPath,
                'chunk_index' => $idx,
                'content' => mb_substr($text, $i, $chunkSize),
                'embedding' => null,
                'tokens_estimate' => (int)floor($chunkSize / 4),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Remove existing chunks for this path to avoid duplicates
        DB::table('textbook_chunks')->where('source_path', $publicDiskPath)->delete();
        if ($textbookId) {
            DB::table('textbook_chunks')->where('textbook_id', $textbookId)->delete();
        }
        foreach ($chunks as $row) {
            DB::table('textbook_chunks')->insert($row);
        }
    }

    protected function extractDocx(string $path): string
    {
        if (!class_exists('ZipArchive')) return '';
        $zip = new \ZipArchive();
        if ($zip->open($path) === true) {
            $index = $zip->locateName('word/document.xml');
            if ($index !== false) {
                $xml = $zip->getFromIndex($index);
                $zip->close();
                // Strip XML tags and decode entities
                $plain = strip_tags($xml ?? '');
                $plain = html_entity_decode($plain, ENT_QUOTES | ENT_XML1);
                return $plain;
            }
            $zip->close();
        }
        return '';
    }

    protected function extractPdf(string $path): string
    {
        try {
            $parser = new PdfParser();
            $doc = $parser->parseFile($path);
            $text = $doc->getText() ?? '';
            return (string) $text;
        } catch (\Throwable $e) {
            return '';
        }
    }
}
