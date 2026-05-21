<?php

use Livewire\Component;

new class extends Component
{
    public array $breadcrumbs;

    public function mount()
    {
        $this->breadcrumbs = $this->generate();
    }

    protected function generate(): array
    {
        $segments = request()->segments();
        $breadcrumbs = [];

        $url = '';
        foreach ($segments as $segment) {
            $url .= '/' . $segment;

            // skip segmen yang berupa angka (ID)
            if (is_numeric($segment)) continue;

            $breadcrumbs[] = [
                'label' => ucfirst(str_replace('-', ' ', $segment)),
                'url'   => url($url),
            ];
        }

        return $breadcrumbs;
    }
};
?>

<div>
    <flux:breadcrumbs>
    @foreach ($this->breadcrumbs as $index => $crumb)
        @if (!$loop->last)
            <flux:breadcrumbs.item :href="$crumb['url']">
                {{ $crumb['label'] }}
            </flux:breadcrumbs.item>
        @else
            <flux:breadcrumbs.item>
                {{ $crumb['label'] }}
            </flux:breadcrumbs.item>
        @endif
    @endforeach
</flux:breadcrumbs>
</div>
