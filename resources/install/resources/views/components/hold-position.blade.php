@props(['position', 'containers', 'containerPositions'])

@php
    $containerId = collect($containerPositions)->search(function ($containerData) use ($position) {
        return $containerData['position_id'] === $position->id;
    });

    $containerData = $containerId ? $containerPositions[$containerId] : null;
    $container = $containers->find($containerId);
@endphp

<div class="hold-position border {{ $position->side ? 'position-' . $position->side : 'position-center' }}"
    x-on:click="selectPosition('{{ $position->id }}')"
    x-bind:class="{
        'selected': selectedPosition === '{{ $position->id }}',
        'occupied': @js($containerData !== null)
    }">
    @if ($containerData)
        <div class="position-card"
            x-on:click.stop="selectContainer('{{ $containerId }}')"
            x-bind:class="{ 'selected': selectedContainer === '{{ $containerId }}' }">
            <div class="card h-100 {{ $containerData['content_type'] }}">
                <div class="card-body p-1 text-center">
                    <div class="position-code">{{ $position->code }}</div>
                    <div class="container-number">{{ $containerData['container_number'] }}</div>
                    <div class="container-details">
                        <i class="bi bi-{{ $containerData['content_type'] === 'baggage' ? 'luggage' : 'box-seam' }}"></i>
                        {{ number_format($containerData['weight']) }} kg
                        @if ($containerData['pieces'])
                            <small class="text-muted">({{ $containerData['pieces'] }} pcs)</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="empty-position">
            <div class="position-code">{{ $position->code }}</div>
            <i class="bi bi-box-seam"></i>
            <small class="text-muted">{{ $position->max_weight }} kg</small>
        </div>
    @endif
</div>
