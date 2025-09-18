<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{--  Currency  --}}
    <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/robsontenorio/mary@0.44.2/libs/currency/currency.js"></script>

</head>
<body class="min-h-screen font-sans antialiased bg-base-200">

    {{-- NAVBAR mobile only --}}
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            <x-app-brand />
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden me-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-nav>

    {{-- MAIN --}}
    <x-main>
        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">

            {{-- BRAND --}}
            <x-app-brand class="px-5 pt-4" />

            {{-- MENU --}}
            <x-menu activate-by-route>

                {{-- User --}}
                @if($user = auth()->user())
                    <x-menu-separator />

                    <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="-mx-2 !-my-2 rounded">
                        <x-slot:actions>
                            <x-button icon="o-power" class="btn-circle btn-ghost btn-xs" tooltip-left="logoff" no-wire-navigate link="/logout" />
                        </x-slot:actions>
                    </x-list-item>

                    <x-menu-separator />
                @endif

                <x-menu-item title="Daftar Produk" icon="o-sparkles" link="/product" />
                
                <x-menu-sub title="Produk" icon="o-cog-6-tooth">
                    <x-menu-item title="Tambah" icon="o-plus" link="/products/create" />
                    <x-menu-item title="Kategori Produk" icon="o-bars-3" link="/product_types" />
                    <x-menu-item title="Kategori Produk" icon="o-plus" link="/product_types/create" />
                </x-menu-sub>

                <x-menu-item title="Daftar Pengeluaran" icon="o-sparkles" link="/expenses" />

                <x-menu-sub title="Transaksi" icon="o-cog-6-tooth">
                    <x-menu-item title="Daftar Transaksi" icon="o-square-3-stack-3d" link="/transactions/" />
                    <x-menu-item title="Transaksi Customer" icon="o-plus" link="/transactions/create" />
                    <x-menu-item title="Transaksi Reseller" icon="o-plus" link="/transactions/create-reseller" />
                </x-menu-sub>

                <x-menu-separator></x-menu-separator>
                <x-menu-item title="User" icon="o-user" link="/user/management" />
                
                {{-- <x-menu-sub title="Settings" icon="o-cog-6-tooth">
                    <x-menu-item title="Wifi" icon="o-wifi" link="####" />
                    <x-menu-item title="Archives" icon="o-archive-box" link="####" />
                </x-menu-sub> --}}
            </x-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{--  TOAST area --}}
    <x-toast />
</body>
</html>
