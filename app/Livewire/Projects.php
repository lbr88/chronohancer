<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Project;
use App\Models\Tag;

class Projects extends Component
{
    public $name;
    public $description;
    public $selectedTags = [];
    
    protected $rules = [
        'name' => 'required|min:3',
        'description' => 'required',
    ];
    
    public function save()
    {
        $this->validate();
        
        $project = Project::create([
            'name' => $this->name,
            'description' => $this->description,
            'user_id' => auth()->id(),
        ]);
        
        // Attach tags if any are selected
        if (!empty($this->selectedTags)) {
            $project->tags()->attach($this->selectedTags);
        }
        
        $this->reset(['name', 'description', 'selectedTags']);
        session()->flash('message', 'Project created successfully.');
    }
    
    public function render()
    {
        return view('livewire.projects', [
            'projects' => Project::where('user_id', auth()->id())->get(),
            'tags' => Tag::all(),
        ]);
    }
}
