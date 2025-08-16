<div class="space-y-8">
    <!-- Header del dashboard -->
    <div class="text-center">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">Bienvenido a CursoMy</h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Tu plataforma de aprendizaje personal. Escanea tu carpeta de videos para comenzar a organizar tus cursos.
        </p>
    </div>
    
    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="glass rounded-xl p-6 text-center">
            <div class="text-3xl font-bold text-blue-600" id="total-courses">0</div>
            <div class="text-gray-600">Cursos</div>
        </div>
        <div class="glass rounded-xl p-6 text-center">
            <div class="text-3xl font-bold text-green-600" id="total-lessons">0</div>
            <div class="text-gray-600">Clases</div>
        </div>
        <div class="glass rounded-xl p-6 text-center">
            <div class="text-3xl font-bold text-purple-600" id="total-topics">0</div>
            <div class="text-gray-600">Temáticas</div>
        </div>
        <div class="glass rounded-xl p-6 text-center">
            <div class="text-3xl font-bold text-orange-600" id="total-instructors">0</div>
            <div class="text-gray-600">Instructores</div>
        </div>
    </div>
    
    <!-- Grid de cursos -->
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800">Mis Cursos</h2>
            <div class="text-sm text-gray-500" id="courses-count">0 cursos encontrados</div>
        </div>
        
        <!-- Grid de tarjetas de cursos -->
        <div id="courses-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <!-- Las tarjetas de cursos se cargarán dinámicamente aquí -->
        </div>
        
        <!-- Estado vacío -->
        <div id="empty-state" class="text-center py-12">
            <div class="w-24 h-24 mx-auto mb-4 text-gray-300">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M6 20h12M6 4h12"></path>
                </svg>
            </div>
            <h3 class="text-xl font-medium text-gray-900 mb-2">No hay cursos aún</h3>
            <p class="text-gray-500 mb-6">
                Coloca algunos videos en la carpeta /uploads y ejecuta el escaneo para comenzar.
            </p>
            <div class="space-x-3">
                <button id="scan-incremental-btn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Escaneo Incremental
                </button>
                <button id="scan-rebuild-btn" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    Rebuild Completo
                </button>
            </div>
        </div>
    </div>
</div>
