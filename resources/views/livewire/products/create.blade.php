<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Mary\Traits\Toast;
use App\Models\Product;
use App\Models\ProductType;


new class extends Component {
    use Toast;

    #[Rule('required')]
    public string $product_name = '';

    #[Rule('required')]
    public string $customer_price = '';


    #[Rule('required')]
    public string $reseller_price = '';


    #[Rule('required')]
    public string $product_type_id = '';

    public function mount(): void
    {
        // $this->product = new Product();
        // $this->fill($this->product);
    }

    public function save(): void
    {
        $data = $this->validate();
        // print_r($data);
        $data['buy_price'] = 0;
        Product::create($data);

        $this->success("Data Produk berhasil dimasukkan", redirectTo: '/product');

    }

    public function with()
    {
        return [
            'product_type' => ProductType::all()
        ];
    }
}; ?>

<div>
    <x-header title="Tambah Produk" separator />

    <div class="lg:w-1/2">
        <x-form wire:submit="save">
            <x-input label="Nama Produk" wire:model="product_name" />
            <div class="lg:grid grid-cols-6 gap-3">
                <div class="col-span-3">
                    <x-input label="Harga Customer" wire:model="customer_price" money prefix="Rp"/>
                </div>
                <div class="col-span-3">
                    <x-input label="Harga Reseller" wire:model="reseller_price" money prefix="Rp"/>
                </div>
            </div>
            <x-select label="Kategori" wire:model="product_type_id" :options="$product_type" placeholder="---" />

            <x-slot:actions>
                <x-button label="Cancel" link="/product" />
                {{-- The important thing here is `type="submit"` --}}
                {{-- The spinner property is nice! --}}
                <x-button label="Simpan" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </div>
</div>