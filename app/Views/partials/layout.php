<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'CursoMy LMS Lite') ?></title>
    
    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Configuración de TailwindCSS para modo oscuro -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617'
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Estilos personalizados para glassmorphism en modo oscuro -->
    <style>
        .glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(148, 163, 184, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .glass-light {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            min-height: 100vh;
        }
        
        .btn-primary {
            @apply px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 font-medium;
        }
        
        .btn-secondary {
            @apply px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg transition-colors duration-200 font-medium;
        }
        
        .btn-outline {
            @apply px-4 py-2 border border-slate-400 hover:bg-slate-700 text-slate-300 rounded-lg transition-colors duration-200 font-medium;
        }
    </style>
</head>
<body class="bg-dark-900 text-slate-100 min-h-screen">
    <!-- Topbar -->
    <?php include __DIR__ . '/topbar.php'; ?>
    
    <!-- Contenido principal -->
    <main class="container mx-auto px-4 py-8">
        <?= $content ?>
    </main>
    
    <!-- Scripts -->
    <script type="module" src="/assets/js/main.js"></script>
</body>
</html>
