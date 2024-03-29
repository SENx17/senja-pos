@php use Carbon\Carbon;use Illuminate\Support\Facades\Log; @endphp
<x-page-layout>

    <x-slot name="appBar">
        <div class="navbar-app">
            <div class="content-navbar d-flex flex-row justify-content-between">

                @if($status == 'Baru')

                    <div id="nav-leading" class="d-flex flex-row align-items-center">
                        <div class="navbar-title">
                            Detail produksi
                        </div>
                    </div>

                    <div id="nav-action-button" class="d-flex flex-row align-items-center">
                        <div class="dropdown margin-left-10">
                            <button type="btn"
                                    class="btn btn-text-only-danger btn-nav margin-left-10"
                                    @click="$dispatch('cancel-edit-warehouse')">
                                Batal
                            </button>
                        </div>


                        <button type="btn"
                                class="btn btn-text-only-primary btn-nav margin-left-10"
                                wire:click="acceptAndNext"
                        >Terima dan lanjutkan
                        </button>

                    </div>

                @elseif($status == 'Produksi diterima')
                    <div id="nav-leading" class="d-flex flex-row align-items-center">
                        <div class="navbar-title">
                            Komponen resep
                        </div>
                    </div>

                    <div id="nav-action-button" class="d-flex flex-row align-items-center">
                        <div class="dropdown margin-left-10">
                            <button type="btn"
                                    class="btn btn-text-only-danger btn-nav margin-left-10">
                                Batal
                            </button>
                        </div>


                        <button type="btn"
                                class="btn btn-text-only-primary btn-nav margin-left-10"
                                wire:click="saveRequest"
                                wire:confirm="Anda akan menyimpan permintaan bahan sebelum mengirim permintaan ini ke gudang. Anda yakin untuk membuatnya ?"
                        >Simpan permintaan
                        </button>

                    </div>

                @elseif($status == 'Komponen produksi disimpan')
                    <div id="nav-leading" class="d-flex flex-row align-items-center">
                        <div class="navbar-title">
                            Komponen resep
                        </div>
                    </div>

                    <div id="nav-action-button" class="d-flex flex-row align-items-center">
                        <div class="dropdown margin-left-10">
                            <button type="btn"
                                    class="btn btn-text-only-danger btn-nav margin-left-10">
                                Batal
                            </button>
                        </div>

                        <div class="dropdown margin-left-10">
                            <button type="btn"
                                    class="btn btn-text-only-secondary btn-nav margin-left-10">Edit
                            </button>
                        </div>


                        <button type="btn"
                                class="btn btn-text-only-primary btn-nav margin-left-10"
                                wire:click="requestMaterialToWarehouse"
                                wire:confirm="Anda akan membuat permintaan bahan dan mengirim permintaan ini ke gudang. Anda yakin untuk membuatnya ?"
                        >Kirim permintaan
                        </button>

                    </div>

                @elseif($status == 'Membuat permintaan bahan' || $status == 'Bahan dikirim')
                    <div id="nav-leading" class="d-flex flex-row align-items-center">
                        <div class="navbar-title">
                            Komponen resep
                        </div>
                    </div>

                    <div id="nav-action-button" class="d-flex flex-row align-items-center">
                        <button type="btn"
                                class="btn btn-text-only-primary btn-nav margin-left-10"
                                {{ isset($production->requestStock->requestStockHistory) && $production->requestStock->requestStockHistory()->latest()->first()->status != 'Bahan dikirim' ? 'disabled' : '' }}
                                wire:click="validateAndAccept"
                                wire:confirm="Anda memvalidasi bahan yang diterima Anda yakin sudah melakukan pengecekan bahan yang diterima ?"
                        >Validasi dan terima
                        </button>

                    </div>

                @elseif($status == 'Bahan diterima')
                    <div id="nav-leading" class="d-flex flex-row align-items-center">
                        <div class="navbar-title">
                            Komponen resep
                        </div>
                    </div>

                    <div id="nav-action-button" class="d-flex flex-row align-items-center">

                        <button type="btn"
                                class="btn btn-text-only-primary btn-nav margin-left-10"
                                wire:click="finishProduction"
                                wire:confirm="Konfirmasi produksi telah selesai Anda akan membuat laporan bahwa produksi telah selesai."
                        >Produksi selesai
                        </button>

                    </div>

                @elseif($status == 'Produksi selesai')

                    <div id="nav-leading" class="d-flex flex-row align-items-center">
                        <div class="navbar-title">
                            Pengiriman produksi
                        </div>
                    </div>

                    <div id="nav-action-button" class="d-flex flex-row align-items-center">

                        <button type="btn"
                                class="btn btn-text-only-primary btn-nav margin-left-10"
                                wire:click="validateItemRemaining"
                                wire:confirm="Anda memvalidasi pengiriman dan sisa bahan produksi Anda yakin dan akan membuat pengiriman ?"
                        >Proses pengiriman
                        </button>

                    </div>
                @elseif($status == 'Menunggu pengiriman')
                    <div id="nav-leading" class="d-flex flex-row align-items-center">
                        <div class="navbar-title">
                            Pengiriman produksi
                        </div>
                    </div>

                    <div id="nav-action-button" class="d-flex flex-row align-items-center">

                        <button type="btn"
                                class="btn btn-text-only-primary btn-nav margin-left-10"
                                wire:click="sendItem"
                                wire:confirm="Anda yakin akan mengirim hasil produksi?"
                        >Kirim
                        </button>

                    </div>

                @elseif($status == 'Selesai produksi')
                    <div id="nav-leading" class="d-flex flex-row align-items-center">
                        <div class="navbar-title">
                            Hasil produksi
                        </div>
                    </div>
                @endif
            </div>
            <div id="title-divider"></div>
            <div id="divider"></div>
        </div>
    </x-slot>

    <div id="content-loaded">
        <x-notify::notify/>
        <div class="row">

            {{-- PRODUKSI BARU --}}
            @if($status == 'Baru')
                <div class="col-sm-5 offset-1">
                    {{-- KODE REFERENSI --}}
                    <div>
                        <p class="subtitle-3-regular">Kode referensi</p>
                        <div id="divider" class="margin-top-6"></div>
                        <p class="margin-top-6 subtitle-3-medium">{{ $requestStock->code }}</p>
                    </div>


                    <div class="margin-top-24">
                        <p class="subtitle-3-regular">Tanggal</p>
                        <div id="divider" class="margin-top-6"></div>
                        <p class="margin-top-6 subtitle-3-medium">
                            {{ Carbon::createFromFormat('Y-m-d H:i:s', $requestStock->created_at)->locale('id_ID')->isoFormat('D MMMM Y') }}
                        </p>
                    </div>

                    <div class="margin-top-24">
                        <p class="subtitle-3-regular">Item produksi</p>
                        <div id="divider" class="margin-top-6"></div>
                        <table id="" class="table borderless table-hover margin-top-6">
                            <thead class="table-head-color">
                            <tr>
                                <th scope="col">Item</th>
                                <th scope="col">Jumlah</th>
                                <th scope="col">Unit</th>
                            </tr>
                            </thead>
                            <tbody>

                            {{-- Gunakan eager loading untuk mengambil item terkait secara efisien --}}
                            @php
                                $requestStock->load('requestStockDetail.item');
                            @endphp
                            @foreach ($requestStock->requestStockDetail as $requestDetail)
                                <tr class="items-table-head-color" id="po1" style="cursor: pointer">
                                    <td>{{ $requestDetail->item->name }}</td>
                                    <td>{{ $requestDetail->qty }}</td>
                                    <td>{{ $requestDetail->item->unit->name }}</td>
                                </tr>

                            @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- PRODUKSI DITERIMA --}}
            @elseif($status == 'Produksi diterima')
                <div class="col-sm-5 offset-1">
                    <div class="container-input-default  margin-top-16">
                        <label for="warehouseInput"
                               class="form-label input-label">Kode produksi</label>

                        <div id="divider" class="margin-symmetric-vertical-6"></div>

                        <input type="name" class="form-control input-default"
                               id="warehouseInput"
                               value="{{ $production->code }}" disabled>
                    </div>
                </div>

                <div class="col-sm-9 offset-1 margin-top-16 set-height-item-request">

                    @if($errors->has('components.*.recipe'))
                        <span class="text-xs text-red-600">
                            {{ $errors->first('components.*.recipe') }}
                        </span>
                    @endif

                    @if(isset($components) && !empty($components))

                        <div class="accordion" id="accordionExample" wire:ignore>
                            @foreach($components as $subKey => $component)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne{{ $component['item']['id'] }}">
                                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#accordion{{ $component['item']['id'] }}"
                                                aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                                aria-controls="accordion{{ $component['item']['id'] }}">
                                            {{ $component['item']['name'] }}
                                        </button>
                                    </h2>
                                    <div id="accordion{{ $component['item']['id'] }}"
                                         class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                         aria-labelledby="headingOne{{ $component['item']['id'] }}"
                                         data-bs-parent="#accordionExample"> {{-- Use the same data-bs-parent for all accordion items --}}
                                        <div class="accordion-body">
                                            <table class="table-component table table-hover margin-top-16"
                                                   id="tableItemRequest"
                                            >
                                                <thead class="sticky-topphp">
                                                <tr>
                                                    <th>
                                                        <input class="form-check-input" type="checkbox" value=""
                                                               id="selectAllCheckbox"
                                                               wire:model="selectAll">
                                                    </th>
                                                    <th>Item</th>
                                                    <th>Jumlah Resep</th>
                                                    <th>Unit</th>
                                                    <th>Jumlah Diminta</th>
                                                    <th>Unit</th>
                                                </tr>
                                                </thead>

                                                <tbody>

                                                {{--  Looping isi resep dari item yang diminta--}}

                                                @if(isset($component['recipe']) && !empty($component['recipe']))

                                                    @foreach($component['recipe'] as $key =>  $recipe)
                                                        <tr wire:key="{{ $loop->iteration }}">
                                                            <td>
                                                                <input class="form-check-input" type="checkbox"
                                                                       id="checkbox_{{ $loop->iteration }}"
                                                                       wire:model="components.{{ $subKey }}.recipe.{{ $key }}.isChecked">
                                                            </td>
                                                            <td>{{ $recipe['item_component_name'] }}</td>
                                                            <td>{{ $recipe['item_component_usage'] }}</td>
                                                            <td>{{ $recipe['item_component_unit'] }}</td>
                                                            <td>
                                                                <input x-mask:dynamic="$money($input, '.')"
                                                                       type="text"
                                                                       class="form-control input-default"

                                                                       wire:model="components.{{ $subKey }}.recipe.{{ $key }}.item_component_usage">
                                                            </td>
                                                            <td>{{ $recipe['item_component_unit'] }}</td>
                                                        </tr>
                                                    @endforeach

                                                @endif


                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    @endif


                </div>

                {{-- PRODUKSI DISIMPAN --}}
            @elseif($status == 'Komponen produksi disimpan')
                <div class="col-sm-5 offset-1">
                    <div class="container-input-default  margin-top-16">
                        <label for="warehouseInput"
                               class="form-label input-label">Kode produksi</label>

                        <div id="divider" class="margin-symmetric-vertical-6"></div>

                        <input type="name" class="form-control input-default"
                               id="warehouseInput"
                               value="{{ $production->code }}" disabled>
                    </div>

                    @if(isset($productionComponentSave) && !empty($productionComponentSave))

                        <div class="accordion margin-top-24" id="accordionExample" wire:ignore>
                            @foreach($productionComponentSave as $subKey => $component)

                                <div class="accordion-item" wire:key="{{ $loop->iteration}}">
                                    <h2 class="accordion-header" id="headingOne{{ $component['targetItem']['id'] }}">
                                        <button class="accordion-button"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#accordion{{ $component['targetItem']['id'] }}"
                                                aria-expanded="true"
                                                aria-controls="accordion{{ $component['targetItem']['id'] }}">
                                            {{ $component['targetItem']['name'] }}
                                        </button>
                                    </h2>
                                    <div id="accordion{{ $component['targetItem']['id']}}"
                                         class="accordion-collapse collapse show"
                                         aria-labelledby="headingOne{{ $component['targetItem']['id'] }}"
                                    >
                                        <div class="accordion-body">
                                            <table class="table-component table table-hover margin-top-16"
                                                   id="tableItemRequest">
                                                <thead class="sticky-topphp">
                                                <tr>
                                                    <th style="width: 33%;">Item</th>
                                                    <th style="width: 33%;">Jumlah permintaan</th>
                                                    <th style="width: 33%;">Unit</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @if(isset($component['components']) && !empty($component['components']))
                                                    @foreach($component['components'] as $recipe)
                                                        <tr wire:key="{{ $loop->iteration }}">
                                                            <td>{{ $recipe['name'] }}</td>
                                                            <td>{{ $recipe['target_qty'] }}</td>
                                                            <td>{{ $recipe['unit'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    @endif
                </div>

            @elseif($status == 'Membuat permintaan bahan' || $status == 'Bahan dikirim')

                <div class="col-sm-5 offset-1">
                    <div class="container-input-default">
                        <p class="subtitle-3-regular">Kode produksi</p>

                        <div id="divider" class="margin-symmetric-vertical-6"></div>

                        <input type="name" class="form-control input-default"
                               id="warehouseInput"
                               value="{{ $production->code }}" disabled>
                    </div>

                    <div class="container-input-default  margin-top-16">
                        <p class="subtitle-3-regular">Kode referensi</p>
                        <div id="divider" class="margin-top-6"></div>
                        <input type="name" class="form-control input-default margin-top-6"
                               id="warehouseInput"
                               value="{{ $production->requestStock->code }}" disabled>
                    </div>
                </div>

                <div class="col-sm-7 offset-1 margin-top-24">
                    <div class="">
                        <div class="d-flex flex-row justify-content-between align-items-center">
                            <p class="subtitle-3-regular">
                                Informasi item</p>
                            @if(isset($production->requestStock->requestStockHistory) && $production->requestStock->requestStockHistory()->latest()->first()->status != 'Bahan dikirim')
                                <p class="text-danger"> * Menunggu kiriman bahan</p>
                            @endif
                        </div>

                        @if($errors->has('components.*.accept_qty'))
                            <span class="text-xs text-red-600">
                            {{ $errors->first('components.*.accept_qty') }}
                        </span>
                        @endif

                        <div id="divider" class="margin-top-6"></div>

                        <table class="table-component table table-hover margin-top-6"
                               id="tableItemRequest"
                        >
                            <thead class="sticky-top">
                            <tr>
                                <th>Item</th>
                                <th>Permintaan</th>
                                <th>Dikirim</th>
                                <th>Diterima</th>
                                <th>Unit</th>
                            </tr>
                            </thead>
                            <tbody>

                            @if(isset($components))

                                @foreach($components as $key => $component)

                                    <tr wire:key="{{ $loop->iteration }}">
                                        <td>{{ $component['name'] }}</td>
                                        <td>{{ $component['request_qty'] }}</td>
                                        <td>{{ $component['send_qty'] }}</td>
                                        <td>
                                            <input type="number" class="form-control input-default"
                                                   wire:model="components.{{$key}}.qty_accept"
                                                {{ isset($production->requestStock->requestStockHistory) && $production->requestStock->requestStockHistory()->latest()->first()->status != 'Bahan dikirim' ? 'disabled' : '' }}
                                            >
                                        </td>
                                        <td>{{ $component['unit'] }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5">Gagal mendapatkan item</td>
                                </tr>
                            @endif


                            </tbody>
                        </table>
                    </div>
                </div>

            @elseif($status == 'Bahan diterima')
                <div class="col-sm-5 offset-1">
                    <div class="container-input-default">
                        <label class="form-label input-label">Kode produksi</label>

                        <div id="divider" class="margin-symmetric-vertical-6"></div>

                        <input type="name" class="form-control input-default"
                               id="warehouseInput"
                               value="{{ $production->code }}" disabled>
                    </div>

                    <div class="container-input-default  margin-top-16">
                        <label class="form-label input-label">Kode referensi</label>
                        <div id="divider" class="margin-top-6"></div>
                        <input type="name" class="form-control input-default margin-top-6"
                               id="warehouseInput"
                               value="{{ $production->requestStock->code }}" disabled>
                    </div>

                    <div class="margin-top-24">
                        <h4 class="subtitle-3-bold">Permintaan produksi</h4>

                        {{-- Looping isi permintaan produksi --}}
                        @if(isset($components))
                            @foreach($components as $result)

                                <div class="margin-top-8" wire:key="{{ $loop->iteration }}">
                                    <div class="row margin-top-16">
                                        <div class="col-md-6">
                                            <div class="container-input-default">
                                                <label
                                                    class="form-label input-label">{{ $result['name']}}</label>
                                                <div id="divider" class="margin-symmetric-vertical-6"></div>
                                                <input type="text" class="form-control input-default"
                                                       value="{{ number_format($result['target_qty'], 2, '.', '') }}"
                                                       disabled>

                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="container-input-default">
                                                <label class="form-label input-label">Unit</label>
                                                <div id="divider" class="margin-symmetric-vertical-6"></div>
                                                <input type="text" class="form-control input-default" disabled
                                                       value="{{ $result['unit'] }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @endforeach

                        @endif


                    </div>


                    <div class="margin-top-24">
                        <h4 class="subtitle-3-bold">Hasil produksi</h4>

                        {{-- Looping hasil produksi --}}
                        @if(isset($components))
                            @foreach($components as $key => $result)

                                <div class="margin-top-8" wire:key="{{ $loop->iteration }}">
                                    <div class="row margin-top-16">
                                        <div class="col-md-6">
                                            <div class="container-input-default">
                                                <label
                                                    class="form-label input-label">{{ $result['name'] }}</label>
                                                <div id="divider" class="margin-symmetric-vertical-6"></div>
                                                <input type="number" class="form-control input-default"
                                                       wire:model="components.{{$key}}.result_qty"
                                                >
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="container-input-default">
                                                <label class="form-label input-label">Unit</label>
                                                <div id="divider" class="margin-symmetric-vertical-6"></div>
                                                <input type="text" class="form-control input-default" disabled
                                                       value="{{ $result['unit']}}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @endforeach
                        @endif
                    </div>

                    <div class="margin-top-24">
                        <label for="description" class="form-label">Catatan</label>
                        <div id="divider" class="margin-symmetric-vertical-6"></div>
                        <textarea class="form-control textarea" id="description" rows="5"
                                  wire:model="note"
                                  placeholder="Tulis catatan"></textarea>
                    </div>


                </div>

            @elseif($status == 'Produksi selesai')
                <div class="col-sm-5 offset-1">
                    <div class="container-input-default">
                        <label class="form-label input-label">Kode produksi</label>

                        <div id="divider" class="margin-symmetric-vertical-6"></div>

                        <input type="name" class="form-control input-default"
                               id="warehouseInput"
                               value="{{ $production->code }}" disabled>
                    </div>

                    <div class="container-input-default  margin-top-16">
                        <label class="form-label input-label">Kode referensi</label>
                        <div id="divider" class="margin-top-6"></div>
                        <input type="name" class="form-control input-default margin-top-6"
                               id="warehouseInput"
                               value="{{ $production->requestStock->code }}" disabled>
                    </div>

                    <div class="margin-top-24">
                        <h4 class="subtitle-3-bold">Hasil produksi</h4>

                        {{-- Looping hasil produksi --}}
                        @if(isset($components))
                            @foreach($components as $key => $result)

                                <div class="margin-top-8" wire:key="{{ $loop->iteration }}">
                                    <div class="row margin-top-16">
                                        <div class="col-md-6">
                                            <div class="container-input-default">
                                                <label
                                                    class="form-label input-label">{{ $result['name'] }}</label>
                                                <div id="divider" class="margin-symmetric-vertical-6"></div>
                                                <input type="number" class="form-control input-default"
                                                       wire:model="components.{{$key}}.result_qty"
                                                       disabled
                                                >
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="container-input-default">
                                                <label class="form-label input-label">Unit</label>
                                                <div id="divider" class="margin-symmetric-vertical-6"></div>
                                                <input type="text" class="form-control input-default" disabled
                                                       value="{{ $result['unit']}}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @endforeach
                        @endif
                    </div>

                    <div class="margin-top-24">
                        <div class="title d-flex flex-row justify-content-between align-items-center">
                            <h4 class="subtitle-3-bold">Item sisa</h4>

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="flexSwitchCheckChecked" checked wire:model="isSaveOnCentral">
                                <label class="form-check-label" for="flexSwitchCheckChecked"
                                >Simpan
                                    didapur
                                    sentral</label>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-sm-8 offset-1">

                    <table class="table-component table table-hover margin-top-16"
                           id="tableItemRequest"
                    >
                        <thead class="sticky-topphp">
                        <tr>
                            <th>
                                <input class="form-check-input" type="checkbox" value=""
                                       id="selectAllCheckbox"
                                       wire:model="selectAll">
                            </th>
                            <th>Item</th>
                            <th>Diterima</th>
                            <th>Digunakan</th>
                            <th>Sisa</th>
                            <th>Unit</th>
                        </tr>
                        </thead>
                        <tbody>

                        @if(!empty($itemRemaining))
                            @foreach($itemRemaining as $key => $item)
                                {{ Log::info( $item)  }}
                                <tr wire:key="{{ $loop->iteration }}">
                                    <td>
                                        <input class="form-check-input" type="checkbox"
                                               id="checkbox_{{ $loop->iteration }}"
                                               wire:model.live="itemRemaining.{{$key}}.isChecked"
                                        >
                                    </td>
                                    <td>{{ $item['item_name']}}</td>
                                    <td>{{ $item['qty_accept']}}</td>
                                    <td>
                                        <input type="name" class="form-control input-default"
                                               id="warehouseInput"
                                               {{ $item['isChecked'] ? '' : 'disabled' }}
                                               wire:model.live.debounce.600ms="itemRemaining.{{$key}}.qty_use">
                                    </td>
                                    <td>{{ ($item['qty_accept'] - $item['qty_use']) }}</td>
                                    <td>{{ $item['unit']}}</td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>


                </div>

            @elseif($status == 'Menunggu pengiriman' || $status == 'Selesai produksi')
                <div class="col-sm-5 offset-1">
                    <div>
                        <p class="subtitle-3-regular">Kode referensi</p>
                        <div id="divider" class="margin-top-6"></div>
                        <p class="margin-top-6 subtitle-3-medium">{{ $production->requestStock->code }}</p>
                    </div>


                    <div class="margin-top-24">
                        <p class="subtitle-3-regular">Kode produksi</p>
                        <div id="divider" class="margin-top-6"></div>
                        <p class="margin-top-6 subtitle-3-medium">
                            {{ $production->code }}
                        </p>
                    </div>

                    <div class="margin-top-24">
                        <p class="subtitle-3-regular">Diminta oleh</p>
                        <div id="divider" class="margin-top-6"></div>
                        <p class="margin-top-6 subtitle-3-medium">
                            {{ $production->requestStock->warehouse->name }}
                        </p>
                    </div>

                    <div class="margin-top-24">
                        <p class="subtitle-3-regular">Tanggal permintaan</p>
                        <div id="divider" class="margin-top-6"></div>
                        <p class="margin-top-6 subtitle-3-medium">
                            {{ Carbon::createFromFormat('Y-m-d H:i:s', $requestStock->created_at)->locale('id_ID')->isoFormat('D MMMM Y') }}
                        </p>
                    </div>

                    <div class="margin-top-24">
                        <p class="subtitle-3-regular">Tanggal selesai produksi</p>
                        <div id="divider" class="margin-top-6"></div>
                        <p class="margin-top-6 subtitle-3-medium">
                            {{ Carbon::createFromFormat('Y-m-d H:i:s', $production->remaining->first()->created_at )->locale('id_ID')->isoFormat('D MMMM Y') }}
                        </p>
                    </div>

                    <div class="margin-top-24">
                        <p class="subtitle-3-regular">Hasil produksi</p>
                        <div id="divider" class="margin-top-6"></div>
                        <table class="table-component table table-hover margin-top-16"
                        >
                            <thead class="sticky-topphp">
                            <tr>
                                <th>Item</th>
                                <th>Diminta</th>
                                <th>Hasil</th>
                                <th>Unit</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(isset($production->result))

                                @foreach($production->result as $resultProduction)
                                    <tr wire:key="{{ $loop->iteration }}">
                                        <td>{{ $resultProduction->targetItem->name }}</td>
                                        <td>{{ $resultProduction->qty_target }}</td>
                                        <td>{{ $resultProduction->qty_result }}</td>
                                        <td>{{ $resultProduction->targetItem->unit->name }}</td>
                                    </tr>
                                @endforeach

                            @endif
                            </tbody>
                        </table>
                    </div>


                    <div class="margin-top-24">
                        <p class="subtitle-3-regular">Hasil sisa produksi</p>
                        <div id="divider" class="margin-top-6"></div>
                        <table class="table-component table table-hover margin-top-16"
                        >
                            <thead class="sticky-topphp">
                            <tr>
                                <th>Item</th>
                                <th>Jumlah</th>
                                <th>Unit</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(isset($production->remaining))
                                @foreach($production->remaining->first()->detail as $itemRemaining)
                                    <tr wire:key="{{ $loop->iteration }}">
                                        <td>{{ $itemRemaining->item->name }}</td>
                                        <td>{{ $itemRemaining->qty_remaining }}</td>
                                        <td>{{ $itemRemaining->item->unit->name }}</td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>

            @endif


        </div>


        <script>

        </script>


</x-page-layout>
