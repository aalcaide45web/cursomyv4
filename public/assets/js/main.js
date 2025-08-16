// Archivo principal de JavaScript
console.log('🚀 CursoMy LMS Lite iniciado');

// Importar módulos del dashboard
import './dashboard/index.js';

// Funcionalidad global
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ DOM cargado completamente');
    
    // Inicializar funcionalidades globales
    initializeGlobalSearch();
    initializeScanButtons();
});

function initializeGlobalSearch() {
    const searchInput = document.getElementById('global-search');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.trim();
            if (query.length >= 2) {
                // TODO: Implementar búsqueda global
                console.log('🔍 Búsqueda:', query);
            }
        });
    }
}

function initializeScanButtons() {
    const incrementalBtn = document.getElementById('scan-incremental');
    const rebuildBtn = document.getElementById('scan-rebuild');
    
    if (incrementalBtn) {
        incrementalBtn.addEventListener('click', function() {
            console.log('📁 Escaneo incremental iniciado');
            // TODO: Implementar escaneo incremental
        });
    }
    
    if (rebuildBtn) {
        rebuildBtn.addEventListener('click', function() {
            console.log('🔄 Rebuild completo iniciado');
            // TODO: Implementar rebuild completo
        });
    }
}
