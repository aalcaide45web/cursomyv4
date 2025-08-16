<nav class="glass sticky top-0 z-50 border-b border-white/20">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-lg">C</span>
                </div>
                <span class="text-xl font-bold text-gray-800">CursoMy</span>
            </div>
            
            <!-- Buscador global -->
            <div class="flex-1 max-w-2xl mx-8">
                <div class="relative">
                    <input 
                        type="text" 
                        id="global-search"
                        placeholder="Buscar por temática, instructor, curso, nota, minuto..."
                        class="w-full px-4 py-2 pl-10 glass rounded-lg border-0 focus:ring-2 focus:ring-blue-500 focus:outline-none text-gray-800 placeholder-gray-500"
                    >
                    <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Botones de escaneo -->
            <div class="flex items-center space-x-3">
                <button 
                    id="scan-incremental"
                    class="px-4 py-2 glass rounded-lg text-sm font-medium text-gray-700 hover:bg-white/20 transition-all duration-200 flex items-center space-x-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Incremental</span>
                </button>
                
                <button 
                    id="scan-rebuild"
                    class="px-4 py-2 glass rounded-lg text-sm font-medium text-gray-700 hover:bg-white/20 transition-all duration-200 flex items-center space-x-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Rebuild</span>
                </button>
            </div>
        </div>
    </div>
</nav>

<!-- Barra de progreso para escaneo -->
<div id="scan-progress" class="hidden fixed top-16 left-0 w-full bg-blue-500 h-1 z-40">
    <div id="scan-progress-bar" class="bg-blue-600 h-full transition-all duration-300" style="width: 0%"></div>
</div>
