<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Models\TransactionReceipts;
use App\Models\Transactions;
use App\Models\TransactionDetails;
use App\Models\LastTransactionNumber;
use App\Models\TransactionPayments;
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

    #[Rule('required')]
    public int $user_searchable_id = 0;

    #[Rule('required')]
    public string $name = '';

    #[Rule('required')]
    public string $phone = '';

    #[Rule('required')]
    public string $address = '';

    public bool $transDone = false;

    public bool $myModal1 = false;

    public bool $myModal2 = false;

    public bool $myModalPelanggan = false;

    public bool $myModalProsesSelesai = false;

    public bool $myModalCustomProduct = false;

    public bool $myModalAddNotes = false;

    public string $search = '';

    public bool $drawer = false;

    public int $hidden_trans_id = -1;

    public string $customer_name, $customer_type, $transaction_date, $pay_status, $staff_name, $staff_id, $transaction_pay_type, $keterangan;

    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];

    public $selectedMetodePembayaran = 1;

    public $grandTotal = 0;

    public $paid = 0;

    public $change_return = 0;

    public $grandTotalCalc = 0;

    // Selected option

    // public ?int $user_searchable_id = null;

    // Options list
    public Collection $usersSearchable;

    public string $product_custom_name = '';

    public int $product_custom_qty = 1;

    public string $product_custom_price = '';

    public string $product_custom_notes = '';

    public int $current_editing = 0;

    public ?string $detail_notes = '';

    public $taken_time, $taken_date, $order_date, $order_time;

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
        return TransactionDetails::query()
            ->selectRaw("transaction_details.id, IF(product_id is null, concat_ws('',transaction_details.product_name, ' \n<div class=\"tab\">', notes, '</div>'), concat_ws('',products.product_name, ' \n<div class=\"tab\">', notes, '</div>')) as product_name, product_qty, product_price, product_subtotal, notes")
            ->leftJoin('products', 'product_id', '=', 'products.id')
            ->where('transaction_id', $this->hidden_trans_id)->get();
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
            ['key' => 'product_name', 'label' => 'Nama Produk', 'class' => 'w-64'],
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
        $this->searchData('');

        // $this->search();

        // remove not process transaction (state new) within more than 24 hours
        Transactions::whereRaw('DATE_ADD(created_at, INTERVAL 24 HOUR) <= CURRENT_DATE()')
            ->where('transaction_state', 1)
            ->delete();

    }

    public function config()
    {
        return [
            'locale' => 'id'
        ];
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
            'config1' => $this->config(),
            'config2' => [
                'enableTime' => true,
                'noCalendar' => true,
                'dateFormat' => "H:i",
                'time_24hr' => true
            ],
            'usersSearchable' => [],
        ];
    }

    public function update($det_trans_id, $value)
    {
        $this->myModalAddNotes = false;
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
        if ($this->paid) {
            $this->change_return = ($this->paid - $this->grandTotalCalc < 0) ? 0 : $this->paid - $this->grandTotalCalc;
        }
    }

    // Delete action
    public function delete($id): void
    {
        $trans_det = TransactionDetails::find($id);

        $trans_id = $trans_det->transaction_id;

        $trans_det->delete();

        $count = TransactionDetails::where('transaction_id', $trans_id)->count();
        if (!$count) {
            $this->hidden_trans_id = -1;
        }


        // recalculate grand total

        $grandTotal = TransactionDetails::query()
            ->where('transaction_id', $trans_id)
            ->selectRaw("SUM(product_qty * product_price) as grand_total")
            ->first()->grand_total;

        $this->grandTotalCalc = $grandTotal;
        $this->grandTotal = number_format($grandTotal, 0, ',', '.');

        $this->warning("Item berhasil dihapus", 'deleted item.', position: 'toast-bottom');

    }

    // Also called as you type
    public function searchData(string $value = '')
    {
        // Besides the search results, you must include on demand selected option
        $selectedOption = Customer::where('id', $this->user_searchable_id)->get();

        $this->usersSearchable = Customer::query()
            ->where('name', 'like', "%$value%")
            ->take(45)
            ->orderBy('name')
            ->get()
            ->merge($selectedOption);     // <-- Adds selected option
    }

    public function savePelanggan()
    {
        $this->validate([
            'name' => 'required',
            'phone' => 'required',
            'address' => 'required'
        ], [
            'name.required' => 'Nama tidak boleh kosong',
            'phone.required' => 'Nomor Tel/Hp tidak boleh kosong',
            'address.required' => 'Alamat tidak boleh kosong',
        ]);

        $resid = Customer::insertGetId([
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
        ]);

        $this->user_searchable_id = $resid;
        $this->searchData();
        $this->myModalPelanggan = false;

        $this->success("Customer berhasil disimpan", "silahkan pilih pelanggan di pilihan customer");

    }

    public function saveTransaction()
    {
        Log::info("metode pembayaran: " . $this->selectedMetodePembayaran);

        $this->validate([
            'user_searchable_id' => [
                'required',
                Illuminate\Validation\Rule::notIn([0]),
            ],
            'taken_date' => ['required'],
            'taken_time' => ['required'],
            'order_date' => ['required'],
            'order_time' => ['required'],
        ], [
            'user_searchable_id' => 'Pelanggan tidak boleh kosong',
            'taken_date' => 'Tanggal Ambil tidak boleh kosong',
            'taken_time' => 'Waktu Ambil tidak boleh kosong',
            'order_date' => 'Tanggal Pesan tidak boleh kosong',
            'order_time' => 'Waktu Pesan tidak boleh kosong',
        ]);

        $transaction_state = 1;
        $transaction_state = ($this->paid != 0) ? ($this->paid < $this->grandTotalCalc ? 3 : 2) : 3;

        DB::beginTransaction();
        try {
            $order_date = date('Y-m-d', strtotime($this->order_date));
            $order_time = date('H:i:00', strtotime($this->order_time));
            $transaction_date = strtotime($order_date . ' ' . $order_time);

            Transactions::find($this->hidden_trans_id)
                ->update([
                    'customer_id' => $this->user_searchable_id,
                    'transaction_pay_type' => $this->selectedMetodePembayaran,
                    'transaction_state' => $transaction_state,
                    'change_return' => $this->change_return,
                    'grand_total' => $this->grandTotalCalc,
                    'staff_id' => Auth::user()->id,
                    'transaction_date' => date('Y-m-d H:i:s', $transaction_date),
                    'paid' => $this->paid,
                ]);
            LastTransactionNumber::insert(['transaction_id' => $this->hidden_trans_id]);


            // add payment even paid is 0 to record payments
            // if ($this->paid != 0) {
            $transaction_payment_id = TransactionPayments::insertGetId([
                'transaction_id' => $this->hidden_trans_id,
                'amount' => $this->paid,
                'method' => ($this->paid) ? $this->selectedMetodePembayaran : 0,
                'trans_status' => ($this->paid != 0) ? ($this->paid < $this->grandTotalCalc ? 'DP' : 'Lunas') : 'Hutang',
                'created_at' => date('Y-m-d H:i:s'),
                'change_return' => $this->change_return,
                'staff_id' => Auth::user()->id,
            ]);

            Log::info('tr_paye_id' . $transaction_payment_id);

            $taken_date = date('Y-m-d', strtotime($this->taken_date));
            $taken_time = date('H:i:00', strtotime($this->taken_time));
            TransactionReceipts::create([
                'transaction_payment_id' => $transaction_payment_id,
                'type' => 1,
                'issued_by' => Auth::user()->id,
                'issued_at' => date('Y-m-d H:i:s', strtotime($taken_date . $taken_time)),
            ]);
            // }

            DB::commit();
            $this->myModalProsesSelesai = true;
            $this->myModal2 = false;
            $this->transDone = true;

            $this->success("Transaksi berhasil disimpan", "simpan transaksi");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->myModal2 = true;
            $this->transDone = false;

            $this->myModalProsesSelesai = false;
            $this->error("Gagal Simpan Transaksi" . json_encode($e->getMessage()), "simpan transaksi");
        }
    }

    public function addCustomProduct()
    {
        $grandTotal = 0;
        Log::info('hidden trans_id: '. $this->hidden_trans_id);

        if ($this->hidden_trans_id == -1) {
            try {
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
                    'product_id' => null,
                    'product_qty' => $this->product_custom_qty,
                    'product_name' => $this->product_custom_name,
                    'product_price' => (Double) $this->product_custom_price,
                    'notes' => $this->product_custom_notes,
                    'product_subtotal' => (int) $this->product_custom_qty * (int) $this->product_custom_price,
                    'created_by' => Auth::user()->id,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $grandTotal = TransactionDetails::query()
                    ->where('transaction_id', $trans_id)
                    ->selectRaw("SUM(product_qty * product_price) as grand_total")
                    ->first()->grand_total;

            } catch (\Exception $e) {
                Log::debug(json_encode($e->getMessage()));
                $this->warning(json_encode($e->getMessage()) . 'Err.', position: 'toast-bottom');
                $this->warning(json_encode($e->getMessage()));
            }
        } else {
            $this->warning('----' . 'It is fake.', position: 'toast-bottom');
            Log::info("in this");

            TransactionDetails::create([
                'transaction_id' => $this->hidden_trans_id,
                'product_id' => null,
                'product_qty' => $this->product_custom_qty,
                'product_name' => $this->product_custom_name,
                'product_price' => (Double) $this->product_custom_price,
                'notes' => $this->product_custom_notes,
                'product_subtotal' => $this->product_custom_qty * (int) $this->product_custom_price,
                'created_by' => Auth::user()->id,
                'created_at' => date('Y-m-d H:i:s')
            ]);


            $grandTotal = TransactionDetails::query()
                ->where('transaction_id', $this->hidden_trans_id)
                ->selectRaw("SUM(product_qty * product_price) as grand_total")
                ->first()->grand_total;
        }
        $this->grandTotalCalc = $grandTotal;
        $this->grandTotal = number_format($grandTotal, 0, ',', '.');

        $this->myModal1 = false;
        $this->myModalCustomProduct = false;
    }

    public function addNote($id)
    {
        $det = TransactionDetails::find($id);
        if ($det != null) {
            $this->current_editing = $id;
            $this->myModalAddNotes = true;

            $this->detail_notes = $det->notes;
        }
    }

    public function saveNote()
    {
        TransactionDetails::find($this->current_editing)
            ->update([
                'notes' => $this->detail_notes
            ]);

        $this->success('Keterangan berhasil ditambahkan #' . $this->current_editing);
        $this->myModalAddNotes = false;
    }

    public function backToTransaction()
    {
        redirect('/transactions');
    }

    public function resetTransaction()
    {
        // remove current transaction
        $trans = Transactions::find($this->hidden_trans_id);
        if ($trans != null) {
            $trans->details()->delete();
            $trans->delete();
            redirect('/transactions/create');
        }
        $this->info("Transaksi Baru");
    }
}; ?>

<div>
    <div>
        <div class="lg:grid grid-cols-6 lg:gap-8 sm:gap-0">
            <?php /* <div class="col-span-3">


</div> */ ?>

            <x-modal wire:model="myModal1" title="Produk" subtitle="Pilih Produk" class="backdrop-blur">
                <x-input placeholder="Cari..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />

                <!-- TABLE  -->
                <x-card shadow>
                    <x-table show-empty-text
                        empty-text="Belum ada Record Data, Tambahkan melalui tombol tambah di atas!" with-pagination
                        :headers="$headers" :rows="$products" :sort-by="$sortBy"
                        @row-click="$wire.add($event.detail.id)">
                        @scope('actions', $product)
                        {{-- <x-button icon="o-pencil" wire:click="delete({{ $product['id'] }})"
                            wire:confirm="Are you sure?" spinner --}} {{-- class="btn-ghost btn-sm text-error" /> --}}
                        <x-button icon="o-plus" wire:click="add({{ $product['id'] }})" spinner
                            class="btn-ghost btn-sm text-error" />
                        @endscope
                    </x-table>
                </x-card>
                <x-slot:actions>
                    <x-button @click="$wire.myModalCustomProduct = true" icon="o-plus" class="btn-primary">Produk
                        Custom</x-button>
                </x-slot:actions>
            </x-modal>

            <x-modal wire:model="myModal2" title="Bayar" subtitle="Pembayaran" class="backdrop-blur">
                <x-form no-separator wire:submit="saveTransaction">
                    <div class=" text-right"><sup>Rp</sup><span class="text-6xl">{{ $grandTotal }}</span></div>

                    <x-input label="Nominal Pembayaran" wire:model="paid" wire:change="calculateChange"
                        placeholder="Nilai Pembayaran" prefix="Rp" />
                    <x-input readonly label="Kembalian" placeholder="Nilai Pembayaran" wire:model="change_return"
                        prefix="Rp" />
                    <div class="grid grid-cols-8 gap-1">
                        <div class="lg:col-span-6 col-span-4 ">
                            <x-choices label="Pelanggan" wire:model="user_searchable_id" :options="$usersSearchable"
                                placeholder="Search ..." single searchable debounce="300ms" search-function="searchData" />
                        </div>
                        <div class="lg:col-span-2 col-span-4 content-end text-right">
                            <x-button class="mt-2" label="Tambah" @click="$wire.myModalPelanggan = true" icon="o-plus"
                                class="btn-primary" />
                        </div>
                    </div>

                    <div class="grid grid-cols-8 gap-1">
                        <div class="lg:col-span-4 col-span-4 ">
                            <x-datepicker label="Tanggal Pesan" wire:model="order_date" :config="$config1" />
                        </div>
                        <div class="lg:col-span-4 col-span-4 content-end">
                            <x-datepicker label="Jam Pesan" wire:model="order_time" :config="$config2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-8 gap-1">
                        <div class="lg:col-span-4 col-span-4 ">
                            <x-datepicker label="Tanggal Ambil" wire:model="taken_date" :config="$config1" />
                        </div>
                        <div class="lg:col-span-4 col-span-4 content-end">
                            <x-datepicker label="Jam Ambil" wire:model="taken_time" :config="$config2" />
                        </div>
                    </div>

                    <x-select label="Metode Pembayaran" wire:model="selectedMetodePembayaran"
                        :options="$metodePembayaran" icon="o-bars-arrow-down" />
                    <x-textarea label="Keterangan" wire:model="keterangan" />

                    {{-- Notice we are using now the `actions` slot from `x-form`, not from modal --}}
                    <x-slot:actions>
                        <x-button label="Batal" @click="$wire.myModal2 = false" />
                        <x-button label="Bayar" class="btn-primary" spinner="save" type="submit" />
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

            <x-modal wire:model="myModalAddNotes" title="Keterangan" class="backdrop-blur" subtitle="Tambah Keterangan">
                <x-form no-separator wire:submit="saveNote">

                    <x-textarea label="Notes" wire:model="detail_notes" />

                    {{-- Notice we are using now the `actions` slot from `x-form`, not from modal --}}
                    <x-slot:actions>
                        <x-button label="Batal" @click="$wire.myModalPelanggan = false" />
                        <x-button label="Simpan" class="btn-primary" spinner="save" type="submit" />
                    </x-slot:actions>
                </x-form>
            </x-modal>

            <x-modal wire:model="myModalCustomProduct" title="Produk Custom" class="backdrop-blur"
                subtitle="Tambah Produk Custom">
                <x-form no-separator wire:submit="addCustomProduct">

                    <x-input label="Nama Produk" wire:model="product_custom_name" required />
                    <x-input type="number" min="1" label="Qty" wire:model="product_custom_qty" />
                    {{-- <x-input money prefix="Rp" label="Harga Produk" wire:model="product_custom_price" /> --}}
                    <x-input label="Harga Produk" wire:model="product_custom_price" />
                    <x-textarea label="Keterangan" wire:model="product_custom_notes" />

                    <x-slot:actions>
                        <x-button label="Batal" @click="$wire.myModalCustomProduct = false" />
                        <x-button label="Tambah" class="btn-primary" spinner="save" type="submit" />
                    </x-slot:actions>
                </x-form>
            </x-modal>

            <x-modal wire:model="myModalProsesSelesai" title="Proses Selesai" subtitle="Proses Pembayaran"
                class="backdrop-blur">
                <x-slot:actions>
                    <x-button label="Tutup & Transaksi Baru" link="/transactions/create" icon="o-plus" class="btn-error"
                        spinner />
                    <a href="/download/{{ $hidden_trans_id }}" target="_blank" rel="noopener noreferrer"
                        class="btn btn-success">Unduh</a>
                    <a href="/print/{{ $hidden_trans_id }}" target="_blank" rel="noopener noreferrer"
                        class="btn btn-primary">Cetak</a>
                </x-slot:actions>
            </x-modal>


            <div class="col-span-4">
                <x-header title="Transaksi" separator progress-indicator>
                    <x-slot:actions>
                        <div class="flex flex-wrap gap-3">
                            @if ($transDone)
                                <x-button label="Tansaksi Baru" link="/transactions/create" icon="o-plus"
                                    class="grow btn-success" spinner />
                            @else
                                <x-button wire:click="backToTransaction" label="Kembali" icon="o-arrow-left"
                                    class="btn-secondary"
                                    wire:confirm="Anda ingin kembali ke halaman transaksi dan membatalkan transaksi yang sedang berlangsung?" />
                                <x-button wire:click="resetTransaction" label="Batalkan" icon="o-x-mark"
                                    wire:confirm="Anda ingin membatalkan transaksi yang sedang berlangsung?"
                                    class="btn-error" />
                                <x-button label="Tambah Item" @click="$wire.myModal1 = true" icon="o-plus"
                                    class="btn-primary" />
                                @if ($hidden_trans_id == -1)
                                    <x-button disabled label="Bayar" @click="$wire.myModal2 = true" icon="o-paper-airplane"
                                        class="btn-success" spinner />
                                @else
                                    <x-button label="Bayar" @click="$wire.myModal2 = true" icon="o-paper-airplane"
                                        class="btn-success" spinner />
                                @endif
                            @endif
                        </div>
                    </x-slot:actions>
                </x-header>

                <div class=" text-right"><sup>Rp</sup><span class="text-6xl">{{ $grandTotal }}</span></div>
                {{-- <x-header title="" separator /> --}}

                <div class="pt-3.5">
                    <!-- TABLE  -->
                    <x-card shadow class="sm:p-0">
                        <x-table show-empty-text
                            empty-text="Belum ada Record Data, Tambahkan melalui tombol tambah di atas!"
                            :headers="$headersDetTrans" :rows="$detailTrans" :sort-by="$sortBy">
                            {{-- @row-click="$wire.addNote($event.detail.id)"> --}}
                            @scope('cell_product_name', $detTrans)
                            {!! nl2br($detTrans->product_name) !!}
                            @endscope
                            @if (!$transDone)
                                @scope('cell_product_qty', $detTrans)
                                <x-input class="w-10" wire:change="update({{ $detTrans->id }}, $event.target.value)"
                                    type="number" value="{{ $detTrans->product_qty }}" min="1" />
                                @endscope
                            @endif

                            @if (!$transDone)
                            @scope('actions', $product)
                            {{-- <x-button icon="o-pencil" wire:click="delete({{ $product['id'] }})"
                                wire:confirm="Are you sure?" spinner class="btn-ghost btn-sm text-error" /> --}}

                            {{-- <x-button icon="o-trash" wire:click="delete({{ $product['id'] }})" spinner
                                class="btn-ghost btn-sm text-error" wire:confirm="Anda ingin menghapus Item ini?" />
                            --}}
                            <x-dropdown no-x-anchor right>
                                <x-menu-item title="Ubah Keterangan" wire:click="addNote({{ $product['id'] }})"
                                    icon="o-archive-box" />
                                <x-menu-item title="Hapus" icon="o-trash" wire:click="delete({{ $product['id'] }})"
                                    spinner="delete({{ $product['id'] }})"
                                    wire:confirm="Anda ingin menghapus item ini?" />
                            </x-dropdown>
                            @endscope
                            @endif
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