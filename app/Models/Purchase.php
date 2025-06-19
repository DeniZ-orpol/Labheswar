<?php

namespace App\Models;

use App\Traits\HasDynamicTable;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasDynamicTable;
    protected $table = 'purchase';

    protected $fillable = [
        'purchase_receipt_id',
        'bill_date',
        'purchase_party_id',
        'bill_no',
        'delivery_date',
        'gst',
        'product_id',
        'product',
        'mrp',
        'box',
        'pcs',
        'free',
        'p_rate',
        'discount',
        'lumpsum',
        'amount',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'delivery_date' => 'date',
        'mrp' => 'decimal:2',
        'box' => 'decimal:2',
        'pcs' => 'decimal:2',
        'free' => 'decimal:2',
        'p_rate' => 'decimal:2',
        'discount' => 'decimal:2',
        'lumpsum' => 'decimal:2',
        'amount' => 'decimal:2',
    ];


    // Relationship with Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    
    // Relationship with Product
    public function purchaseReceipt()
    {
        return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id');
    }

    // Relationship with Purchase Party
    public function purchaseParty()
    {
        return $this->belongsTo(PurchaseParty::class, 'purchase_party_id');
    }

    // Accessor to get total pieces (box * conversion + pcs)
    // public function getTotalPcsAttribute()
    // {
    //     $product = $this->product;
    //     $boxToPcs = $product ? $product->converse_box : 1;
    //     return ($this->box * $boxToPcs) + $this->pcs;
    // }

    // Accessor to calculate amount with discount
    public function getCalculatedAmountAttribute()
    {
        $baseAmount = $this->p_rate * $this->total_pcs;

        // Apply percentage discount
        if ($this->discount > 0) {
            $baseAmount = $baseAmount - ($baseAmount * ($this->discount / 100));
        }

        // Apply lumpsum discount
        if ($this->lumpsum > 0) {
            $baseAmount = $baseAmount - $this->lumpsum;
        }

        // Add GST if applicable
        if ($this->gst > 0) {
            $baseAmount = $baseAmount + ($baseAmount * ($this->gst / 100));
        }

        return round($baseAmount, 2);
    }

    // Scope to filter by date range
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('bill_date', [$startDate, $endDate]);
    }

    // Scope to filter by party
    public function scopeByParty($query, $partyName)
    {
        return $query->where('party_name', 'like', '%' . $partyName . '%');
    }

    // Scope to filter by product
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }
}
