<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\Filterable;

/**
 * @property string $id
 * @property string $owner_name
 * @property string $currency
 * @property int $balance
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Wallet extends Model
{
    use HasFactory, HasUuids, Filterable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'owner_name',
        'currency',
        'balance',
    ];

    protected $casts = [
        'balance' => 'integer',
    ];

    public function sentTransactions()
    {
        return $this->hasMany(Transaction::class, 'source_wallet_id');
    }

    public function receivedTransactions()
    {
        return $this->hasMany(Transaction::class, 'target_wallet_id');
    }
}
