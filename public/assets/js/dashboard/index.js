// Dashboard module
class Dashboard {
    constructor() {
        this.stats = {};
        this.courses = [];
        this.isScanning = false;
        this.init();
    }
    
    init() {
        this.loadStats();
        this.loadCourses();
        this.bindEvents();
    }
    
    bindEvents() {
        // Botones de escaneo
        const incrementalBtn = document.getElementById('scan-incremental');
        const rebuildBtn = document.getElementById('scan-rebuild');
        
        if (incrementalBtn) {
            incrementalBtn.addEventListener('click', () => this.scanIncremental());
        }
        
        if (rebuildBtn) {
            rebuildBtn.addEventListener('click', () => this.scanRebuild());
        }
        
        // Búsqueda global
        const searchInput = document.getElementById('global-search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
        }
    }
    
    async loadStats() {
        try {
            const response = await fetch('/api/dashboard/stats');
            const data = await response.json();
            
            if (data.success) {
                this.stats = data.data; // Cambiado de data.stats a data.data
                this.updateStatsDisplay();
            }
        } catch (error) {
            console.error('Error cargando estadísticas:', error);
        }
    }
    
    async loadCourses() {
        try {
            const response = await fetch('/api/dashboard/courses');
            const data = await response.json();
            
            if (data.success) {
                this.courses = data.data.courses; // Cambiado de data.courses a data.data.courses
                this.updateCoursesDisplay();
            }
        } catch (error) {
            console.error('Error cargando cursos:', error);
        }
    }
    
    updateStatsDisplay() {
        // Actualizar cada contador individualmente usando los IDs del HTML
        const totalCourses = document.getElementById('total-courses');
        const totalLessons = document.getElementById('total-lessons');
        const totalTopics = document.getElementById('total-topics');
        const totalInstructors = document.getElementById('total-instructors');
        
        if (totalCourses) totalCourses.textContent = this.stats.total_courses || 0;
        if (totalLessons) totalLessons.textContent = this.stats.total_lessons || 0;
        if (totalTopics) totalTopics.textContent = this.stats.total_topics || 0;
        if (totalInstructors) totalInstructors.textContent = this.stats.total_instructors || 0;
        
        // También actualizar el contador de cursos encontrados
        const coursesCount = document.getElementById('courses-count');
        if (coursesCount) {
            coursesCount.textContent = `${this.stats.total_courses || 0} cursos encontrados`;
        }
    }
    
    updateCoursesDisplay() {
        const coursesContainer = document.getElementById('courses-grid');
        if (!coursesContainer) return;
        
        if (this.courses.length === 0) {
            coursesContainer.innerHTML = `
                <div class="col-span-full text-center py-12">
                    <div class="text-gray-500 mb-4">
                        <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay cursos disponibles</h3>
                    <p class="text-gray-500 mb-4">Usa los botones de escaneo para importar videos desde /uploads</p>
                    <div class="flex justify-center space-x-4">
                        <button id="scan-incremental" class="btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Escaneo Incremental
                        </button>
                        <button id="scan-rebuild" class="btn-secondary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Rebuild Completo
                        </button>
                    </div>
                </div>
            `;
            
            // Re-bind events después de actualizar el DOM
            this.bindEvents();
            return;
        }
        
        coursesContainer.innerHTML = this.courses.map(course => this.createCourseCard(course)).join('');
    }
    
    createCourseCard(course) {
        return `
            <div class="glass p-6 rounded-xl">
                <div class="aspect-video bg-gray-200 rounded-lg mb-4 flex items-center justify-center">
                    ${course.cover_path ? 
                        `<img src="${course.cover_path}" alt="${course.name}" class="w-full h-full object-cover rounded-lg">` :
                        `<svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>`
                    }
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">${course.name}</h3>
                <div class="text-sm text-gray-600 mb-3">
                    <div>Instructor: ${course.instructor_name}</div>
                    <div>Tema: ${course.topic_name}</div>
                    <div class="flex items-center mt-2">
                        <div class="flex text-yellow-400">
                            ${this.generateStars(course.avg_rating)}
                        </div>
                        <span class="ml-2 text-gray-500">(${course.ratings_count})</span>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button class="btn-primary flex-1">Ver</button>
                    <button class="btn-secondary flex-1">Continuar</button>
                    <button class="btn-outline">⋮</button>
                </div>
            </div>
        `;
    }
    
    generateStars(rating) {
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 >= 0.5;
        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
        
        let stars = '';
        for (let i = 0; i < fullStars; i++) {
            stars += '<svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
        }
        if (hasHalfStar) {
            stars += '<svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
        }
        for (let i = 0; i < emptyStars; i++) {
            stars += '<svg class="w-4 h-4 fill-current text-gray-300" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
        }
        return stars;
    }
    
    async scanIncremental() {
        if (this.isScanning) return;
        
        this.isScanning = true;
        this.updateScanButton('scan-incremental', 'Escaneando...', true);
        
        try {
            const response = await fetch('/api/scan/incremental', { method: 'POST' });
            const data = await response.json();
            
            if (data.success) {
                this.showScanResult('Escaneo incremental completado', data);
                // Recargar datos
                await this.loadStats();
                await this.loadCourses();
            } else {
                this.showScanError('Error durante el escaneo incremental');
            }
        } catch (error) {
            console.error('Error en escaneo incremental:', error);
            this.showScanError('Error de conexión durante el escaneo');
        } finally {
            this.isScanning = false;
            this.updateScanButton('scan-incremental', 'Escaneo Incremental', false);
        }
    }
    
    async scanRebuild() {
        if (this.isScanning) return;
        
        if (!confirm('¿Estás seguro de que quieres hacer un rebuild completo? Esto procesará todos los archivos.')) {
            return;
        }
        
        this.isScanning = true;
        this.updateScanButton('scan-rebuild', 'Rebuild...', true);
        
        try {
            const response = await fetch('/api/scan/rebuild', { method: 'POST' });
            const data = await response.json();
            
            if (data.success) {
                this.showScanResult('Rebuild completado', data);
                // Recargar datos
                await this.loadStats();
                await this.loadCourses();
            } else {
                this.showScanError('Error durante el rebuild');
            }
        } catch (error) {
            console.error('Error en rebuild:', error);
            this.showScanError('Error de conexión durante el rebuild');
        } finally {
            this.isScanning = false;
            this.updateScanButton('scan-rebuild', 'Rebuild Completo', false);
        }
    }
    
    updateScanButton(buttonId, text, disabled) {
        const button = document.getElementById(buttonId);
        if (button) {
            button.textContent = text;
            button.disabled = disabled;
            if (disabled) {
                button.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                button.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
    }
    
    showScanResult(message, data) {
        // Crear notificación de éxito
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        notification.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                ${message}
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
        
        // Mostrar detalles en consola
        console.log('Resultado del escaneo:', data);
    }
    
    showScanError(message) {
        // Crear notificación de error
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        notification.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                ${message}
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }
    
    handleSearch(query) {
        // Implementar búsqueda global
        console.log('Búsqueda:', query);
        // TODO: Implementar búsqueda en tiempo real
    }
}

// Inicializar dashboard cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    new Dashboard();
});
