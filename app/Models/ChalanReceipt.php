<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChalanReceipt extends Model
{
    use HasFactory;

    protected $table = 'chalan_receipt'; // Optional if table name follows Laravel naming

    protected $fillable = [
        'id',
        'chalan_no',
        'date',
        'from_branch',
        'to_branch',
        'user_id',
        'total_amount',
    ];

    // Example relationships (optional)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch');
    }
    public function stocks()
    {
        return $this->hasMany(Stock::class, 'chalan_id', 'id');
    }
}