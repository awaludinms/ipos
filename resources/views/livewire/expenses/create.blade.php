<?php

use App\Models\Expenses;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Rule;

new class extends Component {
    use Toast;

    #[Rule('required')]
    public string $description = '';

    #[Rule('required')]
    public string $person = '';

    #[Rule('required')]
    public string $expense_value = '';

    #[Rule('required')]
    public string $expenses_date = '';

    public function mount(): void
    {
        if (Auth::user()->role_id != 4) {
            redirect('/transactions/');
        }
        // $this->product = new Product();
        // $this->fill($this->product);
    }

    public function save(): void
    {
        $data = $this->validate();
        Expenses::create($data);

        $this->success("Data Pengeluaran berhasil dimasukkan", redirectTo: '/expenses');

    }


}; ?>

<div>
    <x-header title="Tambah Pengeluaran" separator />

    <div class="w-1/2">
        <x-form wire:submit="save">
            <x-datetime label="Tanggal dan Waktu Pengeluaran" wire:model="expenses_date" type="datetime-local"/>
            <x-textarea label="Deskrispi" wire:model="description" />

            <x-input label="Personal" wire:model="person" />
            <x-input label="Nilai Pengeluaran" wire:model="expense_value" prefix="Rp" />

            <x-slot:actions>
                <x-button label="Cancel" link="/expenses" />
                {{-- The important thing here is `type="submit"` --}}
                {{-- The spinner property is nice! --}}
                <x-button label="Simpan" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </div>
</div>