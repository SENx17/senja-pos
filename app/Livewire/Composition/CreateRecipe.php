<?php

namespace App\Livewire\Composition;

use App\Service\Impl\RecipeServiceImpl;
use App\Service\RecipeService;
use App\Utils\IndonesiaCurrency;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;

class CreateRecipe extends Component
{
    #[Url(as: 'type', keep: true)]
    public string $type = '';

    #[Rule('required|min:5|unique:recipe_items,code')]
    public string $code;
    #[Rule('required|array|min:1')]
    public array $ingredients;
    public Collection $items;

    public Collection $menuOrMaterial;

    #[Rule('required|min:5')]
    public string $selectMenuOrMaterial = '';
    public string $totalAvg;
    public string $totalLastCost;
    private RecipeService $recipeService;

    public function mount()
    {
        $result = $this->extractUrl($this->type);
        $this->type = $result;
        $this->getAllItem();

    }

    public function extractUrl(string $url)
    {
        if ($url != 'recipeMenu' && $url != 'recipeSemi') {
            return 'recipeMenu';
        }

        return $url;
    }

    /**
     * dapatkan semua item komponen yang akan digunakan untuk dijadikan komponen resep
     * @return void
     */
    private function getAllItem()
    {
        $items = $this->recipeService->getAllItem();

        // item null karena error
        if ($items == null) {
            return;
        }
        $this->items = $items;

        // dapatkan data bahan atau menu
        $menuOrMaterial = $this->recipeService->selectMenuOrMaterial(!(($this->type == 'recipeSemi')));

        if ($menuOrMaterial == null) {
            return;
        }
        $this->menuOrMaterial = $menuOrMaterial;


    }

    public function boot()
    {
        $this->recipeService = app()->make(RecipeServiceImpl::class);
    }

    public function render()
    {
        return view('livewire.composition.create-recipe');
    }

    // dapatkan data item

    /**
     * lakukan penambahan array ingredients
     * @return void
     */
    public function addIngredient()
    {
        $this->ingredients[] = [
            'id' => '',
            'usage' => '',
            'unit' => [
                'id' => '',
                'name' => '',
            ],
            'initAvgCost' => '',
            'initLastCost' => '',
            'avgCost' => '',
            'lastCost' => '',
        ];
    }

    /**
     *  jika item ingredients sudah ditentukan maka isi usage, unit, avg cost dan last costnya
     * @param $index
     * @return void
     */
    public function itemSelected($index)
    {

        $itemId = $this->ingredients[$index]['id'];
        $result = $this->items->find($itemId);

        // jika error maka hentikan program
        if ($result == null && empty($result)) {
            Log::error('gagal mendapatkan data item yang dipilih saat buat resep setengah jadi');
            return;
        }


        $avg = IndonesiaCurrency::formatToRupiah($result->stockItem()->latest()->first()->avg_cost);
        $last = IndonesiaCurrency::formatToRupiah($result->stockItem()->latest()->first()->last_cost);

        $this->ingredients[$index]['usage'] = 1;
        $this->ingredients[$index]['unit']['id'] = $result->unit->id;
        $this->ingredients[$index]['unit']['name'] = $result->unit->name;
        $this->ingredients[$index]['initAvgCost'] = $avg;
        $this->ingredients[$index]['initLastCost'] = $last;
        $this->ingredients[$index]['avgCost'] = $avg;
        $this->ingredients[$index]['lastCost'] = $last;

        $this->calculateTotalAvgAndLastCost();
    }

    private function calculateTotalAvgAndLastCost()
    {
        $totalAvg = 0;
        $totalLastCost = 0;
        foreach ($this->ingredients as $ingredient) {
            $avg = str_replace('Rp ', '', $ingredient['avgCost']);
            $lastCost = str_replace('Rp ', '', $ingredient['lastCost']);

            $totalAvg += $avg;
            $totalLastCost += $lastCost;
        }


        $this->totalAvg = IndonesiaCurrency::formatToRupiah(number_format($totalAvg, 3, '', ''));
        $this->totalLastCost = IndonesiaCurrency::formatToRupiah(number_format($totalLastCost, 3, '', ''));

    }

    /**
     * fungsi ini digunakan untuk menghitung avg cost dan last cost saat unit terupdate
     * @param $index
     * @return void
     */
    public function updateUsage($index)
    {

        $initAvgCost = $this->ingredients[$index]['initAvgCost'];
        $initAvgCost = str_replace('Rp ', '', $initAvgCost);
        $initLastCost = $this->ingredients[$index]['initLastCost'];
        $initLastCost = str_replace('Rp ', '', $initLastCost);
        $usage = $this->ingredients[$index]['usage'];


        $this->ingredients[$index]['avgCost'] = IndonesiaCurrency::formatToRupiah(number_format(floatval($usage) * floatval($initAvgCost), 3, '', ''));
        $this->ingredients[$index]['lastCost'] = IndonesiaCurrency::formatToRupiah(number_format(floatval($usage) * floatval($initLastCost), 3, '', ''));

        $this->calculateTotalAvgAndLastCost();
    }


    public function save()
    {

        Log::debug($this->ingredients);

        $this->validate([
            'code' => 'required|min:5|unique:recipe_items,code',
            'ingredients' => 'required|array|min:1',
            'selectMenuOrMaterial' => 'required|min:5',
            'ingredients.*.id' => 'required',
        ]);


        foreach ($this->ingredients as $ingredient) {
            $recipes[] = [
                'items_id' => $ingredient['id'],
                'usage' => $ingredient['usage'],
                'units_id' => $ingredient['unit']['id'],
            ];
        }

        Log::debug($recipes);


        if ($this->type == 'recipeSemi') {
            $result = $this->recipeService->saveRecipeItem($this->code, $this->selectMenuOrMaterial, $recipes);

            if ($result) {
                notify()->success('Berhasil buat resep', 'Sukses');
                $this->reset('code', 'ingredients', 'selectMenuOrMaterial', 'totalAvg', 'totalLastCost');
                return;
            }

            notify()->error('Gagal buat resep', 'Gagal');
            $this->reset('code', 'ingredients', 'selectMenuOrMaterial', 'totalAvg', 'totalLastCost');
            return;
        }
    }

}

