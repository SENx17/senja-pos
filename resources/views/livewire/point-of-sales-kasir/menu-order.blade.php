<x-page-layout-pos>
    <div class="menu-content-wrapper">
        <div class="menu-content d-flex flex-lg-wrap gap-3">
            @for ($data = 0; $data < 30; $data++)
                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <livewire:point-of-sales-kasir.menu-order-modal>
                </div>
                <Button type="button" class="menu-card" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    <div class="card-body">
                        <img src="../img/chicken.png" alt="Test">
                    </div>
                    <p class="text-start mt-2 fw-semibold">
                        Bubur Hot Pot
                    </p>
                </Button>
            @endfor
        </div>
    </div>
</x-page-layout-pos>