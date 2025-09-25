<?php

namespace App\Helpers;

class Breadcrumb
{
    private array $items = [];

    public function add(string $title, ?string $url = null): self
    {
        $this->items[] = [
            'title' => $title,
            'url'   => $url
        ];
        return $this;
    }

    public function get(): array
    {
        return $this->items;
    }
}
