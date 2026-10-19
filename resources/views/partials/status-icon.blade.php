{{-- Rond statusicoon; $status is een Alpine-expressie, $size een Tailwind-maat --}}
<span class="flex-shrink-0 rounded-full flex items-center justify-center text-white {{ $size ?? 'w-10 h-10' }}"
    :class="'dot-' + ({!! $status !!})" aria-hidden="true">
    <svg class="{{ $iconSize ?? 'w-5 h-5' }}" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" x-show="({!! $status !!}) === 'green'">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
    </svg>
    <svg class="{{ $iconSize ?? 'w-5 h-5' }}" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" x-show="({!! $status !!}) === 'amber'">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v5m0 4h.01" />
    </svg>
    <svg class="{{ $iconSize ?? 'w-5 h-5' }}" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" x-show="!['green', 'amber'].includes({!! $status !!})">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
    </svg>
</span>
