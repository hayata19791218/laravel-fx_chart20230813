<?php

namespace App\View\Components\Layout;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Layout extends Component
{
    public ?string $user_name;
    public ?string $title;

    public function __construct(?string $userName = null, ?string $title = 'チャート表aaaaaaa')
    {
        $this->user_name = $userName;
        $this->title = $title;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.layout.layout');
    }
}