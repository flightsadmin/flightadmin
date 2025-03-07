<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Url;
use Livewire\Component;

class Manager extends Component
{
    #[Url]
    public $tab = 'users';

    public function setTab($tab)
    {
        $this->tab = $tab;
    }

    public function render()
    {
        return view('livewire.admin.manager');
    }
}
