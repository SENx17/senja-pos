<x-page-layout>


    <x-slot name="appBar">

        <div class="navbar-app">
            <div class="content-navbar d-flex flex-row justify-content-between">

                <div id="nav-leading" class="d-flex flex-row align-items-center">
                    <div class="navbar-title">
                        Transaksi
                    </div>
                </div>

                <div id="nav-action-button" class="d-flex flex-row align-items-center">


                    <form class="d-flex margin-left-10">
                        <input class="form-control search-bar clear" type="search"
                               placeholder="{{ __('app_locale.input.cari') }}"
                               aria-label="Search" wire:model.live.debounce.600ms="search">
                    </form>


                    <div class="dropdown margin-left-10">
                        <select class="form-select input-default"
                                id="resupplyOutlet" wire:model="selected" wire:change="selectWarehouse">
                            <option value="all" selected disabled>Semua gudang</option>
                            @if(!empty($warehouses))

                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse['id'] }}">{{ $warehouse['name'] }}</option>
                                @endforeach

                            @endif


                        </select>
                    </div>


                    <button type="btn"
                            class="btn btn-text-only-primary btn-nav margin-left-10" wire:click="create">Buat
                        permintaan stok
                    </button>


                </div>
            </div>
            <div id="title-divider"></div>
            <div id="divider"></div>
        </div>
    </x-slot>

    <div id="content-loaded">
        <x-notify::notify/>

        <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
            <input type="radio" class="btn-check" wire:model="toggle" name="btnradio" id="btnradio1" autocomplete="off"
                   value="request"
                   checked wire:change="toggleChange">
            <label class="btn btn-outline-primary" for="btnradio1">Permintaan stok</label>

            <input type="radio" class="btn-check" wire:model="toggle" name="btnradio" id="btnradio2" autocomplete="off"
                   value="stockIn" wire:change="toggleChange">
            <label class="btn btn-outline-primary" for="btnradio2">Stok masuk</label>

            <input type="radio" class="btn-check" wire:model="toggle" name="btnradio" id="btnradio3" autocomplete="off"
                   value="stockOut" wire:change="toggleChange">
            <label class="btn btn-outline-primary" for="btnradio3">Stok keluar</label>
        </div>
    </div>


</x-page-layout>