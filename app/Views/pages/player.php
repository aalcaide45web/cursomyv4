<div class="min-h-screen bg-dark-900">
    <!-- Header del reproductor -->
    <div class="glass border-b border-slate-700/50 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="/" class="text-slate-400 hover:text-slate-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-100" id="lesson-title">Cargando...</h1>
                    <p class="text-sm text-slate-400" id="course-info">Cargando...</p>
                </div>
            </div>
            
            <!-- Controles del reproductor -->
            <div class="flex items-center space-x-4">
                <!-- Velocidad de reproducción -->
                <div class="flex items-center space-x-2">
                    <label class="text-sm text-slate-400">Velocidad:</label>
                    <select id="playback-speed" class="bg-slate-800 border border-slate-600 rounded px-2 py-1 text-slate-200 text-sm">
                        <option value="0.50">0.50x</option>
                        <option value="0.75">0.75x</option>
                        <option value="1.00" selected>1.00x</option>
                        <option value="1.25">1.25x</option>
                        <option value="1.50">1.50x</option>
                        <option value="1.75">1.75x</option>
                        <option value="2.00">2.00x</option>
                        <option value="2.25">2.25x</option>
                        <option value="2.50">2.50x</option>
                        <option value="2.75">2.75x</option>
                        <option value="3.00">3.00x</option>
                        <option value="3.25">3.25x</option>
                        <option value="3.50">3.50x</option>
                        <option value="3.75">3.75x</option>
                        <option value="4.00">4.00x</option>
                        <option value="4.25">4.25x</option>
                        <option value="4.50">4.50x</option>
                        <option value="4.75">4.75x</option>
                        <option value="5.00">5.00x</option>
                        <option value="5.25">5.25x</option>
                        <option value="5.50">5.50x</option>
                        <option value="5.75">5.75x</option>
                        <option value="6.00">6.00x</option>
                        <option value="6.25">6.25x</option>
                        <option value="6.50">6.50x</option>
                        <option value="6.75">6.75x</option>
                        <option value="7.00">7.00x</option>
                        <option value="7.25">7.25x</option>
                        <option value="7.50">7.50x</option>
                        <option value="7.75">7.75x</option>
                        <option value="8.00">8.00x</option>
                    </select>
                </div>
                
                <!-- Botón de pantalla completa -->
                <button id="fullscreen-btn" class="p-2 text-slate-400 hover:text-slate-200 hover:bg-slate-700 rounded transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Contenido principal -->
    <div class="flex h-screen">
        <!-- Panel izquierdo: Reproductor -->
        <div class="flex-1 flex flex-col">
            <!-- Reproductor de video -->
            <div class="flex-1 bg-black relative">
                <video 
                    id="video-player" 
                    class="w-full h-full"
                    preload="metadata"
                    data-lesson-id=""
                    muted
                >
                    Tu navegador no soporta el elemento de video.
                </video>
                
                <!-- Overlay de controles personalizados -->
                <div id="custom-controls" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4">
                    <!-- Barra de progreso personalizada -->
                    <div class="mb-4">
                        <div class="flex items-center space-x-2 text-white text-sm mb-1">
                            <span id="current-time">0:00</span>
                            <span>/</span>
                            <span id="total-time">0:00</span>
                        </div>
                        <div class="relative">
                            <div class="w-full bg-slate-600 rounded-full h-2">
                                <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-150" style="width: 0%"></div>
                            </div>
                            <input 
                                type="range" 
                                id="progress-slider" 
                                class="absolute inset-0 w-full h-2 opacity-0 cursor-pointer"
                                min="0" 
                                max="100" 
                                step="0.1"
                            >
                        </div>
                    </div>
                    
                    <!-- Controles de reproducción -->
                    <div class="flex items-center justify-center space-x-4">
                        <button id="play-pause-btn" class="p-3 bg-white/20 hover:bg-white/30 rounded-full transition-colors">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </button>
                        
                        <button id="rewind-10" class="p-2 text-white hover:bg-white/20 rounded transition-colors" title="Retroceder 10 segundos">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.334 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z"></path>
                            </svg>
                            <span class="text-xs">-10s</span>
                        </button>
                        
                        <button id="forward-10" class="p-2 text-white hover:bg-white/20 rounded transition-colors" title="Avanzar 10 segundos">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                            </svg>
                            <span class="text-xs">+10s</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Información de la lección -->
            <div class="glass p-4">
                <h2 class="text-lg font-semibold text-slate-100 mb-2" id="lesson-description">Descripción de la lección</h2>
                <div class="flex items-center space-x-4 text-sm text-slate-400">
                    <span id="lesson-duration">Duración: --:--</span>
                    <span id="lesson-progress">Progreso: 0%</span>
                </div>
            </div>
        </div>
        
        <!-- Panel derecho: Notas, comentarios y recursos -->
        <div class="w-96 bg-slate-800 border-l border-slate-700 flex flex-col">
            <!-- Tabs de navegación -->
            <div class="flex border-b border-slate-700">
                <button class="flex-1 py-3 px-4 text-slate-300 hover:text-slate-100 hover:bg-slate-700 transition-colors border-b-2 border-transparent hover:border-blue-500" data-tab="notes">
                    📝 Notas
                </button>
                <button class="flex-1 py-3 px-4 text-slate-300 hover:text-slate-100 hover:bg-slate-700 transition-colors border-b-2 border-transparent hover:border-blue-500" data-tab="comments">
                    💬 Comentarios
                </button>
                <button class="flex-1 py-3 px-4 text-slate-300 hover:text-slate-100 hover:bg-slate-700 transition-colors border-b-2 border-transparent hover:border-blue-500" data-tab="resources">
                    📎 Recursos
                </button>
            </div>
            
            <!-- Contenido de los tabs -->
            <div class="flex-1 overflow-y-auto">
                <!-- Tab de Notas -->
                <div id="tab-notes" class="tab-content p-4">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-slate-100 mb-2">Agregar Nota</h3>
                        <div class="space-y-3">
                            <input 
                                type="text" 
                                id="note-timestamp" 
                                placeholder="Timestamp (ej: 1:30)" 
                                class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded text-slate-200 placeholder-slate-400"
                            >
                            <textarea 
                                id="note-text" 
                                placeholder="Escribe tu nota aquí..." 
                                rows="3"
                                class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded text-slate-200 placeholder-slate-400 resize-none"
                            ></textarea>
                            <button id="add-note-btn" class="w-full btn-primary">
                                Agregar Nota
                            </button>
                        </div>
                    </div>
                    
                    <!-- Buscador de notas -->
                    <div class="mb-4">
                        <div class="relative">
                            <input 
                                type="text" 
                                id="note-search" 
                                placeholder="🔍 Buscar en notas..." 
                                class="w-full px-3 py-2 pl-10 bg-slate-700 border border-slate-600 rounded text-slate-200 placeholder-slate-400"
                            >
                            <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Lista de notas con scroll -->
                    <div class="max-h-64 overflow-y-auto space-y-3 pr-2" id="notes-list">
                        <!-- Las notas se cargarán dinámicamente aquí -->
                    </div>
                </div>
                
                <!-- Tab de Comentarios -->
                <div id="tab-comments" class="tab-content p-4 hidden">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-slate-100 mb-2">Agregar Comentario</h3>
                        <div class="space-y-3">
                            <textarea 
                                id="comment-text" 
                                placeholder="Escribe tu comentario aquí..." 
                                rows="3"
                                class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded text-slate-200 placeholder-slate-400 resize-none"
                            ></textarea>
                            <button id="add-comment-btn" class="w-full btn-primary">
                                Agregar Comentario
                            </button>
                        </div>
                    </div>
                    
                    <div id="comments-list" class="space-y-3">
                        <!-- Los comentarios se cargarán dinámicamente aquí -->
                    </div>
                </div>
                
                <!-- Tab de Recursos -->
                <div id="tab-resources" class="tab-content p-4 hidden">
                    <h3 class="text-lg font-semibold text-slate-100 mb-4">Recursos de la Sección</h3>
                    <div id="resources-list" class="space-y-3">
                        <!-- Los recursos se cargarán dinámicamente aquí -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts del reproductor -->
<script type="module" src="/assets/js/player/index.js"></script>
