<?php

use App\Models\ProductType;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Rule;

new class extends Component {
    use Toast;

    #[Rule('required')]
    public string $name = '';

    public function mount(): void
    {
        // $this->product = new Product();
        // $this->fill($this->product);
    }

    public function save(): void
    {
        $data = $this->validate();
        ProductType::create($data);

        $this->success("Data Kategori Produk berhasil dimasukkan", redirectTo: '/product_types');

    }


}; ?>

<div>
    <x-header title="Tambah Kategori Produk" separator />

    <div class="w-1/2">
        <x-form wire:submit="save">
            <x-input label="Nama Kategori Produk" wire:model="name" />

            <x-slot:actions>
                <x-button label="Cancel" link="/product_types" />
                {{-- The important thing here is `type="submit"` --}}
                {{-- The spinner property is nice! --}}
                <x-button label="Simpan" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </div>
</div>