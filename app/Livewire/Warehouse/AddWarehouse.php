<?php

namespace App\Livewire\Warehouse;

use App\Models\Category;
use App\Models\CategoryItem;
use App\Models\CentralKitchen;
use App\Models\Item;
use App\Models\Outlet;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class AddWarehouse extends Component
{
    use WithPagination, WithFileUploads;

    #[Rule('required|min:5|unique:warehouses,warehouse_code')]
    public string $codeWarehouse;
    #[Rule('required|min:5|unique:warehouses,name')]
    public string $nameWarehouse;
    #[Rule('required|min:5')]
    public string $addressWarehouse;

    public array $areas = [];

    public array $items = [];
    public bool $isShow = false;
    public bool $isShowModalNewItem = false;

    public bool $isAddedArea = false;
    public bool $notFound = false;
    public string $state;

    public string $area;
    public string $rack = '';
    #[Url(as: 'qId', keep: true)]
    public string $url = '';

    protected $rules = [
        'areas.*.area.area' => 'required|min:2',
        'areas.*.area.rack' => 'required|min:2',
        'areas.*.area.category_inventory' => 'required|min:3',
        'areas.*.rack.*.rack' => 'required|min:2',
        'areas.*.rack.*.category_inventory' => 'required|min:3',

    ];
    protected $messages = [
        'areas.*.area.area.required' => 'The Area A field is required.',
        'areas.*.area.area.min' => 'The Area A field should be at least 3 characters.',
        'areas.*.area.rack.required' => 'The Rack field is required.',
        'areas.*.area.rack.min' => 'The Rack field should be at least 3 characters.',
        'areas.*.area.category_inventory.required' => 'The Category Inventory field is required.',
        'areas.*.area.category_inventory.min' => 'The Category Inventory field should be at least 3 characters.',
        'areas.*.rack.*.rack.required' => 'The Rack field is required.',
        'areas.*.rack.*.rack.min' => 'The Rack field should be at least 3 characters.',
        'areas.*.rack.*.category_inventory.required' => 'The Category Inventory field is required.',
        'areas.*.rack.*.category_inventory.min' => 'The Category Inventory field should be at least 3 characters.',
    ];


    public function cancelNewItem()
    {
        $this->isShowModalNewItem = false;
    }


    public function loadCategory()
    {
        $this->categoryItems = Category::all();
    }

    public function selectCategory(string $idCategory, string $categoryName)
    {

        $this->categoryId = $idCategory;
        $this->categoryName = $categoryName;
        $this->dispatch('close-dropdown');
    }

    /**
     * fungsi ini digunakan untuk menambahkan input area baru
     * @return void
     */
    public function addArea()
    {
        $this->isAddedArea = true;
        $this->areas[] = ['area' => ['area' => '', 'rack' => '', 'category_inventory' =>
            '', 'item' => []]];

    }

    /**
     * fungsi ini digunakan untuk menambahkan input rak baru ke area
     * @return void
     */
    public function addRack()
    {
        $this->areas[count($this->areas) - 1]['rack'][] = ['rack' => '', 'category_inventory' => '', 'item' => []];
    }

    /**
     * fungsi ini digunakan untuk menghapus area
     * @param $key
     * @return void
     */
    public function remove($key)
    {

        // hapus area terakhir dari array
        unset($this->areas[$key]);

        // pengecekan jika area kosong, maka sembunyikan tombol + rack
        if ($this->checkNotEmptyAreas()) {
            $this->isAddedArea = false;
        }
    }


    /**
     * fungsi ini digunakan untuk melakukan pengecekan apakah data array areas kosong
     * @return bool
     */
    private function checkNotEmptyAreas(): bool
    {
        return empty($this->areas);
    }

    /**
     * fungsi ini digunakan untuk menghapus rak diarea
     * @param $key
     * @param $subkey
     * @return void
     */
    public function removeRack($key, $subkey)
    {
        unset($this->areas[$key]['rack'][$subkey]);
    }

    /**
     * listener dari file native javacript ini digunakan untuk melakukan pembukaan
     * modal secara manual dengan memanggil fungsi open modal
     * @param $area
     * @return void
     */
    #[On('load-modal')]
    public function loadItem($area)
    {
        $this->area = $area;
        $this->rack = '';
        Log::info('area' . $this->area);

        $this->openModal();

    }

    /**
     * fungsi ini digunakan untuk melakukan pembukaan modal
     * @return void
     */
    private function openModal()
    {
        $this->isShow = !$this->isShow;

        $this->items = [];

        // lakuakn cursor paginate data item sebanyak 20
        $item = $this->firstCursor;


        // cek apakah data sudah ditambahkan item dan rak
        foreach ($item['data'] as $data) {
            $this->validateAddedItem($data);
        }


        // simpan id next cursor ke global next cusor id
        // cursor id ini digunakan untuk mendapatkan data selanjutnya menggunakan id
        $this->nextCursorId = $item['next_cursor'];

    }

    /**
     * @param mixed $data
     * @return void
     */
    public function validateAddedItem(mixed $data): void
    {
        $isSkip = false;
        $itemId = $data['id'];


        // cek apakah data sudah ditambahkan kedalam area dan rak
        foreach ($this->areas as $key => $area) {
            $isItemAdded = false; // Tambahkan variabel ini

            // cek kedalam item area
            foreach ($area['area']['item'] as $areaItem) {
                $areaItemId = $areaItem['id'];

                if ($itemId == $areaItemId) {
                    $this->items['data'][] = [
                        'id' => $data['id'],
                        'name' => $data['name'],
                        'image' => $data['item_image'],
                        'checked' => true,
                        'from' => 'area',
                        'indexArea' => $key,
                    ];
                    $isItemAdded = true;
                    $isSkip = true;
                }
            }

            // cek kedalam item area rak tambahan
            if (isset($area['rack'])) {
                foreach ($area['rack'] as $keyRack => $rack) {
                    foreach ($rack['item'] as $rackItem) {
                        if ($itemId == $rackItem['id']) {
                            $this->items['data'][] = [
                                'id' => $data['id'],
                                'name' => $data['name'],
                                'image' => $data['item_image'],
                                'checked' => true,
                                'from' => 'rack',
                                'indexArea' => $key,
                                'indexRack' => $keyRack,
                            ];
                            $isItemAdded = true;
                            $isSkip = true;
                        }
                    }
                }
            }

            if ($isItemAdded) {
                break; // Hentikan iterasi jika item sudah ditambahkan
            }
        }

        if (!$isSkip) {
            $this->items['data'][] = [
                'id' => $data['id'],
                'image' => $data['item_image'],
                'name' => $data['name'],
                'checked' => false,
            ];
        }
    }

    /**
     * fungsi terpisah untuk melakukan pembukaan modal dari data rack,
     * dikarenakan pada tahap ini terdapat perbedaan logic dari pengolahan
     * modal yang lainnya
     * @param $area
     * @param $rack
     * @return void
     */
    #[On('load-modal-rack')]
    public function loadRack($area, $rack)
    {
        // simpan area
        $this->area = $area;
        // simpan rack
        $this->rack = $rack;
        // buka modal
        $this->openModal();
    }

    /**
     * listener ini digunakan untuk melakuakn penutupan modal yang terbuka secara manual
     * dan melakukan logic tambahan untuk mengosongkan data item cursor yang keload
     * @return void
     */
    #[On('dismiss-modal')]
    public function dismissModal()
    {
        $this->items = [];
        $this->isShow = false;
    }


    #[On('dismiss-modal-new-item')]
    public function dismissModalNewItem()
    {

        $this->isShowModalNewItem = false;
    }

    /**
     * listener ini digunakan untuk mendapatkan data item lebih dari data sebelumnya (infinite loading)
     * menggunakan cursor dengan cursor id
     * @return void
     */
    #[On('load-more')]
    public function handleScroll()
    {
        // cek terlebih dahulu apakah cursor id tidak null
        // jika datanya null berarti sudah tidak ada data lagi
        if ($this->nextCursorId != null) {
            $nextItems = Item::orderBy('id')->cursorPaginate(10, ['*'], 'cursor', $this->nextCursorId)->toArray();

            // tambahkan data baru ke variabel $items
            foreach ($nextItems['data'] as $data) {

                $this->validateAddedItem($data);
            }


            // simpan next cursor id dari cursor ini
            $this->nextCursorId = $nextItems['next_cursor'];
            return;
        }

        $this->dispatch('stop-request');

    }

    /**
     * fungsi ini digunakan untuk melakukan validasi data sebelum
     * menambah warehouse
     * @return void
     */
    public function validateInput()
    {

        // TODO: Perbaiki validasi message rack yang sama untuk satu gudang atau area yg sama
        // lakukan validasi hanya data yang diperlukan
        $this->validate([
            'areas.*.area.rack' => [
                'required',
                'min:2',
                function ($attribute, $value, $fail) {

                    foreach ($this->areas as $area) {
                        if (isset($area['rack'])) {
                            foreach ($area['rack'] as $rack) {
                                if ($rack['rack'] == $value) {
                                    $fail("The $attribute must contain distinct values.");
                                }
                            }
                        }
                    }

                },
            ],
            'areas.*.rack.*.rack' => [
                'required',
                'min:2',
                'distinct',
                function ($attribute, $value, $fail) {
                    foreach ($this->areas as $area) {
                        if ($area['area']['rack'] == $value) {
                            $fail("The $attribute must contain distinct values.");
                        }
                    }
                },
            ],
            'areas.*.area.area' => 'required|min:2|distinct',
            'codeWarehouse' => 'required|min:5',
            'nameWarehouse' => 'required|min:5',
            'addressWarehouse' => 'min:5',
        ]);


        $this->storeWarehouse();

    }

    private function storeWarehouse()
    {

        try {

            DB::beginTransaction();
            // lakukan proses simpan gudang
            $warehouse = Warehouse::create(
                [
                    'warehouse_code' => $this->codeWarehouse,
                    'name' => $this->nameWarehouse,
                    'address' => $this->addressWarehouse,
                ]
            );


            // simpan warheouse dan outlet ke warehouses outlets
            if ($this->url == '' && !isset($this->url)) {

                return;
            }

            if ($this->state == 'outlet') {
                // simpan ke warehouses_outlet
                $warehouse->outlet()->syncWithoutDetaching($this->url);
            } else {
                // simpan ke warehouse_central_kitchen
                $warehouse->centralKitchen()->syncWithoutDetaching($this->url);
            }


            foreach ($this->areas as $dataArea) {
                // isi data area
                $areaName = $dataArea['area']['area'];
                $rackName = $dataArea['area']['rack'];

                $area = $warehouse->areas()->create([
                    'name' => $areaName
                ]);

                // isi data rak
                $rack = $area->racks()->create([
                    'name' => $rackName,
                ]);

                if (!empty($dataArea['area']['item'])) {
                    foreach ($dataArea['area']['item'] as $item) {

                        $id = $item['id'];

                        $item = Item::find($id);

                        if ($item) {
                            $item->update(['racks_id' => $rack->id]);
                        }

                    }
                }


                if (isset($dataArea['rack'])) {
                    foreach ($dataArea['rack'] as $dataRack) {
                        $rackName = $dataRack['rack'];

                        $rack = $area->racks()->create([
                            'name' => $rackName,
                        ]);

                        if (isset($dataRack['item'])) {
                            foreach ($dataRack['item'] as $item) {
                                $itemId = $item['id'];

                                $item = Item::find($itemId);

                                if ($item) {
                                    $item->update(['racks_id' => $rack->id]);
                                }
                            }
                        }
                    }
                }


            }

            DB::commit();

            $this->reset();
            notify()->success('Berhasil tambah gudang', 'Sukses');

        } catch (Exception $exception) {
            DB::rollBack();

            Log::error('gagal menambahkan gudang baru');
            Log::error($exception->getMessage());
            Log::error($exception->getTraceAsString());

            notify()->error('gagal tambah gudang', 'Gagal');

        }


    }

    /**
     * fungsi ini digunakan untuk menambahkan item baru ke area dan rak
     * @param $id
     * @param $name
     * @return void
     */
    public function selectItem($id, $name)
    {

        // hapus jika item sudah ada di area atau rack
        // cek apakah data sudah ditambahkan diarea
        foreach ($this->areas as $key => $dataArea) {
            Log::info(json_encode($dataArea));

            // cek apakah item berada didalam area
            $isExist = $this->checkExistItem($dataArea['area']['item'], $id);

            // jika item sudah ditambahkan maka hentikan looping
            if ($isExist) {
                $areaCollection = collect($this->areas[$key]['area']['item']);
                $areaCollection = $this->rejectCollectionAreaItem($areaCollection, $id);
                $this->areas[$key]['area']['item'] = $areaCollection;
                return;
            }

            // cek data di dalam area rack
            if (isset($dataArea['rack'])) {
                foreach ($dataArea['rack'] as $subKey => $subRack) {
                    $isExist = $this->checkExistItem($subRack['item'], $id);

                    $rackCollection = collect($this->areas[$key]['rack'][$subKey]['item']);
                    $rackCollection = $this->rejectCollectionAreaItem($rackCollection, $id);
                    $this->areas[$key]['rack'][$subKey]['item'] = $rackCollection;

                    // jika item sudah ditambahkan didalam rack area maka hentikan looping
                    if ($isExist) {
                        return;
                    }
                }
            }

        }


        // tambahkan item ke area atau rack
        $this->addItem($id, $name);
    }

    /**
     * fungsi ini digunakan untuk mengecek apakah item sudah pernah ditambahkan
     * @return void
     */
    private function checkExistItem(array $areas, string $id): bool
    {
        foreach ($areas as $dataItem) {
            if ($dataItem['id'] == $id) {
                return true;
            }
        }

        return false;
    }

    private function rejectCollectionAreaItem($collection, $id)
    {
        return $collection->reject(function ($item) use ($id) {
            return $item['id'] == $id;
        })->values()->all();
    }

    private function addItem($id, $name)
    {

        if ($this->rack == '') {

            // tambahkan item ke area yang sudah dipilih
            $this->areas[$this->area]['area']['item'][] = [
                'id' => $id,
                'name' => $name,
            ];

            Log::info($this->areas);
            return;
        }


        $this->areas[$this->area]['rack'][$this->rack]['item'][] = [
            'id' => $id,
            'name' => $name,
        ];
    }

    public function mount()
    {
        try {

            $outlet = Outlet::find($this->url);
            $centralKitchen = CentralKitchen::find($this->url);

            if ($outlet == null && $centralKitchen == null) {
                $this->notFound = true;
                return;
            }

            if ($outlet != null) {
                $this->state = 'outlet';
            }

            if ($centralKitchen != null) {
                $this->state = 'central kitchen';
            }

            $this->notFound = false;

        } catch (Exception $exception) {
            Log::error('gagal mendapatkan id outlet / central kitchen di add warehouse');
            Log::error($exception->getMessage());
            Log::error($exception->getTraceAsString());
        }
    }

    public function render()
    {

        return view('livewire.warehouse.add-warehouse');
    }

    public function openModalNewItem()
    {
        $this->isShowModalNewItem = true;
    }

    // TODO: PERBAIKI BUG CLOSE MODAL SAAT PINDAH NAVIGATION PAGE

    public function closeModalNewItem()
    {
        $this->isShowModalNewItem = false;
    }

    /**
     * fungsi ini digunakan untuk menghapus area yang ditambahkan
     * @param $id
     * @param $index
     * @return void
     */
    public function removeCheckboxArea($id, $index)
    {
        // lakukan penghapus item dengan area berdasarkan index
        if ($index !== null && $id !== null) {
            $areaCollection = collect($this->areas[$index]['area']['item']);

            $areaCollection = $this->rejectCollectionAreaItem($areaCollection, $id);

            $this->areas[$index]['area']['item'] = $areaCollection;

        }
    }

    /**
     * fungsi ini digunakan untuk menghapus rack yang sudah ditambahkan
     * @param $id
     * @param $indexArea
     * @param $indexRack
     * @return void
     */
    public function removeCheckboxRack($id, $indexArea, $indexRack)
    {
        if ($id !== null && $indexArea !== null && $indexRack !== null) {
            $rackCollection = collect($this->areas[$indexArea]['rack'][$indexRack]['item']);

            $rackCollection = $rackCollection->reject(function ($item) use ($id) {
                return $item['id'] == $id;
            })->values()->all();


            $this->areas[$indexArea]['rack'][$indexRack]['item'] = $rackCollection;
        }
    }

    public function setLocationWarehouse($id, $name)
    {
        $this->locationWarehouse = [
            'id' => $id,
            'name' => $name
        ];
    }

    public function rendered($view, $html)
    {
        $this->dispatch('set-width-title');
    }


    #[Computed]
    private function firstCursor(): array
    {
        return Item::where('racks_id', null)->orderBy('id')->cursorPaginate(20)->toArray();
    }


}
