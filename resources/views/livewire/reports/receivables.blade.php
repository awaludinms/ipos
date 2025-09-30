<?php

use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Transactions;
use App\Models\Reseller;
use App\Models\Customer;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new class extends Component {
    use WithPagination;
    use Toast;

    public string $search = '';
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
        if (Auth::user()->role_id != 4) {
            redirect('/transactions/');
        }

        $this->start_date = date('Y-m-d H:i:s');
        $this->end_date = date('Y-m-d H:i:s');
        $this->select_type_id = -1;
        $this->select_status_id = 2;
        $this->select_status_selected_id = 2;
        $this->grand_total_report = number_format($this->receiveables()->grand_total, 0, ',', '.');
        $this->title = date('d FY');
    }

    public function report()
    {
        $resellers = Reseller::query()
            ->whereHas('transactions')
            ->withSum([
                'transactions as total_amount' => function ($q) {
                    $q->where('transaction_state', 3);
                }], 
                'grand_total',)
            ->withSum(['transactions as total_paid' => function($q) {
                $q->where('transaction_state', 3);
            }], 'paid')
            ->selectRaw("'reseller' as tipe");
            // ->get();

        $customer = Customer::query()
            ->whereHas('transactions')
            ->withSum([
                'transactions as total_amount' => function ($q) {
                    $q->where('transaction_state', 3);
                }], 
                'grand_total',)
                ->withSum(['transactions as total_paid' => function($q) {
                $q->where('transaction_state', 3);
            }], 'paid')
            ->selectRaw("'pelanggan' as tipe")
            ->union($resellers);   

        return DB::table($customer, 'c')
                ->selectRaw('*, (c.total_amount - c.total_paid) as hutang')
                ->when($this->search, function($q) {
                    $q->where('name', 'like', "%$this->search%");
                });
    }

    public function receiveables()
    {
        return Transactions::where('transaction_state', 3)
            ->selectRaw('SUM(grand_total) as grand_total, SUM(paid) as total_paid, SUM(grand_total - paid) as receivables')
            ->first();
    }

    public function _report()
    {
        $from = date('Y-m-d', strtotime($this->start_date));
        $to = date('Y-m-d', strtotime($this->end_date));

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

        return Transactions::query()
            ->selectRaw("transactions.created_at as transaction_date_simple_formatted, SUM(paid) as paid, SUM(grand_total) as grand_total,
                IF(transaction_state = 2, 'Lunas', IF(transaction_state = 3, 'Hutang', 'Baru')) as transaction_state,
                customer_type, IF(customer_id is null, reseller.name, customer.name) as customer_name
                ")
            ->leftJoin('customer', 'customer.id', '=', 'customer_id')
            ->leftJoin('reseller', 'reseller.id', '=', 'reseller_id')
            
            ->where('transaction_state', 3)
            ->orderBy(...array_values($this->sortBy))
            ->groupBy(['customer_type', 'reseller_id','reseller.name','customer_id', 'customer.name','transactions.created_at', 'transactions.transaction_state', 'paid', 'grand_total']);
        ;
    }


    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name Pelanggan', 'sortable' => true],
            ['key' => 'address', 'label' => 'Alamat', 'sortable' => true],
            ['key' => 'tipe', 'label' => 'Tipe', 'sortable' => true],
            
            ['key' => 'total_amount', 'label' => 'Total Transaksi', 'class' => 'text-right', 'sortable' => true, 'format' => ['currency', '0,.', '']],
            ['key' => 'total_paid', 'label' => 'DP', 'sortable' => true, 'class' => 'text-right', 'format' => ['currency', '0,.', '']],
            ['key' => 'hutang', 'label' => 'Hutang', 'sortable' => true, 'class' => 'text-right', 'format' => ['currency', '0,.', '']],
            
            // ['key' => 'transaction_state', 'label' => 'Status', 'sortable' => true],
        ];
    }

    public function process()
    {
        $this->title = ($this->start_date == $this->end_date) ? date('d F Y', strtotime($this->start_date)) : date('d F Y', strtotime($this->start_date)) . ' - ' . date('d F Y', strtotime($this->end_date));
        $this->report = $this->report()->paginate(10);
        // $this->grand_total_report = number_format($this->report()->sum('grand_total'), 0, ',', '.');
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
                ['id' => 3, 'name' => 'Hutang'],
            ],
            'report' => $this->report()->paginate(10),
            'headers' => $this->headers(),
            'grand_total_report' => number_format($this->receiveables()->grand_total, 0, ',', '.'),
            'paid' => number_format($this->receiveables()->total_paid, 0, ',', '.'),
            'grand_total' => $this->receiveables()->grand_total,
            'paid_total' => $this->receiveables()->total_paid,
        ];
    }

    public function changeStatus()
    {
        $this->select_status_selected_id = $this->select_status_id;
    }

    public function export()
    {
        // Export
    }
}; ?>

<div>
    <x-header title="Laporan Piutang {{ isset($product_types[(int) $select_type_id]) ? '' : '' }}" separator
        progress-indicator subtitle="{{ $title }}">
        <x-slot:middle class="!justify-end">
            {{-- <x-input placeholder="Cari..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" /> --}}
        </x-slot:middle>
        <x-slot:actions>
            {{-- <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
            <x-button label="Tambah" link="/products/create" responsive icon="o-plus" class="btn-primary" /> --}}
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-16 gap-3 mb-3">
        {{-- <div class="lg:col-span-3 col-span-8">
            <x-datepicker hint="Tanggal Mulai" wire:model="start_date" :config="$config1" />
        </div>
        <div class="lg:col-span-3 col-span-8">
            <x-datepicker hint="Tanggal Selesai" wire:model="end_date" :config="$config1" />
        </div> --}}
        {{-- <div class="lg:col-span-3 col-span-8">
            @if ($select_status_selected_id == 2)
                <x-select hint="Kategori" wire:model="select_type_id" :options="$product_types" />
            @else
                <x-select hint="Kategori" disabled wire:model="select_type_id" :options="$product_types" />
            @endif
        </div>
        <div class="lg:col-span-3 col-span-8">
            <x-select hint="Status" wire:model="select_status_id" :options="$status" />
        </div> --}}
        {{-- <div class="lg:col-span-2 col-span-16">
            <x-button label="Proses" icon="o-paper-airplane" class="w-full btn-primary" spinner="process"
                wire:click="process" />
        </div> --}}
        {{-- <div class="lg:col-span-2 col-span-16">
            <x-button label="Export" icon="o-arrow-down-tray" class="w-full btn-success" wire:click="export" />
        </div> --}}
    </div>

    <div class="flex flex-col gap-3">
        <div>
            <x-card shadow>
                <div class="grid grid-cols-4">
                    {{-- <div class="lg:col-span-2 col-span-4 grid grid-cols-4">
                        <div class="lg:col-span-4 col-span-2 text-3xl">
                            {{ $type }}
                        </div>
                        <div class="lg:col-span-4 col-span-2 lg:text-left text-right">
                            {{ $status_state }}
                        </div>
                    </div> --}}
                    <div class="lg:col-span-4 col-span-4 text-right">
                        <sup>Piutang:</sup><div><sup>Rp</sup><span  class="text-5xl">{{ number_format($grand_total - $paid_total) }}</span></div>
                    </div>
                    <div class="lg:col-span-4 col-span-4 text-right">
                        <sup>Total Transaksi:</sup><div><sup>Rp</sup><span  class="text-3xl">{{ $grand_total_report }}</span></div>
                    </div>
                    <div class="lg:col-span-4 col-span-4 text-right">
                        <sup>Dibayar (DP):</sup><div><sup>Rp</sup><span class="text-4xl">{{ $paid }}</span></div>
                    </div>
                </div>
            </x-card>
        </div>
        <div class="grid grid-cols-6">
            <div class="col-span-3"></div>
            <div class="lg:col-span-3 col-span-6">
                <x-input placeholder="Cari..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
            </div>
        </div>
        <div>
            <x-card shadow>
                <x-table show-empty-text
                    empty-text="Belum ada Record Data, Silahkan lakukan pencarian kemudian tekan tombol Proses di atas!"
                    with-pagination :headers="$headers" :rows="$report" :sort-by="$sortBy">
                    
                    @scope('cell_tipe', $customer)
                    <x-badge :value="$customer->tipe"
                        class="{{ ($customer->tipe == 'pelanggan') ? 'badge-primary' : 'badge-error' }} badge-soft" />
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