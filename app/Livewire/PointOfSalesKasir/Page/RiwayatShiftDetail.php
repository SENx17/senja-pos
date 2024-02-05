<?php

namespace App\Livewire\PointOfSalesKasir\Page;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title("Riwayat Shift Detail")]
#[Layout("components.layouts.pos")]
class RiwayatShiftDetail extends Component
{
    public function render()
    {
        return view('livewire.point-of-sales-kasir.page.riwayat-shift-detail');
    }
}
