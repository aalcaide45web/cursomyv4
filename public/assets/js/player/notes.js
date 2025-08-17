// Sistema de notas del Player (FASE 5)
console.log('📝 Sistema de notas inicializado');

export class NotesManager {
    constructor(lessonId) {
        this.lessonId = lessonId;
        this.notes = [];
        this.filteredNotes = [];
        this.videoPlayer = null;
        
        // Elementos del DOM
        this.notesList = document.getElementById('notes-list');
        this.noteTimestampInput = document.getElementById('note-timestamp');
        this.noteTextInput = document.getElementById('note-text');
        this.addNoteBtn = document.getElementById('add-note-btn');
        this.noteSearchInput = document.getElementById('note-search');
        
        this.init();
    }
    
    init() {
        if (!this.notesList || !this.addNoteBtn) {
            console.error('❌ Elementos de notas no encontrados');
            return;
        }
        
        this.setupEvents();
        console.log('📝 NotesManager inicializado');
    }
    
    setupEvents() {
        // Botón de agregar nota
        if (this.addNoteBtn) {
            this.addNoteBtn.addEventListener('click', () => {
                this.addNote();
            });
        }
        
        // Enter en el campo de texto
        if (this.noteTextInput) {
            this.noteTextInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.addNote();
                }
            });
        }
        
        // Enter en el campo de timestamp
        if (this.noteTimestampInput) {
            this.noteTimestampInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.noteTextInput.focus();
                }
            });
        }
        
        // Buscador de notas
        if (this.noteSearchInput) {
            this.noteSearchInput.addEventListener('input', (e) => {
                this.filterNotes(e.target.value);
            });
        }
    }
    
    setVideoPlayer(videoPlayer) {
        this.videoPlayer = videoPlayer;
    }
    
    async loadNotes() {
        try {
            const response = await fetch(`/api/lesson/notes?lesson_id=${this.lessonId}`);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            this.notes = data.notes || [];
            this.filteredNotes = [...this.notes];
            this.renderNotes();
            
            console.log('📝 Notas cargadas:', this.notes.length);
            
        } catch (error) {
            console.error('❌ Error cargando notas:', error);
            this.showError('Error cargando las notas');
        }
    }
    
    async addNote() {
        const timestamp = this.noteTimestampInput.value.trim();
        const text = this.noteTextInput.value.trim();
        
        if (!timestamp || !text) {
            this.showError('Por favor completa todos los campos');
            return;
        }
        
        // Convertir timestamp a segundos
        const seconds = this.parseTimestamp(timestamp);
        if (seconds === null) {
            this.showError('Formato de timestamp inválido. Usa formato MM:SS o HH:MM:SS');
            return;
        }
        
        try {
            const response = await fetch('/api/lesson/notes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    lesson_id: this.lessonId,
                    timestamp: seconds,
                    text: text
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            if (data.success) {
                // Agregar la nueva nota a la lista
                this.notes.push(data.note);
                this.renderNotes();
                
                // Limpiar campos
                this.noteTimestampInput.value = '';
                this.noteTextInput.value = '';
                this.noteTextInput.focus();
                
                this.showSuccess('Nota agregada correctamente');
            }
            
        } catch (error) {
            console.error('❌ Error agregando nota:', error);
            this.showError('Error agregando la nota');
        }
    }
    
    async deleteNote(noteId) {
        if (!confirm('¿Estás seguro de que quieres eliminar esta nota?')) {
            return;
        }
        
        try {
            const response = await fetch(`/api/lesson/notes`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    note_id: noteId
                })
            });
            
            if (response.ok) {
                // Remover nota de la lista
                this.notes = this.notes.filter(note => note.id !== noteId);
                this.filteredNotes = this.filteredNotes.filter(note => note.id !== noteId);
                this.renderNotes();
                this.showSuccess('Nota eliminada correctamente');
            }
            
        } catch (error) {
            console.error('❌ Error eliminando nota:', error);
            this.showError('Error eliminando la nota');
        }
    }
    
    async editNote(noteId, newText) {
        try {
            const response = await fetch(`/api/lesson/notes`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    note_id: noteId,
                    text: newText
                })
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    // Actualizar nota en la lista
                    const noteIndex = this.notes.findIndex(note => note.id === noteId);
                    if (noteIndex !== -1) {
                        this.notes[noteIndex] = data.note;
                        this.filteredNotes = [...this.notes];
                        this.renderNotes();
                        this.showSuccess('Nota actualizada correctamente');
                    }
                }
            }
            
        } catch (error) {
            console.error('❌ Error editando nota:', error);
            this.showError('Error editando la nota');
        }
    }
    
    filterNotes(searchTerm) {
        if (!searchTerm.trim()) {
            this.filteredNotes = [...this.notes];
        } else {
            const term = searchTerm.toLowerCase();
            this.filteredNotes = this.notes.filter(note => 
                note.text.toLowerCase().includes(term) ||
                this.formatTimestamp(note.t_seconds).includes(term)
            );
        }
        this.renderNotes();
    }
    
    startEdit(noteId) {
        const noteElement = document.querySelector(`[data-note-id="${noteId}"]`);
        if (noteElement) {
            const contentDiv = noteElement.querySelector('.note-content');
            const editDiv = noteElement.querySelector('.note-edit');
            
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
    
    cancelEdit(noteId) {
        const noteElement = document.querySelector(`[data-note-id="${noteId}"]`);
        if (noteElement) {
            const contentDiv = noteElement.querySelector('.note-content');
            const editDiv = noteElement.querySelector('.note-edit');
            
            if (contentDiv && editDiv) {
                contentDiv.classList.remove('hidden');
                editDiv.classList.add('hidden');
                
                // Restaurar el texto original
                const textarea = editDiv.querySelector('textarea');
                const note = this.notes.find(n => n.id === noteId);
                if (textarea && note) {
                    textarea.value = note.text;
                }
            }
        }
    }
    
    async saveEdit(noteId) {
        const noteElement = document.querySelector(`[data-note-id="${noteId}"]`);
        if (noteElement) {
            const editDiv = noteElement.querySelector('.note-edit');
            const textarea = editDiv?.querySelector('textarea');
            
            if (textarea) {
                const newText = textarea.value.trim();
                if (newText) {
                    await this.editNote(noteId, newText);
                } else {
                    this.showError('La nota no puede estar vacía');
                }
            }
        }
    }
    
    renderNotes() {
        if (!this.notesList) return;
        
        if (this.filteredNotes.length === 0) {
            if (this.notes.length === 0) {
                this.notesList.innerHTML = '<p class="text-slate-400 text-center">No hay notas aún</p>';
            } else {
                this.notesList.innerHTML = '<p class="text-slate-400 text-center">No se encontraron notas con esa búsqueda</p>';
            }
            return;
        }
        
        // Ordenar notas por timestamp
        const sortedNotes = [...this.filteredNotes].sort((a, b) => a.t_seconds - b.t_seconds);
        
        const notesHTML = sortedNotes.map(note => `
            <div class="bg-slate-700 rounded-lg p-3 hover:bg-slate-600 transition-colors" data-note-id="${note.id}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <button 
                                class="text-blue-400 hover:text-blue-300 text-sm font-medium"
                                onclick="window.notesManager?.seekToTimestamp(${note.t_seconds})"
                            >
                                ${this.formatTimestamp(note.t_seconds)}
                            </button>
                            <span class="text-xs text-slate-400">•</span>
                            <span class="text-xs text-slate-400">
                                ${this.formatDate(note.created_at)}
                            </span>
                        </div>
                        <div class="note-content">
                            <p class="text-slate-200 text-sm">${this.escapeHtml(note.text)}</p>
                        </div>
                        <div class="note-edit hidden mt-2">
                            <textarea 
                                class="w-full px-2 py-1 bg-slate-600 border border-slate-500 rounded text-slate-200 text-sm resize-none"
                                rows="2"
                            >${this.escapeHtml(note.text)}</textarea>
                            <div class="flex space-x-2 mt-2">
                                <button 
                                    onclick="window.notesManager?.saveEdit(${note.id})"
                                    class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs rounded transition-colors"
                                >
                                    Guardar
                                </button>
                                <button 
                                    onclick="window.notesManager?.cancelEdit(${note.id})"
                                    class="px-3 py-1 bg-slate-600 hover:bg-slate-700 text-white text-xs rounded transition-colors"
                                >
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="flex space-x-1 ml-2">
                        <button 
                            onclick="window.notesManager?.startEdit(${note.id})"
                            class="text-blue-400 hover:text-blue-300 p-1"
                            title="Editar nota"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button 
                            onclick="window.notesManager?.deleteNote(${note.id})"
                            class="text-red-400 hover:text-red-300 p-1"
                            title="Eliminar nota"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
        
        this.notesList.innerHTML = notesHTML;
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
    
    parseTimestamp(timestamp) {
        // Formato: MM:SS o HH:MM:SS
        const parts = timestamp.split(':').map(Number);
        
        if (parts.length === 2) {
            // MM:SS
            const minutes = parts[0];
            const seconds = parts[1];
            return minutes * 60 + seconds;
        } else if (parts.length === 3) {
            // HH:MM:SS
            const hours = parts[0];
            const minutes = parts[1];
            const seconds = parts[2];
            return hours * 3600 + minutes * 60 + seconds;
        }
        
        return null;
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
        return date.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
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
window.notesManager = null;
