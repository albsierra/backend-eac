@props(['gradiente'])

@php
$clases = match($gradiente) {
    'autonomo'    => 'badge-autonomo',
    'supervisado' => 'badge-supervisado',
    'guiado'      => 'badge-guiado',
    'asistido'    => 'badge-asistido',
    default       => 'badge-asistido',
};
$etiquetas = [
    'autonomo'    => 'Autónomo',
    'supervisado' => 'Supervisado',
    'guiado'      => 'Guiado',
    'asistido'    => 'Asistido',
];
@endphp

<span class="{{ $clases }}">{{ $etiquetas[$gradiente] ?? $gradiente }}</span>
