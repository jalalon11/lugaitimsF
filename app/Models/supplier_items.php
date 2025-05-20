<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier_Items extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'supplier_items';

    protected $fillable = ['id', 'supplier_id', 'item_id', 'stock', 'serialnumber', 'modelnumber', 'no_ofYears', 'category_id', 'quantity',
    'cost', 'totalCost','status','type','remarks', 'date',];
}
