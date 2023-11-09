<?php

namespace App\Livewire\Warehouse;

use App\Models\Warehouse;
use App\Service\WarehouseService;
use Livewire\Attributes\Url;
use Livewire\Component;

class DetailWarehousePage extends Component
{
    #[Url(as: 'q')]
    public string $urlQuery;

    public string $locationWarehouse;
    public array $areas = [];
    public bool $isAddedArea = false;
    public Warehouse $warehouse;

    public string $mode = 'view';
    public string $htmlCondition;
    public string $seeItemModal;


    public function mount()
    {
        $this->getDetailDataWarehouse($this->urlQuery);

    }

    private function getDetailDataWarehouse(string $id)
    {

        $warehouseService = app()->make(WarehouseService::class);

        // jika id nya kosong
        if (empty($this->urlQuery)) {
            return;
        }

        try {
            $this->warehouse = $warehouseService->getDetailWarehouse($id);

            // tampilkan warehouse tidak ketemu
            if ($this->warehouse == null) return;

            $this->areas = $warehouseService->getDetailDataAreaRackItemWarehouse($this->warehouse);

        } catch (\Exception $e) {
            // warehouse not found
            if ($e->getCode() == 1 || $e->getCode() == 2) {
                $this->htmlCondition = 'Data gudang tidak ditemukan, pastikan gudang ada jika masalah masih berlanjut silahkan hubungi administrator';
            }
        }


    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="d-flex justify-content-center align-items-center position-absolute top-50 start-50">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.warehouse.detail-warehouse-page');
    }


}
