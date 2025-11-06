<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <div class="row">
        <!-- Book Information -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-body">
                    <!-- Book Cover -->
                    <div class="text-center mb-3">
                        <?php if ($book['image']): ?>
                            <img src="/uploads/books/<?= esc($book['image']) ?>" 
                                 class="img-fluid rounded" 
                                 style="max-height: 300px; object-fit: cover;" 
                                 alt="<?= esc($book['title']) ?>">
                        <?php else: ?>
                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center" 
                                 style="height: 300px;">
                                <i class="bi bi-book" style="font-size: 4rem; color: white;"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Book Title -->
                    <h4 class="card-title mb-3"><?= esc($book['title']) ?></h4>

                    <!-- Book Description -->
                    <?php if ($book['description']): ?>
                        <p class="card-text text-muted small">
                            <?= nl2br(esc($book['description'])) ?>
                        </p>
                    <?php endif; ?>

                    <!-- Book Info -->
                    <div class="border-top pt-3 mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">
                                <i class="bi bi-file-pdf"></i> PDF
                            </span>
                            <?php if ($book['pdf_file']): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Tersedia
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-x-circle"></i> Tidak Ada
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">
                                <i class="bi bi-robot"></i> AI Ready
                            </span>
                            <?php if ($book['has_vector']): ?>
                                <span class="badge bg-primary">
                                    <i class="bi bi-check-circle"></i> Siap
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning">
                                    <i class="bi bi-clock"></i> Proses
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if ($book['total_pages']): ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">
                                    <i class="bi bi-file-text"></i> Halaman
                                </span>
                                <span class="badge bg-info"><?= $book['total_pages'] ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Back Button -->
                    <div class="d-grid gap-2 mt-3">
                        <a href="/dashboard" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Katalog
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Chat Interface -->
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-robot"></i> Tanya AI tentang Buku Ini
                    </h5>
                    <small>Ajukan pertanyaan tentang isi buku dan dapatkan jawaban dari AI</small>
                </div>

                <div class="card-body d-flex flex-column" style="height: 600px;">
                    <!-- Chat Messages -->
                    <div id="chatMessages" class="flex-grow-1 overflow-auto mb-3 p-3 bg-light rounded">
                        <!-- Welcome Message -->
                        <div class="chat-message ai-message mb-3">
                            <div class="d-flex align-items-start">
                                <div class="avatar bg-primary text-white rounded-circle p-2 me-2">
                                    <i class="bi bi-robot"></i>
                                </div>
                                <div class="message-content flex-grow-1">
                                    <div class="bg-white rounded p-3 shadow-sm">
                                        <?php if ($book['has_vector']): ?>
                                            <p class="mb-2">👋 Halo! Saya siap membantu Anda memahami buku <strong><?= esc($book['title']) ?></strong>.</p>
                                            <p class="mb-0 small text-muted">Silakan ajukan pertanyaan tentang isi buku ini.</p>
                                        <?php else: ?>
                                            <p class="mb-2">⚠️ Maaf, buku ini belum dapat digunakan untuk fitur AI.</p>
                                            <p class="mb-0 small text-muted">
                                                <?php if ($book['pdf_file']): ?>
                                                    PDF sedang diproses. Silakan coba beberapa saat lagi.
                                                <?php else: ?>
                                                    Admin perlu mengupload file PDF terlebih dahulu.
                                                <?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Suggested Questions -->
                        <?php if ($book['has_vector']): ?>
                            <div id="suggestedQuestions" class="mb-3">
                                <p class="small text-muted mb-2">
                                    <i class="bi bi-lightbulb"></i> Saran pertanyaan:
                                </p>
                                <div class="d-flex flex-wrap gap-2">
                                    <button class="btn btn-sm btn-outline-secondary suggestion-btn">
                                        Apa tema utama buku ini?
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary suggestion-btn">
                                        Siapa tokoh utama dalam buku ini?
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary suggestion-btn">
                                        Bisakah kamu meringkas isi buku ini?
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Chat Input -->
                    <div class="chat-input">
                        <form id="chatForm" <?= !$book['has_vector'] ? 'style="display:none;"' : '' ?>>
                            <div class="input-group">
                                <input type="text" 
                                       id="questionInput" 
                                       class="form-control" 
                                       placeholder="Ketik pertanyaan Anda di sini..." 
                                       autocomplete="off"
                                       <?= !$book['has_vector'] ? 'disabled' : '' ?>>
                                <button type="submit" class="btn btn-primary" id="sendBtn">
                                    <i class="bi bi-send"></i> Kirim
                                </button>
                            </div>
                        </form>

                        <?php if (!$book['has_vector']): ?>
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i>
                                Fitur AI belum tersedia untuk buku ini.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
#chatMessages {
    max-height: 500px;
}

.chat-message {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.avatar {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-message .avatar {
    background-color: #28a745 !important;
}

.typing-indicator {
    display: inline-block;
}

.typing-indicator span {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #999;
    margin: 0 2px;
    animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-10px); }
}

.suggestion-btn:hover {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const bookId = <?= $book['id'] ?>;
const chatMessages = document.getElementById('chatMessages');
const chatForm = document.getElementById('chatForm');
const questionInput = document.getElementById('questionInput');
const sendBtn = document.getElementById('sendBtn');

// Handle form submission
if (chatForm) {
    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const question = questionInput.value.trim();
        if (!question) return;
        
        // Add user message
        addMessage(question, 'user');
        questionInput.value = '';
        
        // Show typing indicator
        showTypingIndicator();
        
        // Send to API
        try {
            const response = await fetch('/ai/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: new URLSearchParams({
                    book_id: bookId,
                    question: question
                })
            });
            
            const data = await response.json();
            
            // Remove typing indicator
            removeTypingIndicator();
            
            if (data.success) {
                addMessage(data.answer, 'ai');
            } else {
                addMessage(data.answer || 'Maaf, terjadi kesalahan. Silakan coba lagi.', 'ai', true);
            }
        } catch (error) {
            removeTypingIndicator();
            addMessage('Terjadi kesalahan koneksi. Silakan coba lagi.', 'ai', true);
            console.error('Error:', error);
        }
    });
}

// Handle suggested questions
document.querySelectorAll('.suggestion-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        questionInput.value = this.textContent.trim();
        questionInput.focus();
        document.getElementById('suggestedQuestions').style.display = 'none';
    });
});

// Add message to chat
function addMessage(text, type, isError = false) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-message ${type}-message mb-3`;
    
    const avatar = type === 'user' 
        ? '<i class="bi bi-person"></i>'
        : '<i class="bi bi-robot"></i>';
    
    const bgClass = type === 'user' ? 'bg-primary text-white' : 'bg-white';
    const avatarBg = type === 'user' ? 'bg-success' : 'bg-primary';
    
    messageDiv.innerHTML = `
        <div class="d-flex align-items-start ${type === 'user' ? 'flex-row-reverse' : ''}">
            <div class="avatar ${avatarBg} text-white rounded-circle p-2 ${type === 'user' ? 'ms-2' : 'me-2'}">
                ${avatar}
            </div>
            <div class="message-content flex-grow-1">
                <div class="${bgClass} rounded p-3 shadow-sm ${isError ? 'border border-danger' : ''}">
                    ${text.replace(/\n/g, '<br>')}
                </div>
                <small class="text-muted">${new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})}</small>
            </div>
        </div>
    `;
    
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Show typing indicator
function showTypingIndicator() {
    const typingDiv = document.createElement('div');
    typingDiv.id = 'typingIndicator';
    typingDiv.className = 'chat-message ai-message mb-3';
    typingDiv.innerHTML = `
        <div class="d-flex align-items-start">
            <div class="avatar bg-primary text-white rounded-circle p-2 me-2">
                <i class="bi bi-robot"></i>
            </div>
            <div class="message-content">
                <div class="bg-white rounded p-3 shadow-sm">
                    <div class="typing-indicator">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    chatMessages.appendChild(typingDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    sendBtn.disabled = true;
}

// Remove typing indicator
function removeTypingIndicator() {
    const indicator = document.getElementById('typingIndicator');
    if (indicator) {
        indicator.remove();
    }
    sendBtn.disabled = false;
}
</script>
<?= $this->endSection() ?>
