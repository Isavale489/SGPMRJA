{{-- Componente para botones de reportes estandarizados --}}

@props([
    'type' => 'pdf', // pdf, excel, general, back
    'route' => '#',
    'text' => '',
    'icon' => '',
    'target' => '_blank',
    'class' => ''
])

@php
    // Configuraciones estándar para cada tipo de botón
    $configs = [
        'pdf' => [
            'class' => 'btn btn-danger',
            'icon' => 'ri-file-pdf-line',
            'text' => 'Exportar PDF'
        ],
        'excel' => [
            'class' => 'btn btn-success',
            'icon' => 'ri-file-excel-line',
            'text' => 'Exportar Excel'
        ],
        'general' => [
            'class' => 'btn btn-info',
            'icon' => 'ri-file-list-3-line',
            'text' => 'Ver Reporte'
        ],
        'back' => [
            'class' => 'btn btn-secondary',
            'icon' => 'ri-arrow-go-back-line',
            'text' => 'Volver'
        ],
        'print' => [
            'class' => 'btn btn-warning',
            'icon' => 'ri-printer-line',
            'text' => 'Imprimir'
        ]
    ];

    $config = $configs[$type] ?? $configs['general'];
    
    // Permitir sobrescribir valores por defecto
    $buttonClass = $class ?: $config['class'];
    $buttonIcon = $icon ?: $config['icon'];
    $buttonText = $text ?: $config['text'];
@endphp

<a href="{{ $route }}" 
   class="{{ $buttonClass }} ms-2" 
   @if($target) target="{{ $target }}" @endif
   {{ $attributes }}>
    <i class="{{ $buttonIcon }} align-bottom me-1"></i> {{ $buttonText }}
</a>