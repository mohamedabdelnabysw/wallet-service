<?php

namespace App\Filters;

class TransactionFilter extends QueryFilter
{
    public function type($type)
    {
        return $this->builder->where('type', $type);
    }

    public function amount($amount)
    {
        return $this->builder->where('amount', $amount);
    }

    public function source_wallet_id($walletId)
    {
        return $this->builder->where('source_wallet_id', $walletId);
    }

    public function target_wallet_id($walletId)
    {
        return $this->builder->where('target_wallet_id', $walletId);
    }

    public function date_from($date)
    {
        return $this->builder->where('created_at', '>=', $date);
    }

    public function date_to($date)
    {
        return $this->builder->where('created_at', '<=', $date);
    }
}
