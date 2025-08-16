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

function loadDashboardStats() {
    // TODO: Cargar estadísticas desde la API
    console.log('📈 Cargando estadísticas...');
    
    // Por ahora, mostrar valores por defecto
    updateStatsDisplay({
        totalCourses: 0,
        totalLessons: 0,
        totalTopics: 0,
        totalInstructors: 0
    });
}

function loadCourses() {
    // TODO: Cargar cursos desde la API
    console.log('📚 Cargando cursos...');
    
    // Por ahora, mostrar estado vacío
    showEmptyState();
}

function updateStatsDisplay(stats) {
    const totalCoursesEl = document.getElementById('total-courses');
    const totalLessonsEl = document.getElementById('total-lessons');
    const totalTopicsEl = document.getElementById('total-topics');
    const totalInstructorsEl = document.getElementById('total-instructors');
    
    if (totalCoursesEl) totalCoursesEl.textContent = stats.totalCourses;
    if (totalLessonsEl) totalLessonsEl.textContent = stats.totalLessons;
    if (totalTopicsEl) totalLessonsEl.textContent = stats.totalTopics;
    if (totalInstructorsEl) totalInstructorsEl.textContent = stats.totalInstructors;
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
