export class VideoPlayer {
    constructor(lessonId, lessonInfo) {
        this.lessonId = lessonId;
        this.lessonInfo = lessonInfo;
        this.video = document.getElementById('video-player');
        this.customControls = document.getElementById('custom-controls');
        this.progressBar = document.getElementById('progress-bar');
        this.progressSlider = document.getElementById('progress-slider');
        this.currentTimeSpan = document.getElementById('current-time');
        this.totalTimeSpan = document.getElementById('total-time');
        this.playPauseBtn = document.getElementById('play-pause-btn');
        this.rewind10Btn = document.getElementById('rewind-10');
        this.forward10Btn = document.getElementById('forward-10');
        
        this.isPlaying = false;
        this.progressUpdateInterval = null;
        
        this.init();
    }
    
    init() {
        if (!this.video) {
            console.error('❌ Elemento de video no encontrado');
            return;
        }
        
        // Configurar volumen máximo y desmutear
        this.video.volume = 1.0;
        this.video.muted = false;
        
        this.setupVideoEvents();
        this.setupCustomControls();
        this.setupKeyboardShortcuts();
        
        console.log('🎬 VideoPlayer inicializado');
    }
    
    setupVideoEvents() {
        // Evento de metadatos cargados
        this.video.addEventListener('loadedmetadata', () => {
            this.updateTotalTime();
            this.updateProgressBar();
        });
        
        // Evento de tiempo actualizado
        this.video.addEventListener('timeupdate', () => {
            this.updateCurrentTime();
            this.updateProgressBar();
        });
        
        // Evento de reproducción
        this.video.addEventListener('play', () => {
            this.isPlaying = true;
            this.updatePlayPauseButton();
            this.startProgressUpdate();
        });
        
        // Evento de pausa
        this.video.addEventListener('pause', () => {
            this.isPlaying = false;
            this.updatePlayPauseButton();
            this.stopProgressUpdate();
        });
        
        // Evento de finalización
        this.video.addEventListener('ended', () => {
            this.isPlaying = false;
            this.updatePlayPauseButton();
            this.stopProgressUpdate();
        });
        
        // Evento de clic en el video para mostrar/ocultar controles
        this.video.addEventListener('click', () => {
            this.toggleCustomControls();
        });
        
        // Evento de movimiento del mouse para mostrar controles
        this.video.addEventListener('mousemove', () => {
            this.showCustomControls();
            this.hideCustomControlsAfterDelay();
        });
    }
    
    setupCustomControls() {
        // Botón de play/pause
        if (this.playPauseBtn) {
            this.playPauseBtn.addEventListener('click', () => {
                this.togglePlayPause();
            });
        }
        
        // Botón de retroceder 10 segundos
        if (this.rewind10Btn) {
            this.rewind10Btn.addEventListener('click', () => {
                this.seek(-10);
            });
        }
        
        // Botón de avanzar 10 segundos
        if (this.forward10Btn) {
            this.forward10Btn.addEventListener('click', () => {
                this.seek(10);
            });
        }
        
        // Slider de progreso
        if (this.progressSlider) {
            this.progressSlider.addEventListener('input', (e) => {
                const percentage = e.target.value;
                this.seekToPercentage(percentage);
            });
        }
        
        // Ocultar controles cuando no hay movimiento del mouse
        this.hideCustomControlsAfterDelay();
    }
    
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                return; // No procesar en campos de texto
            }
            
            switch (e.code) {
                case 'Space':
                    e.preventDefault();
                    this.togglePlayPause();
                    break;
                case 'ArrowLeft':
                    e.preventDefault();
                    this.seek(-10);
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    this.seek(10);
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.changeVolume(0.1);
                    break;
                case 'ArrowDown':
                    e.preventDefault();
                    this.changeVolume(-0.1);
                    break;
                case 'KeyF':
                    e.preventDefault();
                    this.toggleFullscreen();
                    break;
                case 'KeyM':
                    e.preventDefault();
                    this.toggleMute();
                    break;
            }
        });
    }
    
    setVideoSource(filePath) {
        if (this.video) {
            console.log('🎬 Ruta original:', filePath);
            // Convertir backslashes a forward slashes y codificar correctamente
            const normalizedPath = filePath.replace(/\\/g, '/');
            console.log('🎬 Ruta normalizada:', normalizedPath);
            const encodedPath = encodeURIComponent(normalizedPath);
            console.log('🎬 Ruta codificada:', encodedPath);
            this.video.src = `/video/${encodedPath}`;
            console.log('🎬 URL final del video:', this.video.src);
            this.video.load();
            
            // Configurar volumen máximo después de cargar
            this.video.addEventListener('loadeddata', () => {
                this.video.volume = 1.0;
                this.video.muted = false;
                console.log('🎬 Video cargado, volumen configurado al máximo');
            }, { once: true });
        }
    }
    
    togglePlayPause() {
        if (this.video.paused) {
            this.video.play();
        } else {
            this.video.pause();
        }
    }
    
    seek(seconds) {
        const newTime = this.video.currentTime + seconds;
        this.video.currentTime = Math.max(0, Math.min(newTime, this.video.duration));
    }
    
    seekToPercentage(percentage) {
        const newTime = (percentage / 100) * this.video.duration;
        this.video.currentTime = newTime;
    }
    
    seekToTime(seconds) {
        this.video.currentTime = seconds;
    }
    
    setPlaybackSpeed(speed) {
        this.video.playbackRate = speed;
    }
    
    toggleFullscreen() {
        if (!document.fullscreenElement) {
            this.video.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    }
    
    toggleMute() {
        this.video.muted = !this.video.muted;
    }
    
    changeVolume(delta) {
        const newVolume = Math.max(0, Math.min(1, this.video.volume + delta));
        this.video.volume = newVolume;
    }
    
    getCurrentTime() {
        return this.video.currentTime;
    }
    
    getDuration() {
        return this.video.duration;
    }
    
    updateCurrentTime() {
        if (this.currentTimeSpan) {
            this.currentTimeSpan.textContent = this.formatTime(this.video.currentTime);
        }
    }
    
    updateTotalTime() {
        if (this.totalTimeSpan) {
            this.totalTimeSpan.textContent = this.formatTime(this.video.duration);
        }
    }
    
    updateProgressBar() {
        if (this.progressBar && this.progressSlider) {
            const percentage = (this.video.currentTime / this.video.duration) * 100;
            this.progressBar.style.width = `${percentage}%`;
            this.progressSlider.value = percentage;
        }
    }
    
    updatePlayPauseButton() {
        if (this.playPauseBtn) {
            const icon = this.playPauseBtn.querySelector('svg');
            if (icon) {
                if (this.isPlaying) {
                    icon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    `;
                } else {
                    icon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    `;
                }
            }
        }
    }
    
    startProgressUpdate() {
        this.stopProgressUpdate();
        this.progressUpdateInterval = setInterval(() => {
            this.updateProgressBar();
        }, 100);
    }
    
    stopProgressUpdate() {
        if (this.progressUpdateInterval) {
            clearInterval(this.progressUpdateInterval);
            this.progressUpdateInterval = null;
        }
    }
    
    showCustomControls() {
        if (this.customControls) {
            this.customControls.classList.remove('hidden');
        }
    }
    
    hideCustomControls() {
        if (this.customControls) {
            this.customControls.classList.add('hidden');
        }
    }
    
    toggleCustomControls() {
        if (this.customControls) {
            if (this.customControls.classList.contains('hidden')) {
                this.showCustomControls();
            } else {
                this.hideCustomControls();
            }
        }
    }
    
    hideCustomControlsAfterDelay() {
        clearTimeout(this.hideTimeout);
        this.hideTimeout = setTimeout(() => {
            this.hideCustomControls();
        }, 3000);
    }
    
    formatTime(seconds) {
        if (isNaN(seconds)) return '0:00';
        
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.floor(seconds % 60);
        
        return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
    }
}
