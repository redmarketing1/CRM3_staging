<?php

namespace App\Livewire;

use Livewire\Component;

class EstimationTable extends Component
{
    public $estimation;
    public $estimationGroups     = [];
    public $ai_description_field;
    public $allQuotes;
    public $newItems             = []; // To store new rows

    public function mount($estimation, $ai_description_field, $allQuotes)
    {
        $this->estimation           = $estimation;
        $this->estimationGroups     = $estimation->estimationGroups ?? [];
        $this->ai_description_field = $ai_description_field;
        $this->allQuotes            = $allQuotes;
    }

    public function addItem()
    {
        // Create a new empty estimation group
        $newGroup = new \stdClass(); // Or your EstimationGroup model
        // Set default values if needed
        $newGroup->id = 'temp_' . count($this->newItems);
        // Add other default properties as needed

        $this->newItems[] = $newGroup;
    }

    public function removeItem($index)
    {
        unset($this->newItems[$index]);
        $this->newItems = array_values($this->newItems);
    }

    public function render()
    {
        return view('estimation::estimation.show.table.tbody');
    }
}