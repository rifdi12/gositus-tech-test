<?php

namespace App\Libraries;

class VectorStoreService
{
    protected PdfProcessorService $pdfProcessor;
    protected QdrantService $qdrant;
    protected DeepSeekService $deepseek;

    public function __construct()
    {
        $this->pdfProcessor = new PdfProcessorService();
        $this->qdrant = new QdrantService();
        $this->deepseek = new DeepSeekService();
    }

    /**
     * Process and store PDF book in vector database
     *
     * @param string $pdfPath Path to PDF file
     * @param int $bookId Book ID
     * @param array $metadata Additional metadata
     * @return array Processing result
     */
    public function processAndStoreBook(string $pdfPath, int $bookId, array $metadata = []): array
    {
        try {
            // Generate collection name
            $collectionName = "book_{$bookId}";

            // Process PDF
            log_message('info', "Processing PDF for book {$bookId}");
            $processed = $this->pdfProcessor->processPdf($pdfPath, array_merge($metadata, [
                'book_id' => $bookId,
            ]));

            // Create collection if not exists
            if (!$this->qdrant->collectionExists($collectionName)) {
                log_message('info', "Creating Qdrant collection: {$collectionName}");
                $this->qdrant->createCollection($collectionName);
            }

            // Prepare points for insertion
            $points = [];
            foreach ($processed['chunks'] as $index => $chunk) {
                // Create simple embedding (in production, use proper embedding API)
                $vector = $this->qdrant->createSimpleEmbedding($chunk['text']);
                
                $points[] = [
                    'id'      => $index,
                    'vector'  => $vector,
                    'payload' => [
                        'text'        => $chunk['text'],
                        'book_id'     => $bookId,
                        'chunk_index' => $index,
                        'metadata'    => $chunk['metadata'],
                    ],
                ];
            }

            // Insert vectors
            log_message('info', "Inserting " . count($points) . " vectors for book {$bookId}");
            $this->qdrant->insertVectors($collectionName, $points);

            return [
                'success'         => true,
                'collection_name' => $collectionName,
                'chunks_count'    => count($points),
                'pages'           => $processed['pages'],
                'text_length'     => $processed['text_length'],
                'metadata'        => $processed['metadata'],
            ];
        } catch (\Exception $e) {
            log_message('error', "Vector store processing failed for book {$bookId}: " . $e->getMessage());
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Query book content using AI with RAG
     *
     * @param int $bookId Book ID
     * @param string $question User question
     * @param int $topK Number of relevant chunks to retrieve
     * @return array AI response with context
     */
    public function queryBook(int $bookId, string $question, int $topK = 5): array
    {
        try {
            $collectionName = "book_{$bookId}";

            // Check if collection exists
            if (!$this->qdrant->collectionExists($collectionName)) {
                return [
                    'success' => false,
                    'error'   => 'Book has not been processed yet. Please upload a PDF first.',
                    'answer'  => 'Buku ini belum diproses. Silakan upload file PDF terlebih dahulu.',
                ];
            }

            // Create query vector
            log_message('debug', "Creating embedding for question: {$question}");
            $queryVector = $this->qdrant->createSimpleEmbedding($question);
            log_message('debug', "Query vector size: " . count($queryVector));

            // Search for relevant chunks
            log_message('info', "Searching vectors for book {$bookId}, question: {$question}");
            $searchResults = $this->qdrant->search($collectionName, $queryVector, $topK);
            log_message('debug', "Search results count: " . count($searchResults));

            if (empty($searchResults)) {
                log_message('warning', "No search results found for book {$bookId}");
                return [
                    'success' => false,
                    'answer'  => 'Maaf, tidak ditemukan informasi yang relevan untuk menjawab pertanyaan Anda.',
                    'context' => [],
                ];
            }

            // Generate answer using DeepSeek with RAG
            log_message('info', "Generating AI response for book {$bookId}");
            $response = $this->deepseek->generateWithContext($question, $searchResults);

            // Add search results to response
            $response['context'] = $searchResults;
            $response['book_id'] = $bookId;

            return $response;
        } catch (\Exception $e) {
            log_message('error', "Query book failed for book {$bookId}: " . $e->getMessage());
            return [
                'success' => false,
                'error'   => $e->getMessage(),
                'answer'  => 'Maaf, terjadi kesalahan saat memproses pertanyaan Anda.',
            ];
        }
    }

    /**
     * Delete book vectors from database
     *
     * @param int $bookId Book ID
     * @return bool Success status
     */
    public function deleteBookVectors(int $bookId): bool
    {
        try {
            $collectionName = "book_{$bookId}";
            
            if ($this->qdrant->collectionExists($collectionName)) {
                log_message('info', "Deleting collection for book {$bookId}");
                return $this->qdrant->deleteCollection($collectionName);
            }
            
            return true;
        } catch (\Exception $e) {
            log_message('error', "Failed to delete vectors for book {$bookId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get book vector statistics
     *
     * @param int $bookId Book ID
     * @return array Statistics
     */
    public function getBookStats(int $bookId): array
    {
        try {
            $collectionName = "book_{$bookId}";
            
            if (!$this->qdrant->collectionExists($collectionName)) {
                return [
                    'exists' => false,
                    'message' => 'Book not indexed',
                ];
            }

            $info = $this->qdrant->getCollectionInfo($collectionName);
            
            return [
                'exists'        => true,
                'collection'    => $collectionName,
                'vectors_count' => $info['vectors_count'] ?? 0,
                'points_count'  => $info['points_count'] ?? 0,
                'status'        => $info['status'] ?? 'unknown',
            ];
        } catch (\Exception $e) {
            log_message('error', "Failed to get stats for book {$bookId}: " . $e->getMessage());
            return [
                'exists' => false,
                'error'  => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if services are properly configured
     *
     * @return array Configuration status
     */
    public function checkConfiguration(): array
    {
        return [
            'qdrant'   => $this->qdrant->collectionExists('test') !== null,
            'deepseek' => $this->deepseek->isConfigured(),
        ];
    }
}
