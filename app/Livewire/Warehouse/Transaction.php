<?php

namespace App\Livewire\Warehouse;

use App\Models\RequestStock;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use Livewire\Component;

class Transaction extends Component
{
    public Collection $warehouses;

    public string $toggle = 'request';

    public string $selected = 'all';
    #[Url(keep: true, as: 'option')]
    public string $urlQuery = 'request';
    public string $id = '';
    private string $type = 'outlet';

    public function mount()
    {

        $this->getOutletCentralKitchen();


        if ($this->urlQuery != 'request' && $this->urlQuery != 'stockIn' && $this->urlQuery != 'stockOut') {
            $this->urlQuery = 'request';
        }

        $this->getRequestStock();
    }

    private function getOutletCentralKitchen()
    {
        try {

            $warehouses = Warehouse::all(['id', 'name']);

            // Gabungkan kedua koleksi menjadi satu collection
            $this->warehouses = $warehouses;


        } catch (Exception $exception) {
            Log::error('gagal mengambil data outlet dan central kitchen di dropdown transaksi');
            Log::error($exception->getTraceAsString());
            Log::error($exception->getMessage());
        }
    }

    private function getRequestStock()
    {
        try {

            return RequestStock::paginate(10);

        } catch (Exception $exception) {
            Log::error('gagal mendapatkan request stock di transaksi gudang');
            Log::error($exception->getMessage());
            Log::error($exception->getTraceAsString());
        }
    }

    public function boot()
    {
        if ($this->selected != 'all') {
            $this->selectWarehouse();
        }
    }

    public function selectWarehouse()
    {
        try {
            $warehouse = Warehouse::findOrFail($this->selected);


            if (!$warehouse->outlet->isEmpty()) {
                $this->id = $warehouse->id;
                return;
            }

            if (!$warehouse->centralKitchen->isEmpty()) {
                $this->id = $warehouse->id;
                $this->type = 'centralKitchen';
                return;
            }


            // TODO: validasi error id tidak ketemu di ck atau outlet
        } catch (Exception $exception) {
            Log::error($exception->getTraceAsString());
            Log::error($exception->getMessage());
        }
    }

    public function create()
    {

        // TODO: jika id kosong maka buat pesan error
        if ($this->id == '') {
            return;
        }


        // jika toggle berupa request
        if ($this->urlQuery == 'request') {
            $this->redirect("/warehouse/transaction/add-transaction?option=request&type={$this->type}&id={$this->id}", true);
        }

    }

    public function toggleChange()
    {
        $this->urlQuery = $this->toggle;
    }

    public function render()
    {
        return view('livewire.warehouse.transaction', ['requestStock' => RequestStock::when($this->id, function ($query) {
            return $query->where('warehouses_id', $this->id);
        })->orderBy('id', 'DESC')->paginate(10)]);
    }
}
