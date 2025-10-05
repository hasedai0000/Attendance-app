@props(['type' => 'button', 'variant' => 'primary', 'size' => 'medium', 'class' => ''])

@php
$baseClasses = 'action-button';
$variantClasses = [
    'primary' => 'action-button-primary',
    'secondary' => 'action-button-secondary',
    'success' => 'action-button-success',
    'danger' => 'action-button-danger'
];
$sizeClasses = [
    'small' => 'action-button-small',
    'medium' => 'action-button-medium',
    'large' => 'action-button-large'
];

$classes = $baseClasses . ' ' . $variantClasses[$variant] . ' ' . $sizeClasses[$size] . ' ' . $class;
@endphp

<button type="{{ $type }}" class="{{ trim($classes) }}">
  {{ $slot }}
</button>
