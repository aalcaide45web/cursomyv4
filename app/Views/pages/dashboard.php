<div class="space-y-8">
    <!-- Header del dashboard -->
    <div class="text-center">
        <h1 class="text-4xl font-bold text-slate-100 mb-4">Bienvenido a CursoMy</h1>
        <p class="text-lg text-slate-300 max-w-2xl mx-auto">
            Tu plataforma de aprendizaje personal. Escanea tu carpeta de videos para comenzar a organizar tus cursos.
        </p>
    </div>
    
    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="glass rounded-xl p-6 text-center">
            <div class="text-3xl font-bold text-blue-400" id="total-courses">0</div>
            <div class="text-slate-300">Cursos</div>
        </div>
        <div class="glass rounded-xl p-6 text-center">
            <div class="text-3xl font-bold text-green-400" id="total-lessons">0</div>
            <div class="text-slate-300">Clases</div>
        </div>
        <div class="glass rounded-xl p-6 text-center">
            <div class="text-3xl font-bold text-purple-400" id="total-topics">0</div>
            <div class="text-slate-300">Temáticas</div>
        </div>
        <div class="glass rounded-xl p-6 text-center">
            <div class="text-3xl font-bold text-orange-400" id="total-instructors">0</div>
            <div class="text-slate-300">Instructores</div>
        </div>
    </div>
    
    <!-- Grid de cursos -->
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-slate-100">Mis Cursos</h2>
            <div class="text-sm text-slate-400" id="courses-count">0 cursos encontrados</div>
        </div>
        
        <!-- Grid de tarjetas de cursos -->
        <div id="courses-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <!-- Las tarjetas de cursos se cargarán dinámicamente aquí -->
        </div>
        
        <!-- Estado vacío -->
        <div id="empty-state" class="text-center py-12">
            <div class="w-24 h-24 mx-auto mb-4 text-slate-600">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M6 20h12M6 4h12"></path>
                </svg>
            </div>
            <h3 class="text-xl font-medium text-slate-200 mb-2">No hay cursos aún</h3>
            <p class="text-slate-400 mb-6">
                Coloca algunos videos en la carpeta /uploads y ejecuta el escaneo para comenzar.
            </p>
            <div class="space-x-3">
                <button id="scan-incremental-btn" class="btn-outline">
                    Escaneo Incremental
                </button>
                <button id="scan-rebuild-btn" class="btn-primary">
                    Rebuild Completo
                </button>
            </div>
        </div>
    </div>
</div>
