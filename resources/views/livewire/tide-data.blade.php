<div>
    {{-- Tide Data Component - Provides data to page --}}
    <div 
         x-data="{ 
             tides: @js($tides),
             source: @js($source),
             loading: @js($loading),
             error: @js($error)
         }"
         x-init="
             console.log('TideData component initialized');
             console.log('Tides count:', tides.length);
             console.log('Source:', source);
             
             // Make tides available globally IMMEDIATELY
             window.tidesData = tides;
             window.tidesSource = source;
             window.tidesLoading = loading;
             
             // Also dispatch event immediately for any already-initialized components
             setTimeout(() => {
                 window.dispatchEvent(new CustomEvent('tides-updated', { 
                     detail: { tides: tides, source: source }
                 }));
             }, 100);
             
             // Listen for Livewire updates (only if $wire is available)
             if (typeof $wire !== 'undefined') {
                 $wire.on('tides-loaded', (event) => {
                     console.log('Livewire tides-loaded event:', event);
                     window.tidesData = event.tides || event[0].tides;
                     window.tidesSource = event.source || event[0].source;
                     window.tidesLoading = false;
                     
                     // Trigger custom event for other components
                     window.dispatchEvent(new CustomEvent('tides-updated', { 
                         detail: { 
                             tides: window.tidesData, 
                             source: window.tidesSource 
                         }
                     }));
                 });
             }
         ">
        
        @if($error)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">{{ $error }}</p>
                    </div>
                </div>
            </div>
        @endif
        
       
       
    </div>
</div>
