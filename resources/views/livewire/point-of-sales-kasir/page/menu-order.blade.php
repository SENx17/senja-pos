<x-page-layout-pos>
    <livewire:components.navbar-kasir.header-pos-kasir>
        <div class="menu-content-wrapper">
            <div class="menu-content">
                @foreach ($menuCS as $data)
                    <div class="menu-card" data-bs-toggle="modal" data-bs-target="#modalOrderMenu{{ $data['id'] }}">
                        <livewire:point-of-sales-kasir.modal-menu-order :dataMenu="$data" wire:key="{{ $data['id'] }}">
                            <div class="card-body">
                                <img src="{{ asset($data['image_url']) }}" alt="Test">
                            </div>
                            <p class="menu-title text-light-12 color-4040 d-block text-truncate text-start">
                                {{ $data['menu_name'] }}
                            </p>
                    </div>
                @endforeach
                <livewire:point-of-sales-kasir.side-menu-order>
            </div>
</x-page-layout-pos>
