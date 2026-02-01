<?php

namespace App\Livewire\Store\Dashboard;

use App\Models\ActivityLog;
use Livewire\Component;
use Livewire\WithPagination;

class LogsList extends Component
{
    use WithPagination;

    public $slug;

    public function mount($slug)
    {
        $this->slug = $slug;
    }

    public function render()
    {
        // Mudamos para current_store_id para bater com o seu banco
        $storeId = auth('store_user')->user()->current_store_id; 

        $logs = ActivityLog::where('store_id', $storeId)
            ->latest()
            ->paginate(10);

        return view('livewire.store.dashboard.logs-list', [
            'logs' => $logs
        ]);
    }
}