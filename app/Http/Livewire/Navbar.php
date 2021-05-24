<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Navbar extends Component
{
    public $show = false;

    public function onClickAvatar()
    {
        $this->show = !$this->show;
    }

    public function render()
    {
        return view('livewire.navbar');
    }
}
