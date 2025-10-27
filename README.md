# Control de Minutos para Advanced Video Player Pro

Este repositorio contiene un plugin de WordPress que extiende **Advanced Video Player Pro** para contabilizar los minutos visualizados por cada usuario y lección. El plugin añade un contador debajo de cada video y un panel administrativo para revisar, filtrar y exportar las visualizaciones.

## Características

- Registro automático de tiempo de reproducción por usuario y video.
- Sincronización con el reproductor de Advanced Video Player Pro mediante un selector personalizable.
- REST API protegida con _nonce_ para almacenar y consultar el progreso.
- Contador visual de minutos consumidos directamente bajo el video (`140/180`).
- Panel "Visualizaciones" en el administrador con filtros por Curso y Usuario.
- Exportación rápida (Reset, Copy, Excel, Print, PDF) gracias a DataTables Buttons.

## Integración

Para mapear los cursos y lecciones con sus nombres, utiliza los siguientes _hooks_ en tu tema o plugin:

```php
add_filter( 'control_minutos_courses', function () {
    return array(
        123 => 'Curso de ejemplo',
    );
} );

add_filter( 'control_minutos_lessons', function () {
    return array(
        456 => 'Lección 1',
    );
} );
```

Asegúrate de que los elementos `<video>` generados por Advanced Video Player Pro incluyan los atributos `data-video-id`, `data-course-id` y `data-lesson-id` para que el seguimiento identifique correctamente cada reproducción.

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
