<?php

namespace App\Libraries;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class DeepSeekService
{
    protected Client $client;
    protected string $apiKey;
    protected string $apiUrl = 'https://api.deepseek.com/v1/chat/completions';
    protected string $model = 'deepseek-chat';

    public function __construct()
    {
        $this->apiKey = getenv('DEEPSEEK_API_KEY') ?: '';
        
        $this->client = new Client([
            'timeout' => 60,
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ],
        ]);
    }

    /**
     * Generate response using RAG (Retrieval-Augmented Generation)
     *
     * @param string $question User question
     * @param array $context Relevant context from vector search
     * @param array $options Additional options
     * @return array Response with answer and metadata
     */
    public function generateWithContext(string $question, array $context, array $options = []): array
    {
        try {
            $systemPrompt = $this->buildSystemPrompt();
            $contextPrompt = $this->buildContextPrompt($context);
            $userPrompt = $this->buildUserPrompt($question, $contextPrompt);

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ];

            $response = $this->chat($messages, $options);

            return [
                'success' => true,
                'answer'  => $response['content'] ?? '',
                'model'   => $response['model'] ?? $this->model,
                'tokens'  => $response['tokens'] ?? 0,
                'context_used' => count($context),
            ];
        } catch (\Exception $e) {
            log_message('error', 'DeepSeek RAG failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error'   => $e->getMessage(),
                'answer'  => 'Maaf, terjadi kesalahan saat memproses pertanyaan Anda.',
            ];
        }
    }

    /**
     * Send chat request to DeepSeek API
     *
     * @param array $messages Conversation messages
     * @param array $options Additional options
     * @return array API response
     */
    protected function chat(array $messages, array $options = []): array
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('DeepSeek API key not configured');
        }

        $payload = [
            'model'       => $options['model'] ?? $this->model,
            'messages'    => $messages,
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens'  => $options['max_tokens'] ?? 2000,
            'stream'      => false,
        ];

        try {
            $response = $this->client->post($this->apiUrl, [
                'json' => $payload,
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            return [
                'content' => $body['choices'][0]['message']['content'] ?? '',
                'model'   => $body['model'] ?? '',
                'tokens'  => $body['usage']['total_tokens'] ?? 0,
            ];
        } catch (GuzzleException $e) {
            log_message('error', 'DeepSeek API request failed: ' . $e->getMessage());
            throw new \RuntimeException('Failed to get response from DeepSeek: ' . $e->getMessage());
        }
    }

    /**
     * Build system prompt for the AI
     *
     * @return string System prompt
     */
    protected function buildSystemPrompt(): string
    {
        return <<<PROMPT
Anda adalah asisten AI yang membantu pengguna memahami isi buku di perpustakaan digital E-Library.

Tugas Anda:
1. Menjawab pertanyaan pengguna berdasarkan konteks yang diberikan dari buku
2. Memberikan jawaban yang akurat, informatif, dan mudah dipahami
3. Jika informasi tidak ada dalam konteks, katakan dengan jujur
4. Gunakan bahasa Indonesia yang baik dan sopan
5. Berikan referensi ke bagian spesifik dalam buku jika memungkinkan

Panduan:
- Fokus pada fakta yang ada dalam buku
- Jangan membuat informasi yang tidak ada dalam konteks
- Jika pertanyaan tidak relevan dengan buku, arahkan kembali ke topik buku
- Berikan jawaban yang ringkas namun lengkap
PROMPT;
    }

    /**
     * Build context prompt from search results
     *
     * @param array $context Context from vector search
     * @return string Formatted context
     */
    protected function buildContextPrompt(array $context): string
    {
        if (empty($context)) {
            return 'Tidak ada konteks yang relevan ditemukan.';
        }

        $contextText = "Berikut adalah bagian-bagian relevan dari buku:\n\n";
        
        foreach ($context as $index => $item) {
            $chunk = $item['payload']['text'] ?? '';
            $score = $item['score'] ?? 0;
            $chunkIndex = $item['payload']['chunk_index'] ?? $index;
            
            $contextText .= "--- Bagian " . ($index + 1) . " (Relevansi: " . number_format($score * 100, 1) . "%) ---\n";
            $contextText .= $chunk . "\n\n";
        }

        return $contextText;
    }

    /**
     * Build user prompt with question and context
     *
     * @param string $question User question
     * @param string $context Formatted context
     * @return string Complete user prompt
     */
    protected function buildUserPrompt(string $question, string $context): string
    {
        return <<<PROMPT
{$context}

Berdasarkan konteks di atas, jawab pertanyaan berikut:

{$question}

Instruksi:
- Gunakan HANYA informasi dari konteks yang diberikan
- Jika informasi tidak cukup, katakan bahwa Anda perlu informasi lebih lanjut
- Berikan jawaban dalam bahasa Indonesia yang jelas dan terstruktur
- Sertakan referensi ke bagian spesifik jika memungkinkan
PROMPT;
    }

    /**
     * Simple chat without context (fallback)
     *
     * @param string $message User message
     * @param array $options Additional options
     * @return array Response
     */
    public function simpleChat(string $message, array $options = []): array
    {
        try {
            $messages = [
                ['role' => 'system', 'content' => 'Anda adalah asisten perpustakaan digital yang membantu pengguna.'],
                ['role' => 'user', 'content' => $message],
            ];

            $response = $this->chat($messages, $options);

            return [
                'success' => true,
                'answer'  => $response['content'] ?? '',
                'tokens'  => $response['tokens'] ?? 0,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
                'answer'  => 'Maaf, terjadi kesalahan saat memproses pertanyaan Anda.',
            ];
        }
    }

    /**
     * Validate API key configuration
     *
     * @return bool True if API key is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Set API key
     *
     * @param string $apiKey DeepSeek API key
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
        $this->client = new Client([
            'timeout' => 60,
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ],
        ]);
    }

    /**
     * Set model
     *
     * @param string $model Model name
     */
    public function setModel(string $model): void
    {
        $this->model = $model;
    }
}
