<?php

namespace App\Livewire;

use App\Services\TideDataService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class TideData extends Component
{
    public $tides = [];
    public $loading = true;
    public $error = null;
    public $source = 'Rijkswaterstaat CSV';
    public $availableYears = [];
    
    protected $tideService;

    public function mount()
    {
        $this->loadTideData();
    }

    public function loadTideData()
    {
        $this->loading = true;
        $this->error = null;

        try {
            $this->loadCsvTideData();
        } catch (\Exception $e) {
            Log::error('Error loading tide data', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $this->error = 'Kon getijdendata niet laden uit CSV bestanden.';
            $this->tides = [];
        }

        $this->loading = false;
        
        // Dispatch browser event to update Alpine components  
        $this->dispatch('tides-loaded', [
            'tides' => $this->tides,
            'source' => $this->source
        ]);
    }

    private function loadCsvTideData()
    {
        $tideService = app(TideDataService::class);
        
        // Get all available tide data
        $allTides = $tideService->getAllTides();
        
        if (!empty($allTides)) {
            $this->tides = $allTides;
            $this->availableYears = $tideService->getAvailableYears();
            $this->source = 'Rijkswaterstaat CSV';
            
            Log::info('Loaded CSV tide data', [
                'count' => count($this->tides),
                'years' => $this->availableYears,
            ]);
        } else {
            throw new \Exception('No tide data found in CSV files');
        }
    }

    public function refresh()
    {
        $this->loadTideData();
    }

    public function render()
    {
        return view('livewire.tide-data');
    }
}
