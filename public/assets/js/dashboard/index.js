// Módulo del Dashboard
console.log('📊 Dashboard inicializado');

// Estado del dashboard
let dashboardState = {
    courses: [],
    stats: {
        totalCourses: 0,
        totalLessons: 0,
        totalTopics: 0,
        totalInstructors: 0
    }
};

// Inicializar dashboard cuando se carga
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

function initializeDashboard() {
    console.log('🎯 Inicializando dashboard...');
    
    // Cargar estadísticas
    loadDashboardStats();
    
    // Cargar cursos
    loadCourses();
    
    // Configurar botones del estado vacío
    setupEmptyStateButtons();
}

async function loadDashboardStats() {
    try {
        console.log('📈 Cargando estadísticas...');
        
        const response = await fetch('/api/dashboard/stats');
        const data = await response.json();
        
        if (data.success) {
            const stats = data.data;
            dashboardState.stats = {
                totalCourses: stats.total_courses,
                totalLessons: stats.total_lessons,
                totalTopics: stats.total_topics,
                totalInstructors: stats.total_instructors
            };
            
            updateStatsDisplay(dashboardState.stats);
        } else {
            console.error('❌ Error al cargar estadísticas:', data.error);
        }
    } catch (error) {
        console.error('❌ Error de red al cargar estadísticas:', error);
        // Mostrar valores por defecto en caso de error
        updateStatsDisplay({
            totalCourses: 0,
            totalLessons: 0,
            totalTopics: 0,
            totalInstructors: 0
        });
    }
}

async function loadCourses() {
    try {
        console.log('📚 Cargando cursos...');
        
        const response = await fetch('/api/dashboard/courses');
        const data = await response.json();
        
        if (data.success) {
            dashboardState.courses = data.data.courses;
            updateCoursesDisplay();
        } else {
            console.error('❌ Error al cargar cursos:', data.error);
            showEmptyState();
        }
    } catch (error) {
        console.error('❌ Error de red al cargar cursos:', error);
        showEmptyState();
    }
}

function updateStatsDisplay(stats) {
    const totalCoursesEl = document.getElementById('total-courses');
    const totalLessonsEl = document.getElementById('total-lessons');
    const totalTopicsEl = document.getElementById('total-topics');
    const totalInstructorsEl = document.getElementById('total-instructors');
    
    if (totalCoursesEl) totalCoursesEl.textContent = stats.totalCourses;
    if (totalLessonsEl) totalLessonsEl.textContent = stats.totalLessons;
    if (totalTopicsEl) totalTopicsEl.textContent = stats.totalTopics;
    if (totalInstructorsEl) totalInstructorsEl.textContent = stats.totalInstructors;
}

function updateCoursesDisplay() {
    const coursesGrid = document.getElementById('courses-grid');
    const emptyState = document.getElementById('empty-state');
    const coursesCount = document.getElementById('courses-count');
    
    if (!coursesGrid) return;
    
    if (dashboardState.courses.length === 0) {
        showEmptyState();
        return;
    }
    
    // Ocultar estado vacío
    if (emptyState) emptyState.classList.add('hidden');
    
    // Actualizar contador
    if (coursesCount) {
        coursesCount.textContent = `${dashboardState.courses.length} curso${dashboardState.courses.length !== 1 ? 's' : ''} encontrado${dashboardState.courses.length !== 1 ? 's' : ''}`;
    }
    
    // Generar tarjetas de cursos
    coursesGrid.innerHTML = dashboardState.courses.map(course => createCourseCard(course)).join('');
}

function createCourseCard(course) {
    const ratingStars = '⭐'.repeat(Math.round(course.avg_rating));
    const ratingText = course.ratings_count > 0 ? `${course.avg_rating.toFixed(1)} (${course.ratings_count})` : 'Sin valoraciones';
    
    return `
        <div class="glass rounded-xl p-6 hover:bg-white/20 transition-all duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-lg">${course.name.charAt(0).toUpperCase()}</span>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">${course.topic_name}</div>
                    <div class="text-xs text-gray-400">${course.instructor_name}</div>
                </div>
            </div>
            
            <h3 class="font-semibold text-gray-800 mb-2">${course.name}</h3>
            
            <div class="flex items-center justify-between mb-4">
                <div class="text-sm text-gray-600">${course.instructor_name}</div>
                <div class="text-xs text-gray-500">${ratingStars} ${ratingText}</div>
            </div>
            
            <div class="flex space-x-2">
                <button class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                    Ver
                </button>
                <button class="px-3 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors">
                    Reanudar
                </button>
            </div>
        </div>
    `;
}

function showEmptyState() {
    const coursesGrid = document.getElementById('courses-grid');
    const emptyState = document.getElementById('empty-state');
    const coursesCount = document.getElementById('courses-count');
    
    if (coursesGrid) coursesGrid.innerHTML = '';
    if (emptyState) emptyState.classList.remove('hidden');
    if (coursesCount) coursesCount.textContent = '0 cursos encontrados';
}

function setupEmptyStateButtons() {
    const incrementalBtn = document.getElementById('scan-incremental-btn');
    const rebuildBtn = document.getElementById('scan-rebuild-btn');
    
    if (incrementalBtn) {
        incrementalBtn.addEventListener('click', function() {
            console.log('📁 Escaneo incremental desde dashboard');
            // TODO: Implementar escaneo incremental
        });
    }
    
    if (rebuildBtn) {
        rebuildBtn.addEventListener('click', function() {
            console.log('🔄 Rebuild completo desde dashboard');
            // TODO: Implementar rebuild completo
        });
    }
}
