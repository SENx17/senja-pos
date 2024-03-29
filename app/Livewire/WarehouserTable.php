<?php

namespace App\Livewire;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;
use PowerComponents\LivewirePowerGrid\Cache;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Exportable;
use PowerComponents\LivewirePowerGrid\Footer;
use PowerComponents\LivewirePowerGrid\Header;
use PowerComponents\LivewirePowerGrid\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridColumns;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;


final class WarehouserTable extends PowerGridComponent
{
    use WithExport;

    public bool $deferLoading = true; // default false

    #[Modelable]
    public string $search = '';

    public string $loadingComponent = 'components.loading';


    public function setUp(): array
    {

        $userId = '123';

        return [

            Exportable::make('warehouse-data')
                ->type(Exportable::TYPE_XLS, Exportable::TYPE_CSV),
            Header::make()->showToggleColumns(),
            Footer::make()
                ->showPerPage(10)
                ->showRecordCount(),
            Cache::make()->forever()->ttl(30000),

        ];
    }

    public function datasource(): Builder
    {
        return Warehouse::query()
            ->join('areas', function ($areas) {
                $areas->on('areas.warehouses_id', '=', 'warehouses.id');
            })->join('racks', function ($racks) {
                $racks->on('racks.areas_id', '=', 'areas.id');
            })
            ->select(['warehouses.id', 'warehouses.address', 'warehouses.name', 'warehouses.warehouse_code', 'areas.name AS areas_name', 'racks.name AS rack_name']);
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function addColumns(): PowerGridColumns
    {
        return PowerGrid::columns();
    }

    public function columns(): array
    {
        return [

            Column::add()->title('Kode gudang')->field('warehouse_code', 'warehouse_code')->searchable()->sortable(),
            Column::add()->title('Nama gudang')->field('name')->searchable()->sortable(),
            Column::add()->title('Area')->field('areas_name')->searchable()->sortable(),
            Column::add()->title('Rak')->field('rack_name')->searchable()->sortable(),
            Column::add()->title('Alamat')->field('address')->searchable()->sortable(),

        ];
    }


    public function filters(): array
    {
        return [

        ];
    }

    public function rendered($view, $html)
    {
        $this->dispatch('test');
    }

}
