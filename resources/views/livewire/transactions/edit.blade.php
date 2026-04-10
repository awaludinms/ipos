<?php

use App\Models\Transactions;
use App\Models\Product;
use App\Models\TransactionDetails;
use App\Models\TempTransaction;
use App\Models\TempTransactionDetail;
use App\Models\TransactionPayments;
use App\Models\TransactionReceipts;
use App\Models\Customer;
use App\Models\Reseller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination;


new class extends Component {
    use Toast;
    use WithPagination;


    public bool $myModal1 = false;
    public bool $myModal2 = false;

    public bool $myModalCustomProduct = false;
    public bool $myModalProsesSelesai = false;
    public bool $myModalAddNotes = false;
    public bool $myModalConfirm = false;
    public string $product_custom_name = '';

    public int $product_custom_qty = 1;

    public string $product_custom_price = '';

    public string $product_custom_notes = '';

    public int $current_editing = 0;

    public ?string $detail_notes = '';

    public string $search = '';

    public bool $edit_status = false;


    public Transactions $transaction;

    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];

    public $selectedMetodePembayaran = '';

    public $grandTotal = 0;

    public $paid = 0;

    public $change_return = 0;

    public $grandTotalCalc = 0;

    public Collection $usersSearchable;

    public $paymentAmount;
    public $paymentAmountFormatted;
    public $paidAmount;

    public $taken_time, $taken_date;

    public function trans()
    {
        return Transactions::query()
            ->selectRaw("transactions.id as id, if(customer_type = 'reseller', reseller.name, customer.name) as customer_name, 
                transactions.transaction_date as transaction_date_time_formatted, grand_total, transaction_state, paid, (grand_total - paid) as credit,
                customer_type, last_transaction_numbers.id as note_id, change_return")
            ->leftJoin('customer', 'customer.id', '=', 'customer_id')
            ->leftJoin('reseller', 'reseller.id', '=', 'reseller_id')
            ->leftJoin('last_transaction_numbers', 'last_transaction_numbers.transaction_id', '=', 'transactions.id')
            ->where('transactions.id', $this->transaction->id)
            ->get();
    }

    public function headers(): array
    {
        return [
            ['key' => 'note_id', 'label' => 'Nomor Nota', 'class' => 'w-1'],
            ['key' => 'transaction_date_time_formatted', 'label' => 'Tanggal Transaksi', 'class' => 'w-64'],
            // ['key' => 'buy_price', 'label' => 'Harga Beli', 'class' => 'w-20'],
            ['key' => 'customer_name', 'label' => 'Customer', 'sortable' => false],
            ['key' => 'grand_total', 'label' => 'Total', 'sortable' => false, 'format' => ['currency', '0,.', '']],
            ['key' => 'paid', 'label' => 'Dibayar', 'sortable' => false, 'format' => ['currency', '0,.', '']],
            ['key' => 'credit', 'label' => 'Sisa Pembayaran', 'sortable' => false, 'format' => ['currency', '0,.', '']],
            ['key' => 'change_return', 'label' => 'Kembalian', 'sortable' => false, 'format' => ['currency', '0,.', '']],
            ['key' => 'customer_type', 'label' => 'Tipe Transaksi', 'sortable' => false],
            ['key' => 'transaction_state', 'label' => 'Status Transaksi', 'sortable' => false],
            // ['key' => 'actions', 'label' => 'Aksi', 'sortable' => false, 'class' => 'w-10 text-center']
        ];
    }

    public function headersProduct(): array
    {
        return [
            ['key' => 'product_name', 'label' => 'Nama Produk', 'class' => 'w-64'],
            // ['key' => 'buy_price', 'label' => 'Harga Beli', 'class' => 'w-20'],
            ['key' => 'customer_price', 'label' => 'Harga', 'sortable' => false, 'format' => ['currency', '0,.', '']],
            // ['key' => 'product_type.name', 'label' => 'Kategori', 'sortable' => false],
        ];
    }


    public function headersPayment(): array
    {
        return [
            ['key' => 'transaction_payment_date_formatted', 'label' => 'Tangal Pembayaran', 'sortable' => false],
            ['key' => 'amount', 'label' => 'Dibayar', 'sortable' => false, 'format' => ['currency', '0,.', '']],
            ['key' => 'trans_status', 'label' => 'Keterangan', 'sortable' => false],
            ['key' => 'staff.name', 'label' => 'Petugas', 'sortable' => false],
        ];
    }

    public function detailTransaction()
    {
        return TransactionDetails::query()
            ->selectRaw("transaction_details.id, IF(product_id is null, concat_ws('',transaction_details.product_name, ' \n<div class=\"tab\">', notes, '</div>'), concat_ws('',products.product_name, ' \n<div class=\"tab\">', notes, '</div>')) as product_name, product_qty, product_price, product_subtotal, notes")
            ->leftJoin('products', 'product_id', '=', 'products.id')
            ->where('transaction_id', $this->transaction->id)
            ->get();
    }

    public function detailTransactionTemp()
    {
        return TempTransactionDetail::query()
            ->selectRaw("temp_transaction_details.id, IF(product_id is null, concat_ws('',temp_transaction_details.product_name, ' \n<div class=\"tab\">', notes, '</div>'), concat_ws('',products.product_name, ' \n<div class=\"tab\">', notes, '</div>')) as product_name, product_qty, product_price, product_subtotal, notes")
            ->leftJoin('products', 'product_id', '=', 'products.id')
            ->where('temp_transaction_id', $this->transaction->id)
            ->get();
    }

    public function transactionReceipt()
    {
        return TransactionReceipts::query()
            ->selectRaw("issued_at, issued_at as taken_date, issued_at as taken_time, issued_by, IF(type =1, 'Print', 'Download') as type")
            ->whereHas('payment', function ($q) {
                $q->where('transaction_id', $this->transaction->id);
            })
            ->get();
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

    public function headersReceipt()
    {
        return [
            ['key' => 'taken_date', 'label' => 'Tangal Ambil', 'sortable' => false],
            ['key' => 'taken_time', 'label' => 'Jam Ambil', 'sortable' => false],
            ['key' => 'staff.name', 'label' => 'Petugas', 'sortable' => false],
            // ['key' => 'type', 'label' => 'Tipe', 'sortable' => false],
        ];
    }

    public function payments()
    {
        return TransactionPayments::query()
            ->with('staff')
            ->selectRaw('
        *, created_at as transaction_payment_date_formatted')
            ->where('transaction_id', $this->transaction->id)->get();
    }

    public function products()
    {
        return Product::query()->selectRaw('id, product_name, customer_price, reseller_price, product_type_id')
            ->when($this->search, fn(Builder $q) => $q->where('product_name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(5);
    }

    public function add($id)
    {

        // $this->warning($id);

        // $this->warning('----' . 'It is fake.', position: 'toast-bottom');
        $exists = TempTransactionDetail::where([
            'product_id' => $id,
            'temp_transaction_id' => $this->transaction->id,
        ])
            ->exists();

        if (!$exists) {
            TempTransactionDetail::create([
                'temp_transaction_id' => $this->transaction->id,
                'product_id' => $id,
                'product_qty' => 1,
                'product_price' => Product::find($id)->customer_price,
                'product_subtotal' => Product::find($id)->customer_price,
                'created_by' => Auth::user()->id,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } else {

            $tr = TempTransactionDetail::where([
                'product_id' => $id,
                'temp_transaction_id' => $this->transaction->id
            ])->first();

            $last_qty = $tr->product_qty;
            $subtotal = ($last_qty + 1) * Product::find($id)->customer_price;

            $tr->update([
                'product_qty' => ($last_qty + 1),
                'product_subtotal' => $subtotal,
            ]);
        }

        $grandTotal = TempTransactionDetail::query()
            ->where('temp_transaction_id', $this->transaction->id)
            ->selectRaw("SUM(product_qty * product_price) as grand_total")
            ->first()->grand_total;

        $this->grandTotalCalc = $grandTotal;
        $this->grandTotal = number_format($grandTotal, 0, ',', '.');
        $this->paymentAmount = $grandTotal - $this->transaction->paid;
        $this->paymentAmountFormatted = number_format($this->paymentAmount, 0, ',', '.');

        $this->myModal1 = false;
    }

    public function update($det_trans_id, $value)
    {
        $this->myModalAddNotes = false;
        // if ($value > 0) {
        $transdet = TempTransactionDetail::find($det_trans_id);
        $price = $transdet->product_price;
        $trans_id = $transdet->temp_transaction_id;

        $transdet->update([
            'product_qty' => abs($value),
            'product_subtotal' => abs($value) * $price,
        ]);

        $grandTotal = TempTransactionDetail::query()
            ->where('temp_transaction_id', $trans_id)
            ->selectRaw("SUM(product_qty * product_price) as grand_total")
            ->first()->grand_total;

        $this->grandTotalCalc = $grandTotal;

        $this->grandTotal = number_format($grandTotal, 0, ',', '.');
        // }
        $this->paymentAmount = $grandTotal - $this->transaction->paid;
        $this->paymentAmountFormatted = number_format($this->paymentAmount, 0, ',', '.');

    }

    public function addCustomProduct()
    {
        $grandTotal = 0;

        // $this->warning('----' . 'It is fake.', position: 'toast-bottom');

        TempTransactionDetail::create([
            'temp_transaction_id' => $this->transaction->id,
            'product_id' => null,
            'product_qty' => $this->product_custom_qty,
            'product_name' => $this->product_custom_name,
            'product_price' => (Double) $this->product_custom_price,
            'notes' => $this->product_custom_notes,
            'product_subtotal' => $this->product_custom_qty * (int) $this->product_custom_price,
            'created_by' => Auth::user()->id,
            'created_at' => date('Y-m-d H:i:s')
        ]);


        $grandTotal = TempTransactionDetail::query()
            ->where('temp_transaction_id', $this->transaction->id)
            ->selectRaw("SUM(product_qty * product_price) as grand_total")
            ->first()->grand_total;

        $this->grandTotalCalc = $grandTotal;
        $this->grandTotal = number_format($grandTotal, 0, ',', '.');
        $this->paymentAmount = $grandTotal - $this->transaction->paid;
        $this->paymentAmountFormatted = number_format($this->paymentAmount, 0, ',', '.');

        $this->myModal1 = false;
        $this->myModalCustomProduct = false;
    }

    public function addNote($id)
    {
        $det = TempTransactionDetail::find($id);
        if ($det != null) {
            $this->current_editing = $id;
            $this->myModalAddNotes = true;

            $this->detail_notes = $det->notes;
        }
    }

    public function delete($id)
    {
        $det = TempTransactionDetail::find($id);
        if ($det != null) {
            $this->current_editing = $id;
            try {
                $det->delete();
                $this->success("Produk berhasil dihapus");
            } catch (\Exception $e) {
                $this->error("Produk gagal dihapus.");
            }
        }
    }

    public function saveNote()
    {
        TempTransactionDetail::find($this->current_editing)
            ->update([
                'notes' => $this->detail_notes
            ]);

        $this->success('Keterangan berhasil ditambahkan #' . $this->current_editing);
        $this->myModalAddNotes = false;
    }

    public function editConfirmed()
    {
        // prepare for temp table
        $transaction = $this->transaction;

        // check existance of temp transaction by given id
        $exists = TempTransaction::where('id', $transaction->id)->exists();

        if ($exists) {
            TempTransactionDetail::where(
                    'temp_transaction_id',
                    $transaction->id
                )->delete();

            $temp_transaction = TempTransaction::find($transaction->id);
            
            $temp_transaction->delete();
        }

        TempTransaction::create([
            'id' => $transaction->id,
            'transaction_number' => $transaction->transaction_number,
            'customer_name' => $transaction->customer_name,
            'customer_type' => $transaction->customer_type,
            'transaction_date' => $transaction->transaction_date,
            'grand_total' => $transaction->grand_total,
            'paid' => $transaction->paid,
            'pay_status' => $transaction->pay_status,
            'change_return' => $transaction->change_return,
            'staff_name' => $transaction->staff_name,
            'staff_id' => $transaction->staff_id,
            'transaction_pay_type' => $transaction->transaction_pay_type,
            'reseller_id' => $transaction->reseller_id,
            'customer_id' => $transaction->customer_id,
            'transaction_state' => $transaction->transaction_state,
            'keterangan' => $transaction->keterangan,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $sql = "INSERT INTO temp_transaction_details SELECT * FROM transaction_details WHERE transaction_id = " . $this->transaction->id;
        DB::insert($sql);

        $this->paymentAmount = $this->grandTotal - $this->transaction->paid;
        $this->paymentAmountFormatted = number_format($this->paymentAmount, 0, ',', '.');

        $this->myModalConfirm = false;
        $this->edit_status = true;
    }

    public function removeAllChanges()
    {
        $this->edit_status = false;
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

    public function saveTransaction()
    {
        // $this->validate([
        //     'taken_date' => ['required'],
        //     'taken_time' => ['required'],
        // ], [
        //     'taken_date' => 'Tanggal Ambil tidak boleh kosong',
        //     'taken_time' => 'Waktu Ambil tidak boleh kosong',
        // ]);

        $transaction_state = ($this->paidAmount != 0) ? ($this->paidAmount < $this->paymentAmount ? 3 : 2) : 3;

        DB::beginTransaction();
        try {
            Transactions::find($this->transaction->id)
                ->update([
                    'transaction_pay_type' => $this->selectedMetodePembayaran,
                    'transaction_state' => $transaction_state,
                    'change_return' => $this->transaction->change_return + $this->change_return,
                    'updated_by' => Auth::user()->id,
                    'paid' => $this->paidAmount + $this->transaction->paid,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            $trans_payment = TransactionPayments::where('transaction_id', $this->transaction->id)->latest()->first();

            $trans_receipt = null;

            if ($trans_payment != null) {
                Log::info('trans_payment_id: '. $trans_payment->id);
                $trans_receipt = TransactionReceipts::where('transaction_payment_id', $trans_payment->id)->first(); 
            }

            // add payment
            // if ($this->paidAmount != 0) {
            $transaction_payment_id = TransactionPayments::insertGetId([
                'transaction_id' => $this->transaction->id,
                'amount' => $this->paidAmount,
                'method' => $this->selectedMetodePembayaran,
                'change_return' => $this->change_return,
                'trans_status' => ($this->paidAmount != 0) ? ($this->paidAmount < $this->paymentAmount ? 'DP' : 'Lunas') : 'Hutang',
                'created_at' => date('Y-m-d H:i:s'),
                'staff_id' => Auth::user()->id,
            ]);
            // }
            Log::info('tr_paye_id' . $transaction_payment_id);


            $prev_taken_date = ($trans_receipt != null) ? date('Y-m-d', strtotime($trans_receipt->issued_at)) : null;
            $prev_taken_time = ($trans_receipt != null) ? date('H:i', strtotime($trans_receipt->issued_at)) : null;

            $taken_date = ($this->taken_date != '') ? date('Y-m-d', strtotime($this->taken_date)) : $prev_taken_date;
            $taken_time = ($this->taken_time != '') ? date('H:i:00', strtotime($this->taken_time)) : $prev_taken_time;

            TransactionReceipts::create([
                'transaction_payment_id' => $transaction_payment_id,
                'type' => 1,
                'issued_by' => Auth::user()->id,
                'issued_at' => date('Y-m-d H:i:s', strtotime($taken_date . $taken_time)),
            ]);


            $this->myModalProsesSelesai = true;
            $this->myModal2 = false;
            $this->transDone = true;
            $this->transaction = Transactions::find($this->transaction->id);

            if ($this->edit_status) {
                // replace existing transaction detail
                Transactions::find($this->transaction->id)
                    ->update([
                        'grand_total' => $this->grandTotalCalc,
                    ]);

                try {
                    TransactionDetails::where(
                        'transaction_id',
                        $this->transaction->id
                    )->delete();
                } catch (\Exception $e) {
                    $this->error("Gagal Simpan Transaksi" . json_encode($e->getMessage()), "hapus detail transaksi");
                }

                $column = 'transaction_id, product_id, product_qty, product_price, product_subtotal, deleted_by, deleted_at, created_by, updated_by,
                    created_at, updated_at, product_name, notes';
                $column_from = 'temp_transaction_id, product_id, product_qty, product_price, product_subtotal, deleted_by, deleted_at, created_by, updated_by,
                    created_at, updated_at, product_name, notes';
                $sql = "INSERT INTO transaction_details($column) SELECT $column_from FROM temp_transaction_details WHERE temp_transaction_id = " . $this->transaction->id;
                DB::insert($sql);

                TempTransactionDetail::where(
                    'temp_transaction_id',
                    $this->transaction->id
                )->delete();

                TempTransaction::find($this->transaction->id)
                    ->delete();
            }
            $this->edit_status = false;
            DB::commit();

            $this->success("Transaksi berhasil disimpan", "simpan transaksi");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->myModal2 = true;
            $this->transDone = false;
            $this->edit_status = true;

            $this->myModalProsesSelesai = false;
            $this->error("Gagal Simpan Transaksi" . json_encode($e->getMessage()), "simpan transaksi");
        }
    }

    public function calculateChange()
    {
        $this->change_return = ($this->paidAmount - $this->paymentAmount < 0) ? 0 : $this->paidAmount - $this->paymentAmount;
    }

    public function config()
    {
        return [
            'locale' => 'id'
        ];
    }

    public function with()
    {
        return [
            'detailtransaksi' => $this->detailTransaction(),
            'tempdetailtransaksi' => $this->detailTransactionTemp(),
            'headersDetTrans' => $this->headersDetTrans(),
            'headers' => $this->headers(),
            'headersProduct' => $this->headersProduct(),
            'transactions' => $this->trans(),
            'products' => $this->products(),
            'metodePembayaran' => [
                ['id' => 1, 'name' => 'Tunai'],
                ['id' => 2, 'name' => 'Bank'],
                ['id' => 3, 'name' => 'QRIS'],
            ],
            'payments' => $this->payments(),
            'headersPayment' => $this->headersPayment(),
            'receipts' => $this->transactionReceipt(),
            'headersReceipt' => $this->headersReceipt(),
            'config1' => $this->config(),
            'config2' => [
                'enableTime' => true,
                'noCalendar' => true,
                'dateFormat' => "H:i",
                'time_24hr' => true
            ],
        ];
    }

    public function mount()
    {
        // $this->detailtransaksi = $this->detailTransaction();
        // remove existing edited
        $temp = TempTransaction::find($this->transaction->id);
        if ($temp)
            $temp->delete();
        TempTransactionDetail::where('temp_transaction_id', $this->transaction->id)->delete();
        Log::info("Remove existing transaction detail " . $this->transaction->id);
        $this->grandTotal = number_format($this->transaction->grand_total, 0, ',', '.');
        $this->paymentAmount = $this->transaction->grand_total - $this->transaction->paid;
        $this->paymentAmountFormatted = number_format($this->paymentAmount, 0, ',', '.');
    }
}; ?>

<div>
    <x-modal wire:model="myModal1" title="Produk" subtitle="Pilih Produk" class="backdrop-blur">
        <x-input placeholder="Cari..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />

        <!-- TABLE  -->
        <x-card shadow>
            <x-table show-empty-text empty-text="Belum ada Record Data, Tambahkan melalui tombol tambah di atas!"
                with-pagination :headers="$headersProduct" :rows="$products" :sort-by="$sortBy"
                @row-click="$wire.add($event.detail.id)">
                @scope('actions', $product)
                {{-- <x-button icon="o-pencil" wire:click="delete({{ $product['id'] }})" wire:confirm="Are you sure?"
                    spinner --}} {{-- class="btn-ghost btn-sm text-error" /> --}}
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

    <x-modal wire:model="myModalConfirm" title="Konfirmasi Ubah Transaksi" class="backdrop-blur">
        <!-- TABLE  -->
        <x-card shadow>
            Anda ingin mengubah transaksi ini?
        </x-card>
        <x-slot:actions>
            <x-button icon="o-check" class="btn-primary" wire:click="editConfirmed">Ya</x-button>
            <x-button icon="o-x-mark" class="btn-error" @click="$wire.myModalConfirm = false">
                Batal</x-button>
        </x-slot:actions>
    </x-modal>


    <x-modal wire:model="myModal2" title="Bayar" subtitle="Pembayaran" class="backdrop-blur">
        <x-form no-separator wire:submit="saveTransaction">
            <div class=" text-right"><sup>Rp</sup><span class="text-6xl">{{ $paymentAmountFormatted  }}</span></div>

            <x-input label="Nominal Pembayaran" wire:model="paidAmount" wire:change="calculateChange"
                placeholder="Nilai Pembayaran" prefix="Rp" />
            <x-input readonly label="Kembalian" placeholder="Nilai Pembayaran" wire:model="change_return" prefix="Rp" />

            <div class="grid grid-cols-8 gap-1">
                <div class="lg:col-span-4 col-span-4 ">
                    <x-datepicker label="Tanggal Ambil" wire:model="taken_date" :config="$config1" />
                </div>
                <div class="lg:col-span-4 col-span-4 content-end">
                    <x-datepicker label="Jam Ambil" wire:model="taken_time" :config="$config2" />
                </div>
            </div>

            <x-select label="Metode Pembayaran" wire:model="selectedMetodePembayaran" :options="$metodePembayaran"
                icon="o-bars-arrow-down" />
            <x-textarea label="Keterangan" wire:model="keterangan" />

            {{-- Notice we are using now the `actions` slot from `x-form`, not from modal --}}
            <x-slot:actions>
                <x-button label="Batal" @click="$wire.myModal2 = false" />
                <x-button label="Bayar" class="btn-primary" spinner="save" type="submit" />
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
            <x-input prefix="Rp" label="Harga Produk" wire:model="product_custom_price" />
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
            <x-button label="Tutup" @click="$wire.myModalProsesSelesai = false" icon="o-plus" class="btn-error"
                spinner />
            @if ($transaction->customer_type == 'customer')
                <a href="/download/{{ $transaction->id }}" target="_blank" rel="noopener noreferrer"
                    class="btn btn-success">Unduh</a>
                <a href="/print/{{ $transaction->id }}" target="_blank" rel="noopener noreferrer"
                    class="btn btn-primary">Cetak</a>
            @else
                <a href="/download-reseller/{{ $transaction->id }}" target="_blank" rel="noopener noreferrer"
                    class="btn btn-success">Unduh</a>
                <a href="/print-reseller/{{ $transaction->id }}" target="_blank" rel="noopener noreferrer"
                    class="btn btn-primary">Cetak</a>

            @endif
        </x-slot:actions>
    </x-modal>

    <x-header title="Transaksi" separator>
        <x-slot:actions>
            <div class="gap-3">
                @if($transaction->transaction_state == 3)
                    @if($edit_status)

                    @else
                        <x-button label="Kembali" link="/transactions" icon="o-arrow-left" class="btn-secondary" />

                    @endif

                @else
                    <x-button label="Kembali" link="/transactions" icon="o-arrow-left" class="btn-secondary" />

                @endif
            </div>
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card shadow>
        <x-table show-empty-text empty-text="Belum ada Record Data, Tambahkan melalui tombol tambah di atas!"
            :headers="$headers" :rows="$transactions" :sort-by="$sortBy">
            @scope('cell_transaction_state', $detTrans)
            <x-badge :value="$detTrans->transaction_state == 2 ? 'Lunas' : 'Hutang'"
                class="{{ ($detTrans->transaction_state == 2) ? 'badge-primary' : 'badge-error' }} badge-soft" />
            @endscope
        </x-table>
    </x-card>

    <x-header class="mt-6" title="Pembayaran" separator>
        <x-slot:actions>
            <div class="gap-3">
                @if($transaction->transaction_state == 3)
                    @if($edit_status)
                        <x-button disabled label="Bayar" @click="$wire.myModal2 = true" icon="o-paper-airplane"
                            class="btn-success" spinner />
                    @else
                        <x-button label="Bayar" @click="$wire.myModal2 = true" icon="o-paper-airplane" class="btn-success"
                            spinner />
                    @endif
                @endif
            </div>
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card shadow>
        <x-table show-empty-text empty-text="Belum ada Record Data, Tambahkan melalui tombol Bayar diatas!"
            :headers="$headersPayment" :rows="$payments" :sort-by="$sortBy">
            @scope('cell_transaction_state', $detTrans)
            <x-badge :value="$detTrans->transaction_state == 2 ? 'Lunas' : 'Hutang'"
                class="{{ ($detTrans->transaction_state == 2) ? 'badge-primary' : 'badge-error' }} badge-soft" />
            @endscope
        </x-table>
    </x-card>

    <x-header class="mt-6" title="Struk" separator>
        <x-slot:actions>
            <div class="gap-3">
                @if($edit_status)
                    <a disabled href="/download/{{ $transaction->id }}" target="_blank" rel="noopener noreferrer"
                        class="btn btn-secondary">Unduh Struk</a>
                    <a disabled href="/print/{{ $transaction->id }}" target="_blank" rel="noopener noreferrer"
                        class="btn btn-primary">Cetak Struk</a>

                @else
                    @if ($transaction->customer_type == 'customer')
                        <a href="/download/{{ $transaction->id }}" target="_blank" rel="noopener noreferrer"
                            class="btn btn-secondary">Unduh Struk</a>
                        <a href="/print/{{ $transaction->id }}" target="_blank" rel="noopener noreferrer"
                            class="btn btn-primary">Cetak Struk</a>
                    @else
                        <a href="/download-reseller/{{ $transaction->id }}" target="_blank" rel="noopener noreferrer"
                            class="btn btn-secondary">Unduh Struk</a>
                        <a href="/print-reseller/{{ $transaction->id }}" target="_blank" rel="noopener noreferrer"
                            class="btn btn-primary">Cetak Struk</a>

                    @endif
                @endif
            </div>
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card shadow>
        <x-table show-empty-text empty-text="Belum ada Record Data, Tambahkan melalui tombol Bayar diatas!"
            :headers="$headersReceipt" :rows="$receipts" :sort-by="$sortBy">
            @scope('cell_taken_date', $receipt)
                {{ $receipt->issued_at == '1970-01-01 00:00:00' ? 'Idem' : $receipt->taken_date }}
            @endscope
            @scope('cell_taken_time', $receipt)
                {{ $receipt->issued_at == '1970-01-01 00:00:00' ? 'Idem' : $receipt->taken_time }}
            @endscope
        </x-table>
    </x-card>

    <x-header class="mt-6" title="Detail Transaksi" separator progress-indicator>
        <x-slot:actions>
            <div class="gap-3">
                @if($transaction->transaction_state == 3)
                    @if($edit_status)
                        <x-button label="Batal Ubah Detail Transaksi"
                            wire:confirm="Anda ingin membatalkan perubahan pada transaksi ini?" wire:click="removeAllChanges"
                            icon="o-x-mark" class="btn-error" />
                        <x-button label="Tambah Item" @click="$wire.myModal1 = true" icon="o-plus" class="btn-primary" />
                        <x-button label="Simpan & Bayar" @click="$wire.myModal2 = true" icon="o-paper-airplane"
                            class="btn-success" spinner />
                    @else
                        <x-button label="Ubah Daftar Item" @click="$wire.myModalConfirm = true" icon="o-pencil"
                            class="btn-primary" />
                        <x-button disabled label="Tambah Item" @click="$wire.myModal1 = true" icon="o-plus"
                            class="btn-primary" />
                    @endif
                @endif
            </div>
        </x-slot:actions>
    </x-header>


    <div class="my-6 text-right"><sup>Rp</sup><span class="text-6xl">{{ $grandTotal }}</span></div>

    @if($edit_status)
        <!-- TABLE  -->

        <x-card shadow class="sm:p-0">
            <x-table show-empty-text empty-text="Belum ada Record Data, Tambahkan melalui tombol tambah di atas!"
                :headers="$headersDetTrans" :rows="$tempdetailtransaksi" :sort-by="$sortBy">
                {{-- @row-click="$wire.addNote($event.detail.id)"> --}}
                @scope('cell_product_name', $detTrans)
                {!! nl2br($detTrans->product_name) !!}
                @endscope
                @scope('cell_product_qty', $detTrans)
                <x-input class="w-10" wire:change="update({{ $detTrans->id }}, $event.target.value)" type="number"
                    value="{{ $detTrans->product_qty }}" min="1" />
                @endscope

                @scope('actions', $product)
                {{-- <x-button icon="o-pencil" wire:click="delete({{ $product['id'] }})" wire:confirm="Are you sure?"
                    spinner class="btn-ghost btn-sm text-error" />

                {{-- <x-button icon="o-trash" wire:click="delete({{ $product['id'] }})" spinner
                    class="btn-ghost btn-sm text-error" wire:confirm="Anda ingin menghapus Item ini?" />
                --}}
                <x-dropdown no-x-anchor right>
                    <x-menu-item title="Ubah Keterangan" wire:click="addNote({{ $product['id'] }})" icon="o-archive-box" />
                    <x-menu-item title="Hapus" icon="o-trash" wire:click="delete({{ $product['id'] }})"
                        wire:confirm="Anda ingin menghapus item ini?" accesskey="" spinner="delete({{ $product['id'] }})" />
                </x-dropdown>
                @endscope
            </x-table>
        </x-card>
    @else
        <!-- TABLE  -->
        <x-card shadow class="sm:p-0">
            <x-table show-empty-text empty-text="Belum ada Record Data, Tambahkan melalui tombol tambah di atas!"
                :headers="$headersDetTrans" :rows="$detailtransaksi" :sort-by="$sortBy">
                {{-- @row-click="$wire.addNote($event.detail.id)"> --}}
                @scope('cell_product_name', $detTrans)
                {!! nl2br($detTrans->product_name) !!}
                @endscope
                {{-- @scope('cell_product_qty', $detTrans)
                <x-input class="w-10" wire:change="update({{ $detTrans->id }}, $event.target.value)" type="number"
                    value="{{ $detTrans->product_qty }}" min="1" />
                @endscope --}}

                {{-- @scope('actions', $product) --}}
                {{-- <x-button icon="o-pencil" wire:click="delete({{ $product['id'] }})" wire:confirm="Are you sure?"
                    spinner class="btn-ghost btn-sm text-error" /> --}}

                {{-- <x-button icon="o-trash" wire:click="delete({{ $product['id'] }})" spinner
                    class="btn-ghost btn-sm text-error" wire:confirm="Anda ingin menghapus Item ini?" />
                --}}
                {{-- <x-dropdown no-x-anchor right>
                    <x-menu-item title="Ubah Keterangan" wire:click="addNote({{ $product['id'] }})" icon="o-archive-box" />
                    <x-menu-item title="Hapus" icon="o-trash" wire:click="delete({{ $product['id'] }})"
                        wire:confirm="Anda ingin menghapus item ini?" accesskey="" spinner="delete({{ $product['id'] }})" />
                </x-dropdown>
                @endscope --}}
            </x-table>
        </x-card>
    @endif
</div>