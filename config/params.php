<?php

return [
    // Fotos (GD): peso máximo, lado mayor en píxeles y calidad del JPG
    'fotoMaximoMb' => 12,
    'fotoLadoMaximo' => 1600,
    'fotoLadoBanner' => 1920,
    'fotoLadoMiniatura' => 480,
    'fotoCalidadJpg' => 85,
    'fotosPorProducto' => 10,

    'productosPorPagina' => 24,

    // Estilos del sitio: se elige uno en el panel y lo ven todos los visitantes.
    // fuente: familia de Google Fonts para los títulos. oscuro: Bootstrap en modo dark.
    'temaPredeterminado' => 'generico',
    'temas' => [
        'generico' => ['nombre' => 'Genérico mixto', 'icono' => 'bi-bag-heart', 'fuente' => 'Poppins', 'oscuro' => false],
        'viveros' => ['nombre' => 'Viveros', 'icono' => 'bi-flower1', 'fuente' => 'Lora', 'oscuro' => false],
        'animales' => ['nombre' => 'Animales', 'icono' => 'bi-heart', 'fuente' => 'Baloo 2', 'oscuro' => false],
        'deportivo-rojo' => ['nombre' => 'Deportivo rojo', 'icono' => 'bi-trophy', 'fuente' => 'Oswald', 'oscuro' => false],
        'deportivo-azul' => ['nombre' => 'Deportivo azul', 'icono' => 'bi-trophy', 'fuente' => 'Oswald', 'oscuro' => false],
        'deportivo-verde' => ['nombre' => 'Deportivo verde', 'icono' => 'bi-trophy', 'fuente' => 'Oswald', 'oscuro' => false],
        'deportivo-naranja' => ['nombre' => 'Deportivo naranja', 'icono' => 'bi-trophy', 'fuente' => 'Oswald', 'oscuro' => false],
        'vehiculos' => ['nombre' => 'Vehículos', 'icono' => 'bi-car-front', 'fuente' => 'Rajdhani', 'oscuro' => true],
        'textil' => ['nombre' => 'Textil', 'icono' => 'bi-scissors', 'fuente' => 'Playfair Display', 'oscuro' => false],
        'bazar' => ['nombre' => 'Bazar', 'icono' => 'bi-cup-hot', 'fuente' => 'Quicksand', 'oscuro' => false],
        'tecnologico' => ['nombre' => 'Tecnológico', 'icono' => 'bi-cpu', 'fuente' => 'Space Grotesk', 'oscuro' => true],
        // Infantil: blanco con salpicaduras de pintura de colores
        'burbujas' => ['nombre' => 'Burbujas', 'icono' => 'bi-balloon-heart', 'fuente' => 'Fredoka', 'oscuro' => false],
        // Como germweb: oscuro, bordes cian con resplandor y paneles de vidrio
        'germ' => ['nombre' => 'GERM', 'icono' => 'bi-lightning-charge', 'fuente' => 'Orbitron', 'oscuro' => true],
    ],
];
