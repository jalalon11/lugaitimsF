<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Movements;
use App\Models\ItemCategory;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get counts directly from the database tables instead of using models
        $itemCount = DB::table('supplier_items')->count();
        $purchasedCount = DB::table('supplier_items')->where('status', 1)->count();
        $deliveredCount = DB::table('movements')->where('type', 3)->count();
        $requisitionCount = DB::table('movements')->where('type', 1)->count();

        $data = [
            'itemCount' => $itemCount,
            'no_ofPurchased' => $purchasedCount,
            'no_ofDelivered' => $deliveredCount,
            'no_ofRequisition' => $requisitionCount,
        ];

        $years_ofPurchasedLabel = [];
        $values_ofPurchased = [];
        $years = DB::select('select supplier_items.id, items.item, suppliers.name from items, supplier_items, suppliers where items.id = supplier_items.item_id and suppliers.id = supplier_items.supplier_id');
        
        foreach($years as $year)
        {
            $years_ofPurchasedLabel[] = $year->item." - ".$year->name;
            $values = DB::select('select count(movements.user_id) as total
                                from users, movements, supplier_items, items
                                where items.id = supplier_items.item_id
                                and supplier_items.id = movements.supplieritem_id
                                and movements.user_id = users.id
                                and supplier_items.id = '.$year->id.' order by total desc');

            $values_ofPurchased[] = $values[0]->total;
        }

        $years_ofReleasedLabel = [];
        $values_ofReleased = [];
        $years_r = DB::select('SELECT DISTINCT YEAR(movements.created_at) as years, QUARTER(movements.created_at) as quarters
            FROM movements ORDER BY YEAR(movements.created_at) asc');

        foreach($years_r as $year)
        {
            $amount_total = DB::select('select SUM(supplier_items.cost*movements.totalReleased) as accumulated from supplier_items, movements where supplier_items.id = movements.supplieritem_id and QUARTER(movements.created_at) = "'.$year->quarters.'" and YEAR(movements.created_at) = "'.$year->years.'"');
            $acc = $amount_total[0]->accumulated;
            if($acc === null)
            {
               $acc = 0;
            }
            $total = " P ".number_format((float)$acc, 2, '.', ',');
            $years_ofReleasedLabel[] = $year->years." - Q".$year->quarters." : ".$total;
            $values = DB::select('SELECT count(supplier_items.id) as total
            FROM items
            INNER JOIN supplier_items on supplier_items.item_id = items.id
            INNER JOIN movements on movements.supplieritem_id = supplier_items.id WHERE QUARTER(movements.created_at) = "'.$year->quarters.'" and YEAR(movements.created_at) = "'.$year->years.'" ');
            
            $values_ofReleased[] = $values[0]->total;
        }

        return view('pages.home', compact('data', 'years_ofPurchasedLabel', 'values_ofPurchased', 'years_ofReleasedLabel', 'values_ofReleased'));       
    }

    public function get_categorizedChart(Request $request)
    {
        if($request->ajax())
        {
            $category = DB::table('itemcategories')->where('category', $request->category)->first();
            
            if ($category) {
                $years = DB::select('select supplier_items.id, items.item, suppliers.name from items, supplier_items, suppliers where items.id = supplier_items.item_id and suppliers.id = supplier_items.supplier_id and supplier_items.category_id = '.$category->id.'');
                $years_ofPurchasedLabel = [];
                $values_ofPurchased = [];
                
                foreach($years as $year)
                {
                    $years_ofPurchasedLabel[] = $year->item." - ".$year->name;
                    $values = DB::select('select distinct count(users.id) as total
                                        from users, movements, supplier_items, items
                                        where items.id = supplier_items.item_id
                                        and supplier_items.id = movements.supplieritem_id
                                        and movements.user_id = users.id
                                        and supplier_items.id = '.$year->id.' and supplier_items.category_id = '.$category->id.' order by total desc');

                    $values_ofPurchased[] = $values[0]->total;
                }
                
                return response()->json([
                    'labels' => $years_ofPurchasedLabel,
                    'values' => $values_ofPurchased,
                ]);
            }
            
            return response()->json([
                'labels' => [],
                'values' => [],
            ]);
        }
    }
}
