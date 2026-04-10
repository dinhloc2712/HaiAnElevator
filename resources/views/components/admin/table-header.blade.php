@props(['key', 'label', 'sortColumn', 'sortOrder'])

@php
    $isSorted = $sortColumn === $key;
    $nextOrder = $isSorted && $sortOrder === 'asc' ? 'desc' : 'asc';
    $icon = $isSorted 
        ? ($sortOrder === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down') 
        : 'fas fa-sort text-muted opacity-25';
    $url = request()->fullUrlWithQuery(['sort_by' => $key, 'sort_order' => $nextOrder]);
@endphp

<th {{ $attributes->merge(['class' => 'cursor-pointer']) }} 
    onclick="window.location.href='{{ $url }}'"
    style="cursor: pointer; user-select: none;">
    <div class="d-flex justify-content-between align-items-center">
        <span>{{ $label }}</span>
        <i class="{{ $icon }}"></i>
    </div>
</th>
