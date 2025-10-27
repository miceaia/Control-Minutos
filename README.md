# Control de Minutos para Advanced Video Player Pro

Este repositorio contiene un plugin de WordPress que extiende **Advanced Video Player Pro** para contabilizar los minutos visualizados por cada usuario y lección. El plugin añade un contador debajo de cada video y un panel administrativo para revisar, filtrar y exportar las visualizaciones.

## Características

- Registro automático de tiempo de reproducción por usuario y video.
- Sincronización directa con **Advanced Video Player Pro** y recuperación automática de la duración sincronizada desde Bunny.
- Detección del contexto de **LearnDash** para asociar cada reproducción con su curso y lección sin configuraciones extra.
- REST API protegida con _nonce_ para almacenar y consultar el progreso.
- Contador visual renovado que muestra minutos consumidos y minutos restantes justo debajo de cada video.
- Panel "Visualizaciones" con estética moderna, filtros por curso/usuario, búsqueda global y barra de progreso por fila.
- Exportación rápida (Reset, Copy, Excel, Print, PDF) gracias a DataTables Buttons.

## Integración

Cuando LearnDash está activo, el plugin obtiene automáticamente el listado de cursos (`sfwd-courses`) y lecciones (`sfwd-lessons`). Si necesitas sobrescribir nombres o añadir contenidos personalizados puedes seguir utilizando los filtros:

```php
add_filter( 'control_minutos_courses', function ( $courses ) {
    $courses[123] = 'Curso de ejemplo';
    return $courses;
} );

add_filter( 'control_minutos_lessons', function ( $lessons ) {
    $lessons[456] = 'Lección 1';
    return $lessons;
} );
```

El script frontal busca automáticamente los `<video>` que renderiza `miceaia-Advance-video-pro` (`.avppro-player`, `.miceaia-video-player` o `[data-avppro-player]`). Procura que cada reproductor exponga el identificador del video mediante `data-video-id`; si el plugin de sincronización tiene registrada la duración, el contador mostrará los minutos restantes desde el primer segundo de reproducción.

## Instalación

1. Copia la carpeta `control-minutos` dentro de `wp-content/plugins/` de tu instalación de WordPress.
2. Activa el plugin **Control de Minutos** desde el panel de administración.
3. Abre una lección con video para verificar que aparezca el contador de minutos consumidos.
4. Visita el panel **Visualizaciones** para consultar o exportar los registros.

## Desarrollo

- PHP 7.4 o superior.
- WordPress 5.8 o superior.
- DataTables 1.13 para la interfaz de reportes.

Las contribuciones son bienvenidas mediante _pull requests_.

### Selector de videos

Si tu reproductor genera un marcado diferente, ajusta el selector CSS utilizado por el script del contador:

```php
add_filter( 'control_minutos_video_selector', function () {
    return '.mi-clase-personalizada video';
} );
```
