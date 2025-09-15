<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Models\Transactions;
use App\Models\TransactionDetails;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Log;


new class extends Component {
    use WithPagination;
    use Toast;

    public bool $myModal1 = false;

    public bool $myModal2 = false;

    public bool $myModalPelanggan = false;

    public string $search = '';

    public bool $drawer = false;

    public int $hidden_trans_id = -1;

    public string $customer_name, $customer_type, $transaction_date, $pay_status, $staff_name, $staff_id, $transaction_pay_type, $keterangan;

    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];

    public $selectedMetodePembayaran = '';

    public $grandTotal = 0;

    public $paid = 0;

    public $change_return = 0;

    public $grandTotalCalc = 0;

    // Selected option
    public ?int $user_searchable_id = null;

    // Options list
    public Collection $usersSearchable;

    #[Rule('required')]
    public string $name = '';

    #[Rule('required')]
    public string $phone = '';

    #[Rule('required')]
    public string $address = '';

    //
    public function products()
    {
        return Product::query()->selectRaw('id, product_name, customer_price, reseller_price, product_type_id')
            ->when($this->search, fn(Builder $q) => $q->where('product_name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(5);
    }

    public function detailTrans()
    {
        return TransactionDetails::query()->where('transaction_id', $this->hidden_trans_id)->get();
    }

    public function headers(): array
    {
        return [
            ['key' => 'product_name', 'label' => 'Nama Produk', 'class' => 'w-64'],
            // ['key' => 'buy_price', 'label' => 'Harga Beli', 'class' => 'w-20'],
            ['key' => 'customer_price', 'label' => 'Harga', 'sortable' => false, 'format' => ['currency', '0,.', '']],
            // ['key' => 'product_type.name', 'label' => 'Kategori', 'sortable' => false],
        ];
    }

    public function headersDetTrans(): array
    {
        return [
            ['key' => 'product.product_name', 'label' => 'Nama Produk', 'class' => 'w-64'],
            // ['key' => 'buy_price', 'label' => 'Harga Beli', 'class' => 'w-20'],
            ['key' => 'product_qty', 'label' => 'Qty', 'sortable' => false, 'class' => 'w-10'],
            ['key' => 'product_price', 'label' => 'Harga', 'sortable' => false, 'format' => ['currency', '0,.', '']],
            ['key' => 'product_subtotal', 'label' => 'Sub Total', 'sortable' => false, 'format' => ['currency', '0,.', '']],
        ];
    }

    public function add($id)
    {

        // $this->warning($id);
        if ($this->hidden_trans_id == -1) {
            try {
                $this->hidden_trans_id = 10;

                $data = [
                    'transaction_number' => date('YmdHis'),
                    'customer_name' => $this->customer_name != "" ? "-" : $this->customer_name,
                    'transaction_date' => $this->transaction_date,
                    'customer_type' => 'customer',
                    'grand_total' => 0,
                    'paid' => 0,
                    'pay_status' => 0,
                    'change_return' => 0,
                    'staff_name' => 0,
                    'staff_id' => 0,
                    'transaction_pay_type' => $this->transaction_pay_type,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $trans_id = Transactions::insertGetId($data);
                $this->hidden_trans_id = $trans_id;

                // $this->warning($trans_id . json_encode($data) . 'It is fake.', position: 'toast-bottom');
                // $this->warning("Will delete #$id", );

                TransactionDetails::create([
                    'transaction_id' => $trans_id,
                    'product_id' => $id,
                    'product_qty' => 1,
                    'product_price' => Product::find($id)->customer_price,
                    'product_subtotal' => Product::find($id)->customer_price,
                    'created_by' => Auth::user()->id,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $grandTotal = TransactionDetails::query()
                    ->where('transaction_id', $trans_id)
                    ->selectRaw("SUM(product_qty * product_price) as grand_total")
                    ->first()->grand_total;

            } catch (\Exception $e) {
                Log::debug(json_encode($e->getMessage()));
                $this->warning(json_encode($e->getMessage()) . 'It is fake.', position: 'toast-bottom');
                // $this->warning(json_encode($e->getMessage()));
            }
        } else {
            // $this->warning('----' . 'It is fake.', position: 'toast-bottom');
            $exists = TransactionDetails::where([
                'product_id' => $id,
                'transaction_id' => $this->hidden_trans_id,
            ])
                ->exists();

            if (!$exists) {
                TransactionDetails::create([
                    'transaction_id' => $this->hidden_trans_id,
                    'product_id' => $id,
                    'product_qty' => 1,
                    'product_price' => Product::find($id)->customer_price,
                    'product_subtotal' => Product::find($id)->customer_price,
                    'created_by' => Auth::user()->id,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            } else {

                $tr = TransactionDetails::where([
                    'product_id' => $id,
                    'transaction_id' => $this->hidden_trans_id
                ])->first();

                $last_qty = $tr->product_qty;
                $subtotal = ($last_qty + 1) * Product::find($id)->customer_price;

                $tr->update([
                    'product_qty' => ($last_qty + 1),
                    'product_subtotal' => $subtotal,
                ]);
            }

            $grandTotal = TransactionDetails::query()
                ->where('transaction_id', $this->hidden_trans_id)
                ->selectRaw("SUM(product_qty * product_price) as grand_total")
                ->first()->grand_total;
        }
        $this->grandTotalCalc = $grandTotal;
        $this->grandTotal = number_format($grandTotal, 0, ',', '.');

        $this->myModal1 = false;
    }

    public function mount()
    {
        $this->customer_name = '';
        $this->transaction_date = date('1900-01-01');
        $this->transaction_pay_type = '';
        $this->search();
    }

    public function with(): array
    {
        return [
            'products' => $this->products(),
            'headers' => $this->headers(),
            'headersDetTrans' => $this->headersDetTrans(),
            'detailTrans' => $this->detailTrans(),
            'metodePembayaran' => [
                ['id' => 1, 'name' => 'Tunai'],
                ['id' => 2, 'name' => 'Bank'],
                ['id' => 3, 'name' => 'QRIS'],
            ],
            'grandTotal' => $this->grandTotal,
            'grandTotalCalc' => $this->grandTotalCalc,

        ];
    }

    public function update($det_trans_id, $value)
    {
        // if ($value > 0) {
        $transdet = TransactionDetails::find($det_trans_id);
        $price = $transdet->product_price;
        $trans_id = $transdet->transaction_id;

        $transdet->update([
            'product_qty' => abs($value),
            'product_subtotal' => abs($value) * $price,
        ]);

        $grandTotal = TransactionDetails::query()
            ->where('transaction_id', $trans_id)
            ->selectRaw("SUM(product_qty * product_price) as grand_total")
            ->first()->grand_total;

        $this->grandTotalCalc = $grandTotal;

        $this->grandTotal = number_format($grandTotal, 0, ',', '.');
        // }
    }

    public function calculateChange()
    {
        $this->change_return = $this->paid - $this->grandTotalCalc;
    }

    // Delete action
    public function delete($id): void
    {
        $this->warning("Will delete #$id", 'deleted item.', position: 'toast-bottom');
        TransactionDetails::find($id)->delete();
    }

    // Also called as you type
    public function search(string $value = '')
    {
        // Besides the search results, you must include on demand selected option
        $selectedOption = Customer::where('id', $this->user_searchable_id)->get();

        $this->usersSearchable = Customer::query()
            ->where('name', 'like', "%$value%")
            ->take(5)
            ->orderBy('name')
            ->get()
            ->merge($selectedOption);     // <-- Adds selected option
    }

    public function savePelanggan()
    {
        Customer::create([
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
        ]);

        $this->search();
        $this->myModalPelanggan = false;
    }
}; ?>

<div>
    <div>
        <div class="lg:grid grid-cols-6 lg:gap-8 sm:gap-0">
            <?php /* <div class="col-span-3">


</div> */ ?>

            <x-modal wire:model="myModal1" title="Produk" subtitle="Pilih Produk" box-class="border"
                class="backdrop-blur">
                <x-input placeholder="Search..." wire:model.live.debounce="search" clearable
                    icon="o-magnifying-glass" />

                <!-- TABLE  -->
                <x-card shadow>
                    <x-table with-pagination :headers="$headers" :rows="$products" :sort-by="$sortBy"
                        @row-click="$wire.add($event.detail.id)">
                        @scope('actions', $product)
                        {{-- <x-button icon="o-pencil" wire:click="delete({{ $product['id'] }})"
                            wire:confirm="Are you sure?" spinner --}} {{-- class="btn-ghost btn-sm text-error" /> --}}
                        <x-button icon="o-plus" wire:click="add({{ $product['id'] }})" spinner
                            class="btn-ghost btn-sm text-error" />
                        @endscope
                    </x-table>
                </x-card>
            </x-modal>

            <x-modal wire:model="myModal2" title="Bayar" subtitle="Pembayaran" class="backdrop-blur">
                <x-form no-separator>
                    <div class=" text-right"><sup>Rp</sup><span class="text-6xl">{{ $grandTotal }}</span></div>

                    <x-input label="Nominal Pembayaran" wire:model="paid" wire:change="calculateChange"
                        placeholder="Nilai Pembayaran" prefix="Rp" />
                    <x-input readonly label="Kembalian" placeholder="Nilai Pembayaran" wire:model="change_return"
                        prefix="Rp" />
                    <div class="grid grid-cols-7 gap-3">
                        <div class="col-span-5">
                            <x-choices label="Pelanggan" wire:model="user_searchable_id" :options="$usersSearchable"
                                placeholder="Search ..." single searchable />
                        </div>
                        <div class="col-span-2 content-end text-right">
                            <x-button class="mt-2" label="Tambah" @click="$wire.myModalPelanggan = true" icon="o-plus"
                                class="btn-primary" />
                        </div>
                    </div>

                    <x-select label="Metode Pembayaran" wire:model="selectedMetodePembayaran"
                        :options="$metodePembayaran" icon="o-bars-arrow-down" />
                    <x-textarea label="Keterangan" wire:model="keterangan" />

                    {{-- Notice we are using now the `actions` slot from `x-form`, not from modal --}}
                    <x-slot:actions>
                        <x-button label="Batal" @click="$wire.myModal2 = false" />
                        <x-button label="Bayar" class="btn-primary" />
                    </x-slot:actions>
                </x-form>
            </x-modal>

            <x-modal wire:model="myModalPelanggan" title="Pelanggan" class="backdrop-blur" subtitle="Tambah Pelanggan">
                <x-form no-separator wire:submit="savePelanggan">

                    <x-input label="Nama Pelanggan" wire:model="name" icon="o-user" />
                    <x-input label="Nomor HP" wire:model="phone" icon="o-user" />
                    <x-textarea label="Alamat" wire:model="address" />

                    {{-- Notice we are using now the `actions` slot from `x-form`, not from modal --}}
                    <x-slot:actions>
                        <x-button label="Batal" @click="$wire.myModalPelanggan = false" />
                        <x-button label="Simpan" class="btn-primary" spinner="save" type="submit" />
                    </x-slot:actions>
                </x-form>
            </x-modal>

            <div class="col-span-4">
                <x-header title="Transaksi" separator progress-indicator>
                    <x-slot:actions>
                        <div class="gap-3">
                            <x-button label="Tambah Item" @click="$wire.myModal1 = true" icon="o-plus"
                                class="btn-primary" />
                            <x-button label="Bayar" @click="$wire.myModal2 = true" icon="o-paper-airplane"
                                class="btn-success" spinner />
                        </div>
                    </x-slot:actions>
                </x-header>

                <div class=" text-right"><sup>Rp</sup><span class="text-6xl">{{ $grandTotal }}</span></div>
                {{-- <x-header title="" separator /> --}}

                <div class="pt-3.5">
                    <!-- TABLE  -->
                    <x-card shadow class="sm:p-0">
                        <x-table :headers="$headersDetTrans" :rows="$detailTrans" :sort-by="$sortBy">
                            @scope('cell_product_qty', $detTrans)
                            <x-input class="w-10" wire:change="update({{ $detTrans->id }}, $event.target.value)"
                                type="number" value="{{ $detTrans->product_qty }}" min="1" />
                            @endscope

                            @scope('actions', $product)
                            {{-- <x-button icon="o-pencil" wire:click="delete({{ $product['id'] }})"
                                wire:confirm="Are you sure?" spinner --}} {{-- class="btn-ghost btn-sm text-error" />
                            --}}
                            <x-button icon="o-trash" wire:click="delete({{ $product['id'] }})" spinner
                                class="btn-ghost btn-sm text-error" wire:confirm="Anda ingin menghapus Item ini?" />
                            @endscope
                        </x-table>
                    </x-card>
                </div>

            </div>
            {{-- <div class="col-span-2">
                <div>


                </div>
            </div> --}}
            <input type="hidden" value="{{ $hidden_trans_id }}">
        </div>
    </div>
</div>