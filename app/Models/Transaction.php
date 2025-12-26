<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\Filterable;

/**
 * @property string $id
 * @property string $type
 * @property int $amount
 * @property string $source_wallet_id
 * @property string $target_wallet_id
 * @property string $idempotency_key
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read Wallet|null $sourceWallet
 * @property-read Wallet|null $targetWallet
 */
class Transaction extends Model
{
    use HasFactory, HasUuids, Filterable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'type',
        'amount',
        'source_wallet_id',
        'target_wallet_id',
        'idempotency_key',
    ];

    protected $casts = [
        'amount' => 'integer',
        'created_at' => 'datetime',
    ];

    public function sourceWallet()
    {
        return $this->belongsTo(Wallet::class, 'source_wallet_id');
    }

    public function targetWallet()
    {
        return $this->belongsTo(Wallet::class, 'target_wallet_id');
    }
}
