# CursoMy LMS Lite

Un sistema de gestión de aprendizaje (LMS) ligero y personal para organizar tus videos de cursos.

## 🚀 Características

- **Dashboard intuitivo** con tarjetas de cursos
- **Escaneo automático** de carpetas de videos
- **Player HTML5** con control de velocidad (0.50x - 10.00x)
- **Sistema de notas** con timestamps
- **Comentarios** por clase
- **Valoraciones** con estrellas
- **Buscador global** por contenido y tiempo
- **Diseño glassmorphism** con TailwindCSS

## 📋 Requisitos

- PHP 8.0+
- SQLite3
- Servidor web (Apache/Nginx) o servidor PHP integrado

## 🛠️ Instalación

1. **Clonar el repositorio:**
   ```bash
   git clone <url-del-repositorio>
   cd cursomyV3
   ```

2. **Configurar la base de datos:**
   ```bash
   php scripts/init_db.php
   ```

3. **Configurar permisos:**
   ```bash
   chmod 755 uploads/
   chmod 755 cache/
   ```

4. **Iniciar servidor de desarrollo:**
   ```bash
   php -S localhost:8000 -t public
   ```

5. **Abrir en navegador:**
   ```
   http://localhost:8000
   ```

## 📁 Estructura de Videos

Coloca tus videos siguiendo esta estructura:
```
/uploads/
  /{topic}/           # Ej: programacion, diseño, marketing
    /{instructor}/    # Ej: juan_perez, maria_garcia
      /{course}/      # Ej: php_basico, react_avanzado
        /{section}/   # Ej: fundamentos, intermedio
          /{lesson}.mp4  # Ej: variables.mp4, funciones.mp4
```

## 🔧 Uso

1. **Primer uso:** Ejecuta "Rebuild" para escanear toda la carpeta
2. **Uso diario:** Usa "Incremental" para detectar solo cambios
3. **Organizar:** Los videos se organizan automáticamente por estructura de carpetas
4. **Aprender:** Reproduce videos, toma notas y marca progreso

## 📚 Fases de Desarrollo

- ✅ **FASE 0:** Estructura base y dashboard vacío
- 🔄 **FASE 1:** Base de datos y repositorios
- ⏳ **FASE 2:** Escáner e importador
- ⏳ **FASE 3:** Dashboard completo
- ⏳ **FASE 4:** Páginas de curso
- ⏳ **FASE 5:** Player y funcionalidades
- ⏳ **FASE 6:** Sistema de valoraciones
- ⏳ **FASE 7:** Buscador global
- ⏳ **FASE 8:** Extras y pulido

## 🎯 Próximos Pasos

- Implementar escáner de archivos
- Crear sistema de player HTML5
- Añadir funcionalidades de notas y comentarios
- Implementar buscador global

## 📝 Licencia

Este proyecto es de uso personal y educativo.
