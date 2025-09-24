<?php

use App\Models\User;
use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new class extends Component {
    use WithPagination;
    use Toast;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];

    public bool $myModalUser = false;

    public string $email_user = '';

    public string $name = '';

    public string $password_user = '';

    public string $password_user_confirmation = '';

    public int $user_id = -1;

    public function save()
    {
        if (Auth::user()->role_id == 4) { # (4) admin
            if ($this->user_id == -1) {
                $this->validate([
                    'name' => 'required',
                    'email_user' => 'required|email|unique:users,email',
                    'password_user' => 'required|min:8|confirmed',
                    'password_user_confirmation' => 'required'
                ]);

                User::create([
                    'name' => $this->name,
                    'password' => Hash::make($this->password_user),
                    'email' => $this->email_user,
                ]);
                $this->user_id = -1;

                $this->success("User berhasil ditambahkan", 'penambahan user');

            } else {
                if (trim($this->password_user_confirmation) == '') {

                    $this->validate([
                        'name' => 'required',
                        'email_user' => 'required|unique:users,email,' . $this->user_id
                    ]);
                } else {
                    $this->validate([
                        'name' => 'required',
                        'email_user' => 'required|email|unique:users,email',
                        'password_user' => 'required|min:8|confirmed',
                        'password_user_confirmation' => 'required'
                    ]);
                }

                User::find($this->user_id)->update([
                    'name' => $this->name,
                    'email' => $this->email_user,
                ]);

                $this->user_id = -1;

                $this->success("User berhasil diubah", 'perubahan user');
            }
        }

        $this->myModalUser = false;
    }

    public function edit(int $id)
    {
        $this->myModalUser = true;
        $user = User::find($id);
        if ($user != null) {
            $this->name = $user->name;
            $this->email_user = $user->email;
            $this->password_user_confirmation = '';
            $this->user_id = $id;
        } else {
            $this->myModalUser = false;
        }
    }

    public function delete($id): void
    {
        // $this->success('-->' . $id . '<--', '');
        User::find($id)->delete();
        $this->success('User berhasil dihapus');

    }

    public function users()
    {
        return User::where('id', '!=', 1)
            ->when($this->search, function(Builder $q) {
                $q->where('email', 'like', "%$this->search%");
                $q->orWhere('name', 'like', "%$this->search%");
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    public function headers()
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Nama', 'class' => 'w-64'],
            // ['key' => 'buy_price', 'label' => 'Harga Beli', 'class' => 'w-20'],
            ['key' => 'email', 'label' => 'Email', 'sortable' => false],
        ];
    }

    public function mount()
    {
        if (Auth::user()->role_id != 4) {
            $this->redirect('/transactions/create');
        }
    }


    public function with()
    {
        return [
            'headers' => $this->headers(),
            'users' => $this->users()
        ];
    }
}; ?>

<div>
    <x-modal wire:model="myModalUser" title="User" class="backdrop-blur"
        subtitle="{{ $this->user_id != -1 ? 'Ubah User' : 'Tambah User' }}">
        <x-form no-separator wire:submit="save">

            <x-input label="Nama User" wire:model="name" icon="o-user" />
            <x-input type="email" label="Email" wire:model="email_user" icon="o-user" />
            {{-- @if($this->user_id == -1) --}}
            <x-input type="password" label="Password" wire:model="password_user" icon="o-key" />
            <x-input type="password" label="Konfirmasi Password" wire:model="password_user_confirmation" icon="o-key" />
            {{-- @endif --}}
            {{-- Notice we are using now the `actions` slot from `x-form`, not from modal --}}
            <x-slot:actions>
                <x-button label="Batal" @click="$wire.myModalUser = false" />
                <x-button label="Simpan" class="btn-primary" spinner="save" type="submit" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    <x-header title="User" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Cari..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
            <x-button label="Tambah" @click="$wire.user_id=-1;$wire.myModalUser = true" responsive icon="o-plus"
                class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card shadow>
        <x-table show-empty-text empty-text="Belum ada Record Data, Tambahkan melalui tombol tambah di atas!"  with-pagination :headers="$headers" :rows="$users" :sort-by="$sortBy"
            @row-click="$wire.edit($event.detail.id)">
            @scope('actions', $user)
            {{-- <x-button icon="o-pencil" wire:click="delete({{ $user['id'] }})" wire:confirm="Are you sure?" spinner
                --}} {{-- class="btn-ghost btn-sm text-error" /> --}}
            <x-button icon="o-trash" wire:click="delete({{ $user['id'] }})" wire:confirm="Are you sure?" spinner
                class="btn-ghost btn-sm text-error" />
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <x-input placeholder="Cari..." wire:model.live.debounce="search" icon="o-magnifying-glass"
            @keydown.enter="$wire.drawer = false" />

        <x-slot:actions>
            <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner />
            <x-button label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>
    <input type="hidden" value="{{ $user_id }}">
</div>