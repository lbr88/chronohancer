<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Task;
use App\Models\Project;

class Tasks extends Component
{
    public $title;
    public $description;
    public $project_id;
    public $estimated_time;
    public $due_date;
    
    protected $rules = [
        'title' => 'required|min:3',
        'description' => 'required',
        'project_id' => 'required|exists:projects,id',
        'estimated_time' => 'required|numeric',
        'due_date' => 'required|date',
    ];
    
    public function save()
    {
        $this->validate();
        
        Task::create([
            'title' => $this->title,
            'description' => $this->description,
            'project_id' => $this->project_id,
            'estimated_time' => $this->estimated_time,
            'due_date' => $this->due_date,
            'user_id' => auth()->id(),
        ]);
        
        $this->reset(['title', 'description', 'estimated_time', 'due_date']);
        session()->flash('message', 'Task created successfully.');
    }
    
    public function render()
    {
        return view('livewire.tasks', [
            'tasks' => Task::where('user_id', auth()->id())->get(),
            'projects' => Project::where('user_id', auth()->id())->get(),
        ]);
    }
}
