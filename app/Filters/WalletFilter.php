<?php

namespace App\Filters;

class WalletFilter extends QueryFilter
{
    public function currency($currency)
    {
        return $this->builder->where('currency', $currency);
    }

    public function owner_name($name)
    {
        return $this->builder->where('owner_name', 'like', '%' . $name . '%');
    }
}
