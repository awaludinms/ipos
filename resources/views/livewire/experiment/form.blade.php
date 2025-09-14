<?php

use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

new class extends Component {
 
 // Selected option
 public ?int $user_searchable_id = null;

 // Options list
 public Collection $usersSearchable;

 public function mount()
 {
     // Fill options when component first renders
     $this->search();
 }

 // Also called as you type
 public function search(string $value = '')
 {
     // Besides the search results, you must include on demand selected option
     $selectedOption = User::where('id', $this->user_searchable_id)->get();

     $this->usersSearchable = User::query()
         ->where('name', 'like', "%$value%")
         ->take(5)
         ->orderBy('name')
         ->get()
         ->merge($selectedOption);     // <-- Adds selected option
 }
}
?>

<div>
    <x-choices
    label="Searchable + Single"
    wire:model="user_searchable_id"
    :options="$usersSearchable"
    placeholder="Search ..."
    single
    searchable />
</div>
