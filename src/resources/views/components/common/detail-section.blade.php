@props(['label', 'value' => null, 'class' => ''])

<div class="detail-section {{ $class }}">
  <div class="section-content">
    <span class="section-label">{{ $label }}</span>
    <span class="section-value">{{ $value ?? $slot }}</span>
  </div>
</div>
