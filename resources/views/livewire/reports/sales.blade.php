<?php

use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Builder;
use App\Models\TransactionDetails;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Barryvdh\DomPDF\Facade\Pdf;


new class extends Component {
    use WithPagination;
    use Toast;

    public bool $drawwer = false;
    public $config1 = [];
    public $start_date = "";
    public $end_date = "";
    public int $select_type_id = 1;
    public int $select_status_id = 2;
    public int $select_status_selected_id = 2;
    public string $title = '';
    public $grand_total_report = 0;
    public string $type = '';
    public string $status_state = '';

    public array $sortBy = ['column' => 'transactions.created_at', 'direction' => 'asc'];

    public function config()
    {
        return [
            'locale' => 'id'
        ];
    }

    public function mount()
    {
        $this->start_date = date('Y-m-d H:i:s');
        $this->end_date = date('Y-m-d H:i:s');
        $this->select_type_id = -1;
        $this->select_status_id = 2;
        $this->select_status_selected_id = 2;
        $this->grand_total_report = number_format($this->report()->sum('product_subtotal'), 0, ',', '.');
        $this->title = date('d FY');
    }

    public function report()
    {
        $from = date('Y-m-d', strtotime($this->start_date));
        $to = date('Y-m-d 23:59:59', strtotime($this->end_date));

        $status = [
            ['id' => 2, 'name' => 'Lunas'],
            ['id' => 3, 'name' => 'Hutang'],
        ];

        $product_types = array_merge([
            [
                'id' => -1,
                'name' => 'Semua'
            ]
        ], App\Models\ProductType::all()->toArray());

        $this->type = $this->select_type_id != -1 ? $product_types[(int) $this->select_type_id]['name'] : '';
        $this->status_state = $status[((int) $this->select_status_id) - 2]['name'];

        if ($this->select_status_id == 3) {
            $this->select_type_id = -1;
            $this->select_status_selected_id = 3;
        }

        return TransactionDetails::query()
            ->selectRaw("transactions.created_at as transaction_date_simple_formatted, transaction_details.id, 
                IF(product_id is null, concat_ws('',transaction_details.product_name, ' \n<div class=\"tab\">', notes, '</div>'), concat_ws('',products.product_name, ' \n<div class=\"tab\">', notes, '</div>')) as product_name, 
                product_qty, product_price, product_subtotal, notes, transaction_state, product_types.name, 
                IF(transaction_state = 2, 'Lunas', IF(transaction_state = 3, 'Hutang', 'Baru')) as transaction_state,
                customer_type, IF(customer_id is null, reseller.name, customer.name) as customer_name
                ")
            ->leftJoin('products', 'product_id', '=', 'products.id')
            ->leftJoin('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
            ->leftJoin('product_types', 'product_types.id', '=', 'product_type_id')
            ->leftJoin('customer', 'customer.id', '=', 'customer_id')
            ->leftJoin('reseller', 'reseller.id', '=', 'reseller_id')
            ->when(
                $this->select_type_id,
                function ($q) {
                    if ($this->select_type_id != -1) {
                        $q->where('product_type_id', $this->select_type_id);
                    }
                }
            )
            ->when(
                $this->select_status_id,
                fn(Builder $q) =>
                $q->where('transaction_state', $this->select_status_id)
            )
            ->whereBetween('transactions.created_at', [$from, $to])
            ->whereNotIn('transaction_state', ['1'])
            ->orderBy(...array_values($this->sortBy))
            ;
        // ->dd();
        // ->paginate(8);
    }

    public function headers(): array
    {
        return [
            ['key' => 'transaction_date_simple_formatted', 'label' => 'Tanggal Transaksi', 'class' => 'w-64', 'sortable' => true],
            ['key' => 'product_name', 'label' => 'Nama Produk', 'class' => 'w-64', 'sortable' => true],
            // ['key' => 'buy_price', 'label' => 'Harga Beli', 'class' => 'w-20'],
            ['key' => 'product_price', 'label' => 'Harga', 'class' => 'w-20', 'sortable' => true, 'format' => ['currency', '0,.', '']],
            ['key' => 'product_qty', 'label' => 'Qty', 'sortable' => true],
            ['key' => 'product_subtotal', 'label' => 'Sub Total', 'sortable' => true, 'format' => ['currency', '0,.', '']],
            ['key' => 'name', 'label' => 'Kategori', 'sortable' => true],
            ['key' => 'customer_name', 'label' => 'Pelanggan', 'sortable' => true],
            ['key' => 'customer_type', 'label' => 'Tipe Pelanggan', 'sortable' => true],
            ['key' => 'transaction_state', 'label' => 'Status', 'sortable' => true],
        ];
    }

    public function process()
    {
        $this->title = date('Y-m-d', strtotime($this->start_date)) == date('Y-m-d', strtotime($this->end_date)) ? date('d F Y', strtotime($this->start_date)) : date('d F Y', strtotime($this->start_date)) . ' - ' . date('d F Y', strtotime($this->end_date));
        $this->report = $this->report()->paginate(10);
        $this->grand_total_report = number_format($this->report()->sum('product_subtotal'), 0, ',', '.');
    }

    public function with()
    {
        return [
            'config1' => $this->config(),
            'product_types' => array_merge([
                [
                    'id' => -1,
                    'name' => 'Semua'
                ]
            ], App\Models\ProductType::all()->toArray()),
            'status' => [
                ['id' => 2, 'name' => 'Lunas'],
                // ['id' => 3, 'name' => 'Hutang'],
            ],
            'report' => $this->report()->paginate(10),
            'headers' => $this->headers(),
            'grand_total_report' => number_format($this->report()->sum('product_subtotal'), 0, ',', '.'),
            'paid' => number_format($this->report()->groupBy('transaction_id')->sum('paid'), 0, ',', '.'),
        ];
    }

    public function changeStatus()
    {
        $this->select_status_selected_id = $this->select_status_id;
    }

    public function export()
    {
        // Export
        $report = $this->report()->get();
        $grand_total_report = number_format($this->report()->sum('product_subtotal'), 0, ',', '.');

        $pdf = Pdf::loadView("reports.sales", compact('report', 'grand_total_report'));
        return $pdf->download('report-' . date('Ymd-His') . '.pdf');

    }
}; ?>

<div>
    <x-header title="Laporan Penjualan {{ isset($product_types[(int) $select_type_id]) ? '' : '' }}" separator
        progress-indicator subtitle="{{ $title }}">
        <x-slot:middle class="!justify-end">
            {{-- <x-input placeholder="Cari..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
            --}}
        </x-slot:middle>
        <x-slot:actions>
            {{-- <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
            <x-button label="Tambah" link="/products/create" responsive icon="o-plus" class="btn-primary" /> --}}
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-16 gap-3 mb-3">
        <div class="lg:col-span-3 col-span-8">
            <x-datepicker hint="Tanggal Mulai" wire:model="start_date" :config="$config1" />
        </div>
        <div class="lg:col-span-3 col-span-8">
            <x-datepicker hint="Tanggal Selesai" wire:model="end_date" :config="$config1" />
        </div>
        <div class="lg:col-span-3 col-span-8">
            @if ($select_status_selected_id == 2)
                <x-select hint="Kategori" wire:model="select_type_id" :options="$product_types" />
            @else
                <x-select hint="Kategori" disabled wire:model="select_type_id" :options="$product_types" />
            @endif
        </div>
        <div class="lg:col-span-3 col-span-8">
            <x-select hint="Status" wire:model="select_status_id" :options="$status" />
        </div>
        <div class="lg:col-span-4 col-span-16">
            <x-button label="Proses" icon="o-paper-airplane" class="w-full btn-primary" spinner="process"
                wire:click="process" />
        </div>
        {{-- <div class="lg:col-span-2 col-span-16">
            <x-button label="Export" icon="o-arrow-down-tray" class="w-full btn-success" wire:click="export" />
        </div> --}}
    </div>

    <div class="flex flex-col gap-3">
        <div>
            <x-card shadow>
                <div class="grid grid-cols-4">
                    <div class="lg:col-span-2 col-span-4 grid grid-cols-4">
                        <div class="lg:col-span-4 col-span-2 text-3xl">
                            {{ $type }}
                        </div>
                        <div class="lg:col-span-4 col-span-2 lg:text-left text-right">
                            {{ $status_state }}
                        </div>
                    </div>
                    <div class="lg:col-span-2 col-span-4 text-right">
                        <sup>Rp</sup><span class="text-5xl">{{ $grand_total_report }}</span>
                    </div>
                </div>
            </x-card>
        </div>
        <div>
            <x-card shadow>
                <x-table show-empty-text
                    empty-text="Belum ada Record Data, Silahkan lakukan pencarian kemudian tekan tombol Proses di atas!"
                    with-pagination :headers="$headers" :rows="$report" :sort-by="$sortBy">
                    @scope('cell_product_name', $detTrans)
                    {!! nl2br($detTrans->product_name) !!}
                    @endscope
                    @scope('cell_transaction_state', $detTrans)
                    <x-badge :value="$detTrans->transaction_state"
                        class="{{ ($detTrans->transaction_state == 'Lunas') ? 'badge-primary' : 'badge-error' }} badge-soft" />
                    @endscope
                    {{-- @scope('actions', $user)
                    {{-- <x-button icon="o-pencil" wire:click="delete({{ $user['id'] }})" wire:confirm="Are you sure?"
                        spinner --}} {{-- class="btn-ghost btn-sm text-error" /> --}}
                    {{-- <x-button icon="o-trash" wire:click="delete({{ $user['id'] }})" wire:confirm="Are you sure?"
                        spinner class="btn-ghost btn-sm text-error" /> --}}
                    {{-- @endscope --}}
                </x-table>
            </x-card>
        </div>
    </div>
</div>