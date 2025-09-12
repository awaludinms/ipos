<?php

use App\Models\ProductType;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Mary\Traits\Toast;

new class extends Component {
    //
    use Toast;

    public ProductType $product_type;

    #[Rule('required')]
    public string $name = '';

    public function mount(): void
    {
        $this->fill($this->product_type);
    }

    // public function with()
    // {
    //     // return [
    //     //     'product_type' => ProductType::all()
    //     // ];
    // }

    public function save(): void
    {
        $data = $this->validate();
        $this->product_type->update($data);

        $this->success("Data Kategori Produk berhasil diubah", redirectTo: '/product_types');

    }

}; ?>

<div>
    <x-header title="Edit Kategori Produk" separator />

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