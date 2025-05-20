<?php

namespace App\Http\Controllers;

use App\Models\Supplier_Items;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Movements;
use Carbon\Carbon;
class SupplierItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    public function resetStock(Request $request)
    {
        $item_ids = $request->items;
        DB::table('supplier_items')->whereIn('id', $item_ids)->update(array('stock' => 0));
        return response()->json([
            'status'=>true,
            'message'=>'The inventory of chosen items has been successfully replenished.',
        ]);
    }
    public function reTypeItem(Request $request)
    {

        $item_ids = $request->items;
        $req_id = $request->req_id;
        $remarks = "Pending";
        if($request->selected_itemtype === "S1")
        {
            DB::table('supplier_items')->whereIn('id', $item_ids)->update(array('status' => 1));
        }
        if($request->selected_itemtype === "S0")
        {
            DB::table('supplier_items')->whereIn('id', $item_ids)->update(array('status' => 0));
        }
        if($request->selected_itemtype == 2)
        {
            DB::table('movements')->whereIn('id', $item_ids)->update(array('type' => $request->selected_itemtype, 'datePurchased'=>Carbon::now()));
        }
        if($request->selected_itemtype == 3){

            DB::table('movements')->whereIn('id', $item_ids)->update(array('dateReleased'=>Carbon::now(), 'type'=>3));
            // if($request->supplieritem_ids !== null)
            // {
            //     for($i = 0; $i<count($request->supplieritem_ids); $i++)
            //     {
            //         if($request->types[$i] != $request->selected_itemtype)
            //         {
            //             $supplier_item = Supplier_Items::find($request->supplieritem_ids[$i]);
            //             $supplier_item->stock = $supplier_item->stock-$request->qty[$i];
            //             $supplier_item->update();
            //         }
            //     }
            // }
        }
        if($request->selected_itemtype == 4) DB::table('movements')->whereIn('id', $item_ids)->update(array('type' => $request->selected_itemtype,  'dateWasted'=>Carbon::now()));
        if($request->selected_itemtype == 5)
        {
            // DB::table('movements')->whereIn('id', $item_ids)->update(array('type'=>5, 'totalReleased'=>0, 'dateCancelled'=>Carbon::now(), 'reasonforcancel'=>strtoupper($request->reasonforcancel)));
            for($i = 0; $i<count($item_ids); $i++)
            {
                $movement = Movements::find($item_ids[$i]);
                $movement->type = 5;
                $movement->notification = 0;
                $movement->totalCancelled = $request->totalCancelled;
                $movement->reasonforcancel = strtoupper($request->reasonforcancel);
                $movement->dateCancelled = Carbon::now();
                $movement->update();
            }
            if($request->supplieritem_ids !== null)
            {
                for($i = 0; $i<count($request->supplieritem_ids); $i++)
                {
                    if($request->types[$i] != $request->selected_itemtype)
                    {
                        $supplier_item = Supplier_Items::find($request->supplieritem_ids[$i]);
                        $supplier_item->stock = $supplier_item->stock+$request->qty[$i];
                        $supplier_item->update();
                    }
                }
            }
        }
        return response()->json([
            'status'=>true,
            'message'=>'Selected items has been successfully updated.',
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier_Items $supplier_items)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier_Items $supplier_items)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier_Items $supplier_items)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier_Items $supplier_items)
    {
        //
    }
}
