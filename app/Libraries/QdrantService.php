<?php

namespace App\Libraries;

use Exception;
use Qdrant\Qdrant;
use Qdrant\Config;
use Qdrant\Http\Builder;
use Qdrant\Models\Request\CreateCollection;
use Qdrant\Models\Request\VectorParams;
use Qdrant\Models\PointsStruct;
use Qdrant\Models\PointStruct;
use Qdrant\Models\VectorStruct;
use Qdrant\Models\Filter\Filter;
use Qdrant\Models\Filter\Condition\MatchInt;
use Qdrant\Models\Request\SearchRequest;

/**
 * Service for interacting with Qdrant Vector Database
 * Using hkulekci/qdrant package
 */
class QdrantService
{
    private $client;
    private $host;
    private $port;
    protected int $vectorDimension = 384; // Simple embedding dimension

    public function __construct()
    {
        $this->host = getenv('QDRANT_HOST') ?: 'qdrant';
        $this->port = getenv('QDRANT_PORT') ?: '6333';

        $config = new Config($this->host . ':' . $this->port);
        $transport = (new Builder())->build($config);
        $this->client = new Qdrant($transport);
    }

    /**
     * Create a collection for storing vectors
     */
    public function createCollection(string $collectionName, int $vectorSize = null): bool
    {
        try {
            $vectorSize = $vectorSize ?? $this->vectorDimension;
            
            $createCollection = new CreateCollection();
            $createCollection->addVector(
                new VectorParams($vectorSize, VectorParams::DISTANCE_COSINE), 
                'content'
            );
            
            $this->client->collections($collectionName)->create($createCollection);
            
            log_message('info', "Qdrant collection created: {$collectionName}");
            return true;
        } catch (Exception $e) {
            log_message('error', "Failed to create Qdrant collection: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Insert vectors into collection
     */
    public function insertVectors(string $collectionName, array $vectors): bool
    {
        try {
            $points = new PointsStruct();
            
            foreach ($vectors as $vector) {
                $points->addPoint(
                    new PointStruct(
                        $vector['id'],
                        new VectorStruct($vector['vector'], 'content'),
                        $vector['payload'] ?? []
                    )
                );
            }
            
            $this->client->collections($collectionName)->points()->upsert($points, ['wait' => 'true']);
            
            log_message('info', "Inserted " . count($vectors) . " vectors into {$collectionName}");
            return true;
        } catch (Exception $e) {
            log_message('error', "Failed to insert vectors: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Search for similar vectors
     */
    public function search(string $collectionName, array $queryVector, int $limit = 5, array $filter = null): array
    {
        try {
            $searchRequest = (new SearchRequest(new VectorStruct($queryVector, 'content')))
                ->setLimit($limit)
                ->setWithPayload(true);
            
            if ($filter) {
                $qdrantFilter = new Filter();
                foreach ($filter as $key => $value) {
                    $qdrantFilter->addMust(new MatchInt($key, $value));
                }
                $searchRequest->setFilter($qdrantFilter);
            }
            
            $response = $this->client->collections($collectionName)->points()->search($searchRequest);
            
            // Response implements ArrayAccess, get raw data
            $responseData = $response->__toArray();
            
            $points = $responseData['result'] ?? [];
            
            $results = [];
            foreach ($points as $point) {
                $results[] = [
                    'id' => $point['id'] ?? null,
                    'score' => $point['score'] ?? 0,
                    'payload' => $point['payload'] ?? []
                ];
            }
            
            if (function_exists('log_message')) {
                log_message('debug', "Qdrant search returned " . count($results) . " results");
            }
            
            return $results;
        } catch (Exception $e) {
            if (function_exists('log_message')) {
                log_message('error', "Qdrant search failed: " . $e->getMessage());
            }
            return [];
        }
    }

    /**
     * Delete a collection
     */
    public function deleteCollection(string $collectionName): bool
    {
        try {
            $this->client->collections($collectionName)->delete();
            log_message('info', "Deleted Qdrant collection: {$collectionName}");
            return true;
        } catch (Exception $e) {
            log_message('error', "Failed to delete collection: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if collection exists
     */
    public function collectionExists(string $collectionName): bool
    {
        try {
            $this->client->collections($collectionName)->info();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Create simple embeddings for text (placeholder)
     * In production, use OpenAI ada-002 or sentence-transformers
     */
    public function createSimpleEmbedding(string $text): array
    {
        // Simple hash-based embedding for development
        // Replace with real embedding API in production
        $embedding = [];
        $hash = md5($text);
        
        for ($i = 0; $i < $this->vectorDimension; $i++) {
            $seed = hexdec(substr($hash, $i % 32, 2));
            $embedding[] = (float)(($seed / 255.0) * 2.0 - 1.0);
        }
        
        // Normalize
        $magnitude = sqrt(array_sum(array_map(fn($x) => $x * $x, $embedding)));
        if ($magnitude > 0) {
            $embedding = array_map(fn($x) => $x / $magnitude, $embedding);
        }
        
        return $embedding;
    }

    /**
     * Get collection info
     */
    public function getCollectionInfo(string $collectionName): ?array
    {
        try {
            $response = $this->client->collections($collectionName)->info();
            return $response;
        } catch (Exception $e) {
            log_message('error', "Failed to get collection info: " . $e->getMessage());
            return null;
        }
    }
}
