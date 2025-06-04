<?php

namespace App\Http\Controllers;

use App\Models\item;
use App\Models\Supplier_Items;
use App\Models\Movements;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\RequestingItems;
use Carbon\Carbon;
use Response;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       return view('pages.items');
    }
    public function get_myRequestedItems()
    {
       return Datatables::of($this->get_requestedItems())
                ->addColumn('status', function($row) {
                    $html = "<span class = 'badge badge-danger'>".$row->totalCancelled." CANCELLED</span>";
                    if($row->type == 1) {
                        $html = "<span class = 'badge badge-primary'>REQUESTING</span>";
                    }
                    elseif($row->type == 3) {
                        $html = "<span class = 'badge badge-success'>".$row->totalReleased." FULLY RELEASED</span>
                                <span class = 'badge badge-danger'>".$row->totalCancelled." CANCELLED</span>";
                    }
                    elseif($row->type == 5) {
                        $html = "<span class = 'badge badge-success'>".$row->totalReleased." RELEASED</span>
                                <span class = 'badge badge-danger'>".$row->totalCancelled." CANCELLED</span>";
                    }
                    elseif($row->type == 7) {
                        $html = "<span class = 'badge badge-warning'>".$row->totalReleased." PARTIALLY RELEASED</span>
                                <span class = 'badge badge-danger'>".$row->totalCancelled." CANCELLED</span>";
                    }
                    return $html;
                })->rawColumns(['status'])->make(true);
    }
    public function get_allItemsInDatatables()
    {
        return DataTables::of($this->get_allItems())
                ->addColumn('type', function($row){
                    $html = "";
                    if($row->status==0) $html = "<span class = 'badge badge-warning'>WASTED</span>";
                    if($row->status==1) $html = "<span class = 'badge badge-primary'>ACTIVE</span>";
                    return $html;
                })
                ->addColumn('checkboxes', function($row){
                    $html = "<input class = 'checkboxes' style = 'width: 20px; height: 20px;' type = 'checkbox' name = 'itemCheck' id = 'itemCheck' data-supplieritem_id=".$row->supplieritem_id." value = '".$row->supplieritem_id."' />";
                    return $html;
                })
                ->addColumn('cost', function($row){
                    return $html = "&#8369;&nbsp;".number_format((float)$row->cost, 2, '.', ',');
                })
                ->addColumn('totalCost', function($row){
                    return $html = "&#8369;&nbsp;".number_format((float)$row->totalCost, 2, '.', ',');
                })
                ->addColumn('stock', function($row){
                    if($row->stock <= 5 && $row->stock >= 1)
                        return $html = "<span class='badge badge-warning' style='color: black;'>".$row->stock."</span>";
                    if($row->stock == 0)
                        return $html = "<span class = 'badge badge-danger'>".$row->stock."</span>";
                    else
                        return $html = $row->stock;
                })
                ->addColumn('actions', function($row){
                    $html = "<td style = 'display: block; margin: auto; text-align:center'>";
                    $html = '<button type = "button" data-id = '.$row->supplieritem_id.' class = "btn  btn-flat btn-outline btn-primary btn-sm edit"><i class = "fas fa-xs fa-edit"></i>&nbsp;</button>&nbsp;';
                    $html .= '<button type = "button" data-id = '.$row->supplieritem_id.' class = "btn  btn-flat btn-outline btn-secondary btn-sm view"><i class = "fas fa-xs fa-eye"></i>&nbsp;</button>&nbsp;';
                    $html .= "</td>";
                    return $html;
                })
                ->rawColumns(['type', 'checkboxes','actions', 'cost', 'totalCost', 'stock'])
                ->make(true);
    }
    public function get_allItems()
    {
        $sql = DB::select('SELECT date_format(supplier_items.date, "%m-%d-%Y")  as dateT, supplier_items.id as supplieritem_id, items.*, itemcategories.*, suppliers.*, supplier_items.*
                        FROM items, suppliers, supplier_items, itemcategories
                        WHERE itemcategories.id = supplier_items.category_id
                        AND items.id = supplier_items.item_id
                        AND suppliers.id = supplier_items.supplier_id
                        ORDER BY date_format(supplier_items.date, "%m-%d-%Y") desc');
        return $sql;
    }
    public function get_allSupplierItems()
    {
        $sql = DB::select('SELECT date_format(supplier_items.date, "%m-%d-%Y")  as date, supplier_items.id as supplieritem_id, items.*, itemcategories.*, suppliers.*, supplier_items.*
                        FROM items, suppliers, supplier_items, itemcategories
                        WHERE itemcategories.id = supplier_items.category_id
                        AND items.id = supplier_items.item_id
                        AND suppliers.id = supplier_items.supplier_id
                        AND supplier_items.status = 1');
        return response()->json($sql);
    }
    public function get_allUsersByJson()
    {
        $sql = DB::select('select users.*, users.id as user_id, positions.*, departments.*
                            from positions, departments, users
                            where departments.id = users.department_id
                            and positions.id = users.position_id');
       return Response::json($sql);
    }
    public function supplier_allItems()
    {
        $sql = DB::select('SELECT supplier_items.id as supplieritem_id, items.*, itemcategories.*, suppliers.*, supplier_items.*
                        FROM items, suppliers, supplier_items, itemcategories
                        WHERE itemcategories.id = supplier_items.category_id
                        AND items.id = supplier_items.item_id
                        AND suppliers.id = supplier_items.supplier_id
                        AND supplier_items.status = 1');
        return response()->json($sql);
    }
    public function get_allItemOnly()
    {
        $data = Item::select('item')->distinct()->get();
        return response()->json($data);
    }
    public function create()
    {
        //
    }

    public function saveItem(Request $request)
    {
        return response()->json($request->all());
    }
    public function store(Request $request)
    {
        $messages = []; $status="";
        $validatedData = [
            'item'=>'required',
            'date'=>'required',
            'unit'=>'required',
            'brand'=>'required',
            'itemcategory_id'=>'required',
            'supplier'=>'required',
            'quantity'=>'required',
            'cost'=>'required',
            'stock'=>'required',
            'totalCost'=>'required',
            'serialnumber'=>'nullable|string',
            'modelnumber'=>'nullable|string',
            'remarks'=>'nullable|string|min:6',
            'no_ofYears'=>'nullable|min:1',
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048|unique:items,image',
        ];
        $validator = Validator::make($request->all(), $validatedData);
        if($validator->fails())
        {
            $messages = $validator->messages();
        }
        else
        {
            $item = Item::where([
                'item'=>$request->item,
                'brand'=>$request->brand,
            ])->first();


            $image_name = "";
            $image = "";
            $temp = "";
            $item_id = "";
            $hasNewImage = false;

            if($request->hasFile('image'))
            {
                $image_name = $request->image->getClientOriginalName();
                $request->file('image')->storeAs('upload_images', $image_name, 'public');
                $hasNewImage = true;
                // $request->image->move(public_path('upload_images'), $image_name);
            }

            if(!empty($request->item_id))
            {
                $toUpdate = [];
                if(!$hasNewImage)
                {
                    // No new image uploaded, keep existing image
                    $toUpdate = [
                        'item'=>strtoupper($request->item),
                        'unit'=>strtoupper($request->unit),
                        'brand'=>strtoupper($request->brand),
                    ];

                    // If there's a current_image value, preserve it
                    if($request->current_image && $request->current_image !== '') {
                        $toUpdate['image'] = $request->current_image;
                    }
                }
                else
                {
                    // New image uploaded, update with new image
                    $toUpdate = [
                        'item'=>strtoupper($request->item),
                        'unit'=>strtoupper($request->unit),
                        'brand'=>strtoupper($request->brand),
                        'image'=>$image_name,
                    ];
                }

                $u_item = DB::table('items')
                        ->where('id',$request->item_id)
                        ->update($toUpdate);
                $item_id = $request->item_id;

            }
            else
            {
                if($item === null)
                {
                    $item = new Item;
                    $item->item = strtoupper($request->item);
                    $item->unit = strtoupper($request->unit);
                    $item->brand= strtoupper($request->brand);
                    $item->image= $image_name;
                    $item->save();
                    $item_id = $item->id;
                }
                else {
                    $item_id = $item->id;
                    $temp = 1;
                    $status = false;
                    $messages = "The item already exists!";
                }
            }

            if(is_null($request->item_id))
            {
                $isExists = Supplier_Items::where([
                    'item_id'=>$item_id,
                    'category_id'=>$request->itemcategory_id,
                    'supplier_id'=>$request->supplier,
                ])->exists();

                if($isExists)
                {
                    $item_id = $item->id;
                    $temp = 2;
                    $status = false;
                    $messages = "The item already exists!";
                }
            }

            $supplieritem = Supplier_Items::updateOrCreate(['id'=>$request->supplieritem_id], [
                'supplier_id' => $request->supplier,
                'item_id' => $item_id,
                'date'=>$request->date,
                'serialnumber' => $request->serialnumber,
                'modelnumber' => $request->modelnumber,
                'stock' => $request->stock,
                'no_ofYears' => $request->no_ofYears,
                'category_id' => $request->itemcategory_id,
                'quantity' => $request->quantity,
                'cost' => $request->cost,
                'totalCost' => $request->totalCost,
                'remarks' => $request->remarks,
                'status'=>1,
            ]);
            if($temp == 1)
            {
                $status = false;
                $messages = ['item'=>$messages,'brand'=>$messages];
            }
            if($temp == 2)
            {
                $status = false;
                $messages = ['item'=>$messages,
                            'brand'=>$messages,
                            'itemcategory_id'=>$messages,
                            'supplier'=>$messages];
            }
            else
            {
                $messages = "Item has been successfully saved!";
                $status = true;
            }
        }

        return response()->json([
            'status'=>$status,
            'messages'=>$messages,
        ]);
    }
    public function get_allUnits()
    {
        return response()->json(Item::select('unit')->distinct()->get());
    }
    public function get_allBrands()
    {
        return response()->json(Item::select('brand')->distinct()->get());
    }
    /**
     * Display the specified resource.
     */
    public function show(item $item)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($item_id)
    {
        $sql = DB::select('SELECT TIMESTAMPDIFF(YEAR,supplier_items.date, CURDATE())  AS age, suppliers.contact_number as supp_contactNo, suppliers.id as supplier_id, items.*, itemcategories.*, suppliers.*, supplier_items.*, itemcategories.id as itemcategory_id, items.id as item_id, supplier_items.id as supplieritem_id, date_format(supplier_items.date, "%m-%d-%Y")  as date
                        FROM items, suppliers, supplier_items, itemcategories
                        WHERE itemcategories.id = supplier_items.category_id
                        AND items.id = supplier_items.item_id
                        AND suppliers.id = supplier_items.supplier_id
                        AND supplier_items.id = '.$item_id.'');

        return response()->json($sql);
    }

    public function purchaserEdit($item_id)
    {
        $sql = DB::select('SELECT date_format(supplier_items.created_at, "%m-%d-%Y")  as transactedOn, suppliers.contact_number as supp_contactNo, suppliers.id as supplier_id, items.*, itemcategories.*, suppliers.*, supplier_items.*, itemcategories.id as itemcategory_id, items.id as item_id, supplier_items.id as supplieritem_id
                        FROM items, suppliers, supplier_items, itemcategories
                        WHERE itemcategories.id = supplier_items.category_id
                        AND items.id = supplier_items.item_id
                        AND suppliers.id = supplier_items.supplier_id
                        AND supplier_items.id = '.$item_id.'');

        $data = [
            'item'=>$sql,
            // 'requestItem'=>$requestItem,
        ];
        return response()->json($data);
    }

    public function get_RequestedItems()
    {
        $user_id = Auth::user()->id;

        $requestItem = DB::select('select items.*, supplier_items.*, suppliers.*, movements.*, users.*, users.id as purchaser_id, positions.*, departments.*, date_format(movements.created_at, "%m-%d-%Y")  as dateTransact
                                from positions, departments, users, movements, items, supplier_items, suppliers
                                where departments.id = users.department_id
                                and positions.id = users.position_id
                                and items.id = supplier_items.item_id
                                and suppliers.id = supplier_items.supplier_id
                                and supplier_items.id = movements.supplieritem_id
                                and users.id = movements.user_id
                                and users.id = '.$user_id.'');

        return $requestItem;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, item $item)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(item $item)
    {
        $status=false; $message = "";
        if($item !== null)
        {
            $item->delete();
            $status = true;
            $message = "Item has been successfully removed";
        }
        else $message = "Cannot find id or cannot be removed.";

        return response()->json([
            'status'=>$status,
            'message'=>$message,
        ]);
    }
    public function save_cart(Request $request)
    {
        $selectedItems = $request->selecteditems;
        for($i = 0; $i<count($selectedItems); $i++)
        {
            $supplieritem = Supplier_Items::find($selectedItems[$i]['supplieritem_id']);
            $supplieritem->stock = $supplieritem->stock-$selectedItems[$i]['itemQty'];
            $supplieritem->update();

            Movements::create([
                'supplieritem_id'=>$selectedItems[$i]['supplieritem_id'],
                'user_id'=>Auth::user()->id,
                'qty'=>$selectedItems[$i]['itemQty'],
                'notification'=>1,
                'status'=>1,
            ]);
        }

        return response()->json([
            'status'=>true,
            'message'=>'Your items has been successfully processed!'
        ]);
    }
}
