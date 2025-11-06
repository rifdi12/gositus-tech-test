<?php

namespace App\Libraries;

use Smalot\PdfParser\Parser;

class PdfProcessorService
{
    protected Parser $parser;
    protected int $chunkSize = 1000; // Characters per chunk
    protected int $chunkOverlap = 200; // Overlap between chunks

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Extract text from PDF file
     *
     * @param string $filePath Path to PDF file
     * @return array ['text' => string, 'pages' => int]
     */
    public function extractText(string $filePath): array
    {
        try {
            $pdf = $this->parser->parseFile($filePath);
            $text = $pdf->getText();
            $pages = count($pdf->getPages());
            
            // Limit text size to prevent memory issues
            $maxTextLength = 500000; // ~500KB of text (about 100 pages)
            if (mb_strlen($text) > $maxTextLength) {
                log_message('warning', "PDF text too large, truncating from " . mb_strlen($text) . " to {$maxTextLength} chars");
                $text = mb_substr($text, 0, $maxTextLength);
            }

            return [
                'text'  => $this->cleanText($text),
                'pages' => $pages,
            ];
        } catch (\Exception $e) {
            log_message('error', 'PDF extraction failed: ' . $e->getMessage());
            throw new \RuntimeException('Failed to extract PDF text: ' . $e->getMessage());
        }
    }

    /**
     * Split text into chunks for vector storage
     *
     * @param string $text Full text to split
     * @param array $metadata Additional metadata
     * @return array Array of chunks with metadata
     */
    public function splitIntoChunks(string $text, array $metadata = []): array
    {
        $chunks = [];
        $textLength = mb_strlen($text);
        $position = 0;
        $chunkIndex = 0;
        $maxChunks = 500; // Safety limit to prevent runaway memory

        while ($position < $textLength && $chunkIndex < $maxChunks) {
            // Extract chunk
            $chunk = mb_substr($text, $position, $this->chunkSize);
            
            // Try to end at sentence boundary
            if ($position + $this->chunkSize < $textLength) {
                $lastPeriod = mb_strrpos($chunk, '.');
                $lastNewline = mb_strrpos($chunk, "\n");
                $breakPoint = max($lastPeriod, $lastNewline);
                
                if ($breakPoint !== false && $breakPoint > $this->chunkSize * 0.7) {
                    $chunk = mb_substr($chunk, 0, $breakPoint + 1);
                }
            }

            $actualChunkLength = mb_strlen($chunk);
            
            // Prevent infinite loop - ensure we always move forward
            if ($actualChunkLength == 0 || $actualChunkLength <= $this->chunkOverlap) {
                $position += max(1, $this->chunkSize);
                continue;
            }

            // Clean and add chunk
            $cleanChunk = trim($chunk);
            if (!empty($cleanChunk) && mb_strlen($cleanChunk) > 50) {
                $chunks[] = [
                    'text'     => $cleanChunk,
                    'metadata' => array_merge($metadata, [
                        'chunk_index' => $chunkIndex,
                        'chunk_size'  => mb_strlen($cleanChunk),
                    ]),
                ];
                $chunkIndex++;
            }

            // Move position with overlap (always positive movement)
            $movement = max(1, $actualChunkLength - $this->chunkOverlap);
            $position += $movement;
        }

        if ($chunkIndex >= $maxChunks) {
            log_message('warning', "PDF chunking stopped at max limit: {$maxChunks} chunks");
        }

        return $chunks;
    }

    /**
     * Clean extracted text
     *
     * @param string $text Raw text from PDF
     * @return string Cleaned text
     */
    protected function cleanText(string $text): string
    {
        // Remove excessive whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Remove non-printable characters
        $text = preg_replace('/[^\P{C}\n\t]/u', '', $text);
        
        // Normalize line breaks
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        
        // Remove multiple consecutive newlines
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        
        return trim($text);
    }

    /**
     * Get metadata from PDF
     *
     * @param string $filePath Path to PDF file
     * @return array Metadata information
     */
    public function getMetadata(string $filePath): array
    {
        try {
            $pdf = $this->parser->parseFile($filePath);
            $details = $pdf->getDetails();

            return [
                'title'    => $details['Title'] ?? '',
                'author'   => $details['Author'] ?? '',
                'subject'  => $details['Subject'] ?? '',
                'keywords' => $details['Keywords'] ?? '',
                'creator'  => $details['Creator'] ?? '',
                'producer' => $details['Producer'] ?? '',
                'created'  => $details['CreationDate'] ?? '',
                'modified' => $details['ModDate'] ?? '',
            ];
        } catch (\Exception $e) {
            log_message('error', 'PDF metadata extraction failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Process PDF file: extract text and split into chunks
     *
     * @param string $filePath Path to PDF file
     * @param array $additionalMetadata Additional metadata
     * @return array ['chunks' => array, 'pages' => int, 'metadata' => array]
     */
    public function processPdf(string $filePath, array $additionalMetadata = []): array
    {
        // Extract text
        $extracted = $this->extractText($filePath);
        
        // Get PDF metadata
        $pdfMetadata = $this->getMetadata($filePath);
        
        // Merge metadata
        $metadata = array_merge($pdfMetadata, $additionalMetadata);
        
        // Split into chunks
        $chunks = $this->splitIntoChunks($extracted['text'], $metadata);

        return [
            'chunks'   => $chunks,
            'pages'    => $extracted['pages'],
            'metadata' => $metadata,
            'text_length' => mb_strlen($extracted['text']),
        ];
    }

    /**
     * Set chunk size and overlap
     *
     * @param int $size Chunk size in characters
     * @param int $overlap Overlap size in characters
     */
    public function setChunkParameters(int $size, int $overlap): void
    {
        $this->chunkSize = $size;
        $this->chunkOverlap = $overlap;
    }
}
