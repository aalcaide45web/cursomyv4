import { VideoPlayer } from './video-player.js';
import { NotesManager } from './notes.js';
import { CommentsManager } from './comments.js';
import { ProgressManager } from './progress.js';
import { TabManager } from './tabs.js';

class PlayerApp {
    constructor() {
        this.lessonId = null;
        this.videoPlayer = null;
        this.notesManager = null;
        this.commentsManager = null;
        this.progressManager = null;
        this.tabManager = null;
        
        this.init();
    }
    
    async init() {
        try {
            // Obtener ID de la lección desde la URL
            const urlParams = new URLSearchParams(window.location.search);
            this.lessonId = urlParams.get('id');
            
            if (!this.lessonId) {
                this.showError('ID de lección no especificado');
                return;
            }
            
            // Cargar información de la lección
            const lessonInfo = await this.loadLessonInfo();
            if (!lessonInfo) return;
            
            // Inicializar componentes
            this.initializeComponents(lessonInfo);
            
            // Configurar eventos globales
            this.setupGlobalEvents();
            
            console.log('🎬 PlayerApp inicializado correctamente');
            
        } catch (error) {
            console.error('❌ Error inicializando PlayerApp:', error);
            this.showError('Error inicializando la aplicación');
        }
    }
    
    async loadLessonInfo() {
        try {
            const response = await fetch(`/api/lesson/info?id=${this.lessonId}`);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('📚 Información de lección cargada:', data);
            return data;
            
        } catch (error) {
            console.error('❌ Error cargando información de lección:', error);
            this.showError('Error cargando información de la lección');
            return null;
        }
    }
    
    initializeComponents(lessonInfo) {
        // Inicializar reproductor de video
        this.videoPlayer = new VideoPlayer(this.lessonId, lessonInfo);
        
        // Inicializar gestor de notas
        this.notesManager = new NotesManager(this.lessonId);
        
        // Inicializar gestor de comentarios
        this.commentsManager = new CommentsManager(this.lessonId);
        
        // Inicializar gestor de progreso
        this.progressManager = new ProgressManager(this.lessonId);
        
        // Inicializar gestor de tabs
        this.tabManager = new TabManager();
        
        // Actualizar información en la UI
        this.updateLessonInfo(lessonInfo);
    }
    
    updateLessonInfo(lessonInfo) {
        const { lesson, course, section, attachments } = lessonInfo;
        
        // Actualizar título y información del curso
        const lessonTitle = document.getElementById('lesson-title');
        const courseInfo = document.getElementById('course-info');
        const lessonDescription = document.getElementById('lesson-description');
        
        if (lessonTitle) lessonTitle.textContent = lesson.name;
        if (courseInfo) courseInfo.textContent = `${course.name} - ${section.name}`;
        if (lessonDescription) lessonDescription.textContent = lesson.name;
        
        // Configurar video player
        if (this.videoPlayer) {
            this.videoPlayer.setVideoSource(lesson.file_path);
        }
        
        // Cargar recursos
        this.loadResources(attachments);
        
        // Cargar notas y comentarios existentes
        this.notesManager.loadNotes();
        this.commentsManager.loadComments();
    }
    
    loadResources(attachments) {
        const resourcesList = document.getElementById('resources-list');
        if (!resourcesList) return;
        
        if (attachments.length === 0) {
            resourcesList.innerHTML = '<p class="text-slate-400 text-center">No hay recursos disponibles</p>';
            return;
        }
        
        const resourcesHTML = attachments.map(attachment => `
            <div class="bg-slate-700 rounded-lg p-3 hover:bg-slate-600 transition-colors">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-slate-600 rounded flex items-center justify-center">
                            ${this.getFileIcon(attachment.file_type)}
                        </div>
                        <div>
                            <p class="text-slate-200 font-medium">${attachment.filename}</p>
                            <p class="text-xs text-slate-400">${this.formatFileSize(attachment.file_size)}</p>
                        </div>
                    </div>
                    <a href="/video/${encodeURIComponent(attachment.file_path.replace(/\\/g, '/'))}" 
                       download="${attachment.filename}"
                       class="btn-outline text-xs px-3 py-1">
                        Descargar
                    </a>
                </div>
            </div>
        `).join('');
        
        resourcesList.innerHTML = resourcesHTML;
    }
    
    getFileIcon(fileType) {
        const icons = {
            'pdf': '📄',
            'doc': '📝',
            'docx': '📝',
            'txt': '📄',
            'zip': '📦',
            'rar': '📦',
            'mp4': '🎬',
            'avi': '🎬',
            'mov': '🎬',
            'jpg': '🖼️',
            'jpeg': '🖼️',
            'png': '🖼️',
            'gif': '🖼️'
        };
        
        return icons[fileType.toLowerCase()] || '📎';
    }
    
    formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }
    
    setupGlobalEvents() {
        // Evento de cambio de velocidad
        const speedSelect = document.getElementById('playback-speed');
        if (speedSelect) {
            speedSelect.addEventListener('change', (e) => {
                if (this.videoPlayer) {
                    this.videoPlayer.setPlaybackSpeed(parseFloat(e.target.value));
                }
            });
        }
        
        // Evento de pantalla completa
        const fullscreenBtn = document.getElementById('fullscreen-btn');
        if (fullscreenBtn) {
            fullscreenBtn.addEventListener('click', () => {
                if (this.videoPlayer) {
                    this.videoPlayer.toggleFullscreen();
                }
            });
        }
        
        // Guardar progreso antes de cerrar la página
        window.addEventListener('beforeunload', () => {
            if (this.progressManager && this.videoPlayer) {
                const currentTime = this.videoPlayer.getCurrentTime();
                this.progressManager.saveProgress(currentTime);
            }
        });
    }
    
    showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'fixed top-4 right-4 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        errorDiv.textContent = message;
        document.body.appendChild(errorDiv);
        
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }
}

// Inicializar la aplicación cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    new PlayerApp();
});
