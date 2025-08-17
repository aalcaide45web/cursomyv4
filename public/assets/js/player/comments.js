// Sistema de comentarios del Player (FASE 5)
console.log('💬 Sistema de comentarios inicializado');

export class CommentsManager {
    constructor(lessonId) {
        this.lessonId = lessonId;
        this.comments = [];
        this.videoPlayer = null;
        
        // Elementos del DOM
        this.commentsList = document.getElementById('comments-list');
        this.commentTextInput = document.getElementById('comment-text');
        this.addCommentBtn = document.getElementById('add-comment-btn');
        
        this.init();
    }
    
    init() {
        if (!this.commentsList || !this.addCommentBtn) {
            console.error('❌ Elementos de comentarios no encontrados');
            return;
        }
        
        this.setupEvents();
        console.log('💬 CommentsManager inicializado');
    }
    
    setupEvents() {
        // Botón de agregar comentario
        if (this.addCommentBtn) {
            this.addCommentBtn.addEventListener('click', () => {
                this.addComment();
            });
        }
        
        // Enter en el campo de texto
        if (this.commentTextInput) {
            this.commentTextInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.addComment();
                }
            });
        }
    }
    
    setVideoPlayer(videoPlayer) {
        this.videoPlayer = videoPlayer;
    }
    
    async loadComments() {
        try {
            const response = await fetch(`/api/lesson/comments?lesson_id=${this.lessonId}`);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            this.comments = data.comments || [];
            this.renderComments();
            
            console.log('💬 Comentarios cargados:', this.comments.length);
            
        } catch (error) {
            console.error('❌ Error cargando comentarios:', error);
            this.showError('Error cargando los comentarios');
        }
    }
    
    async addComment() {
        const text = this.commentTextInput.value.trim();
        
        if (!text) {
            this.showError('Por favor escribe un comentario');
            return;
        }
        
        // Obtener timestamp actual del video si está disponible
        let timestamp = null;
        if (this.videoPlayer) {
            timestamp = this.videoPlayer.getCurrentTime();
        } else {
            const video = document.getElementById('video-player');
            if (video) {
                timestamp = video.currentTime;
            }
        }
        
        try {
            const response = await fetch('/api/lesson/comments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    lesson_id: this.lessonId,
                    text: text,
                    timestamp: timestamp
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            if (data.success) {
                // Agregar el nuevo comentario a la lista
                this.comments.unshift(data.comment); // Agregar al inicio
                this.renderComments();
                
                // Limpiar campo
                this.commentTextInput.value = '';
                this.commentTextInput.focus();
                
                this.showSuccess('Comentario agregado correctamente');
            }
            
        } catch (error) {
            console.error('❌ Error agregando comentario:', error);
            this.showError('Error agregando el comentario');
        }
    }
    
    async deleteComment(commentId) {
        if (!confirm('¿Estás seguro de que quieres eliminar este comentario?')) {
            return;
        }
        
        try {
            const response = await fetch(`/api/lesson/comments`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    comment_id: commentId
                })
            });
            
            if (response.ok) {
                // Remover comentario de la lista
                this.comments = this.comments.filter(comment => comment.id !== commentId);
                this.renderComments();
                this.showSuccess('Comentario eliminado correctamente');
            }
            
        } catch (error) {
            console.error('❌ Error eliminando comentario:', error);
            this.showError('Error eliminando el comentario');
        }
    }
    
    async editComment(commentId, newText) {
        try {
            const response = await fetch(`/api/lesson/comments`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    comment_id: commentId,
                    text: newText
                })
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    // Actualizar comentario en la lista
                    const commentIndex = this.comments.findIndex(comment => comment.id === commentId);
                    if (commentIndex !== -1) {
                        this.comments[commentIndex] = data.comment;
                        this.renderComments();
                        this.showSuccess('Comentario actualizado correctamente');
                    }
                }
            }
            
        } catch (error) {
            console.error('❌ Error editando comentario:', error);
            this.showError('Error editando el comentario');
        }
    }
    
    startEdit(commentId) {
        const commentElement = document.querySelector(`[data-comment-id="${commentId}"]`);
        if (commentElement) {
            const contentDiv = commentElement.querySelector('.comment-content');
            const editDiv = commentElement.querySelector('.comment-edit');
            
            if (contentDiv && editDiv) {
                contentDiv.classList.add('hidden');
                editDiv.classList.remove('hidden');
                
                // Enfocar el textarea
                const textarea = editDiv.querySelector('textarea');
                if (textarea) {
                    textarea.focus();
                    textarea.select();
                }
            }
        }
    }
    
    cancelEdit(commentId) {
        const commentElement = document.querySelector(`[data-comment-id="${commentId}"]`);
        if (commentElement) {
            const contentDiv = commentElement.querySelector('.comment-content');
            const editDiv = commentElement.querySelector('.comment-edit');
            
            if (contentDiv && editDiv) {
                contentDiv.classList.remove('hidden');
                editDiv.classList.add('hidden');
                
                // Restaurar el texto original
                const textarea = editDiv.querySelector('textarea');
                const comment = this.comments.find(c => c.id === commentId);
                if (textarea && comment) {
                    textarea.value = comment.text;
                }
            }
        }
    }
    
    async saveEdit(commentId) {
        const commentElement = document.querySelector(`[data-comment-id="${commentId}"]`);
        if (commentElement) {
            const editDiv = commentElement.querySelector('.comment-edit');
            const textarea = editDiv?.querySelector('textarea');
            
            if (textarea) {
                const newText = textarea.value.trim();
                if (newText) {
                    await this.editComment(commentId, newText);
                } else {
                    this.showError('El comentario no puede estar vacío');
                }
            }
        }
    }
    
    renderComments() {
        if (!this.commentsList) return;
        
        if (this.comments.length === 0) {
            this.commentsList.innerHTML = '<p class="text-slate-400 text-center">No hay comentarios aún</p>';
            return;
        }
        
        const commentsHTML = this.comments.map(comment => `
            <div class="bg-slate-700 rounded-lg p-3 hover:bg-slate-600 transition-colors" data-comment-id="${comment.id}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            ${comment.t_seconds ? `
                                <button 
                                    class="text-blue-400 hover:text-blue-300 text-sm font-medium"
                                    onclick="window.commentsManager?.seekToTimestamp(${comment.t_seconds})"
                                >
                                    ${this.formatTimestamp(comment.t_seconds)}
                                </button>
                                <span class="text-xs text-slate-400">•</span>
                            ` : ''}
                            <span class="text-xs text-slate-400">
                                ${this.formatDate(comment.created_at)}
                            </span>
                        </div>
                        <div class="comment-content">
                            <p class="text-slate-200 text-sm">${this.escapeHtml(comment.text)}</p>
                        </div>
                        <div class="comment-edit hidden mt-2">
                            <textarea 
                                class="w-full px-2 py-1 bg-slate-600 border border-slate-500 rounded text-slate-200 text-sm resize-none"
                                rows="2"
                            >${this.escapeHtml(comment.text)}</textarea>
                            <div class="flex space-x-2 mt-2">
                                <button 
                                    onclick="window.commentsManager?.saveEdit(${comment.id})"
                                    class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs rounded transition-colors"
                                >
                                    Guardar
                                </button>
                                <button 
                                    onclick="window.commentsManager?.cancelEdit(${comment.id})"
                                    class="px-3 py-1 bg-slate-600 hover:bg-slate-700 text-white text-xs rounded transition-colors"
                                >
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="flex space-x-1 ml-2">
                        <button 
                            onclick="window.commentsManager?.startEdit(${comment.id})"
                            class="text-blue-400 hover:text-blue-300 p-1"
                            title="Editar comentario"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button 
                            onclick="window.commentsManager?.deleteComment(${comment.id})"
                            class="text-red-400 hover:text-red-300 p-1"
                            title="Eliminar comentario"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
        
        this.commentsList.innerHTML = commentsHTML;
    }
    
    seekToTimestamp(seconds) {
        if (this.videoPlayer) {
            this.videoPlayer.seekToTime(seconds);
        } else {
            // Fallback: buscar el video directamente
            const video = document.getElementById('video-player');
            if (video) {
                video.currentTime = seconds;
                video.play();
            }
        }
    }
    
    formatTimestamp(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.floor(seconds % 60);
        
        if (minutes >= 60) {
            const hours = Math.floor(minutes / 60);
            const remainingMinutes = minutes % 60;
            return `${hours}:${remainingMinutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
        }
        
        return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
    }
    
    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInHours = (now - date) / (1000 * 60 * 60);
        
        if (diffInHours < 1) {
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));
            if (diffInMinutes < 1) {
                return 'Ahora mismo';
            }
            return `Hace ${diffInMinutes} minuto${diffInMinutes > 1 ? 's' : ''}`;
        } else if (diffInHours < 24) {
            return `Hace ${Math.floor(diffInHours)} hora${Math.floor(diffInHours) > 1 ? 's' : ''}`;
        } else {
            return date.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    showError(message) {
        this.showNotification(message, 'error');
    }
    
    showSuccess(message) {
        this.showNotification(message, 'success');
    }
    
    showNotification(message, type = 'info') {
        const colors = {
            error: 'bg-red-600',
            success: 'bg-green-600',
            info: 'bg-blue-600'
        };
        
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

// Hacer accesible globalmente para los onclick
window.commentsManager = null;
