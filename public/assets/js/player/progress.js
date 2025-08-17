// Control de progreso del Player (FASE 5)
console.log('📊 Progreso del player inicializado');

export class ProgressManager {
    constructor(lessonId) {
        this.lessonId = lessonId;
        this.saveInterval = null;
        this.lastSavedPosition = 0;
        
        this.init();
    }
    
    init() {
        console.log('📊 ProgressManager inicializado');
        this.startAutoSave();
    }
    
    startAutoSave() {
        // Guardar progreso cada 5 segundos
        this.saveInterval = setInterval(() => {
            this.autoSaveProgress();
        }, 5000);
    }
    
    stopAutoSave() {
        if (this.saveInterval) {
            clearInterval(this.saveInterval);
            this.saveInterval = null;
        }
    }
    
    async autoSaveProgress() {
        const video = document.getElementById('video-player');
        if (!video || video.paused) return;
        
        const currentTime = video.currentTime;
        const duration = video.duration;
        
        // Solo guardar si ha pasado al menos 10 segundos desde el último guardado
        if (Math.abs(currentTime - this.lastSavedPosition) >= 10) {
            await this.saveProgress(currentTime);
            this.lastSavedPosition = currentTime;
        }
    }
    
    async saveProgress(position) {
        try {
            const response = await fetch('/api/lesson/progress', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    lesson_id: this.lessonId,
                    position: position
                })
            });
            
            if (response.ok) {
                console.log('📊 Progreso guardado:', this.formatTime(position));
            }
            
        } catch (error) {
            console.error('❌ Error guardando progreso:', error);
        }
    }
    
    async loadProgress() {
        try {
            const response = await fetch(`/api/lesson/progress?lesson_id=${this.lessonId}`);
            if (response.ok) {
                const data = await response.json();
                if (data.progress) {
                    return data.progress.last_t_seconds || 0;
                }
            }
        } catch (error) {
            console.error('❌ Error cargando progreso:', error);
        }
        return 0;
    }
    
    formatTime(seconds) {
        if (isNaN(seconds)) return '0:00';
        
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.floor(seconds % 60);
        
        return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
    }
    
    destroy() {
        this.stopAutoSave();
    }
}
