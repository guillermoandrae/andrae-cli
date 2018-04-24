<?php

namespace App\Models;

class AbstractModel implements ModelInterface
{
    protected $id;

    final public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getId(): string
    {
        return $this->id;
    }
}
