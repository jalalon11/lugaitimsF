<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\Datatables\Datatables;

class ItemcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            Log::info('ItemCategory index method called');
            return Datatables::of($this->get_allData())
                                ->addColumn('actions', function($row){
                                    $html = "<td align='center'>";
                                        $html .= '<button type = "button" data-id = '.$row->id.' class = "btn btn-sm btn-primary edit"><i class = "fas fa-edit"></i>&nbsp;</button>&nbsp;&nbsp;';
                                        if($row->status == 0)
                                            $html .= '<button type = "button" data-id = '.$row->id.' class = "btn btn-sm btn-primary activate"><i class = "fas fa-unlock"></i>&nbsp;</button>&nbsp;&nbsp;';
                                        if($row->status == 1)
                                            $html .= '<button type = "button" data-id = '.$row->id.' class = "btn btn-sm btn-danger deactivate"><i class = "fas fa-lock"></i>&nbsp;</button>&nbsp;&nbsp;';
                                    $html .= "</td>";
                                    return $html;
                                })
                                ->rawColumns(['actions'])
                                ->make(true);
        } catch (\Exception $e) {
            Log::error('ItemCategory index error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
    public function get_categoriesByJson()
    {
        try {
            Log::info('ItemCategory get_categoriesByJson method called');
            return response()->json($this->get_allData());
        } catch (\Exception $e) {
            Log::error('ItemCategory get_categoriesByJson error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
    public function get_allData()
    {
        try {
            Log::info('ItemCategory get_allData method called');
            return ItemCategory::all();
        } catch (\Exception $e) {
            Log::error('ItemCategory get_allData error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
    public function get_allDataByJson()
    {
        try {
            Log::info('ItemCategory get_allDataByJson method called');
            return response()->json($this->get_allData());
        } catch (\Exception $e) {
            Log::error('ItemCategory get_allDataByJson error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    public function countItems()
    {
        $sql = DB::select("SELECT COUNT(supplier_items.id) AS totalItems, category
                            FROM itemcategories, supplier_items
                            WHERE itemcategories.id = supplier_items.category_id
                            GROUP BY itemcategories.category");
        return response()->json($sql);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            Log::info('ItemCategory store method called', ['request_data' => $request->all()]);

            $status = false; $msg = ""; $validatedData = [];

            // Check if category_id is provided and not empty
            if($request->category_id !== null && $request->category_id !== '')
            {
                $validatedData = [
                    'category' => 'required|unique:itemcategories,category,'.$request->category_id.',id',
                ];
            }
            else
            {
                $validatedData = [
                    'category' => 'required|min:2|unique:itemcategories',
                ];
            }

            $validator = Validator::make($request->all(), $validatedData);

            if($validator->fails()) {
                Log::error('ItemCategory validation failed', ['errors' => $validator->errors()]);
                $msg = $validator->messages();
                $status = false;
            } else {
                Log::info('Creating/updating ItemCategory', [
                    'category_id' => $request->category_id,
                    'category' => $request->category
                ]);

                // Use different approach for create vs update
                if($request->category_id !== null && $request->category_id !== '') {
                    // Update existing category
                    $category = ItemCategory::find($request->category_id);
                    if($category) {
                        $category->category = strtoupper($request->category);
                        $category->status = 1;
                        $category->save();
                        $msg = "Category has been successfully updated!";
                    } else {
                        throw new \Exception('Category not found for update');
                    }
                } else {
                    // Create new category
                    $category = new ItemCategory();
                    $category->category = strtoupper($request->category);
                    $category->status = 1;
                    $category->save();
                    $msg = "Category has been successfully created!";
                }

                $status = true;
                Log::info('ItemCategory saved successfully');
            }

            return response()->json(['status'=>$status, 'messages'=>$msg]);
        } catch (\Exception $e) {
            Log::error('ItemCategory store error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'status'=>false,
                'messages'=>'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ItemCategory $itemcategory)
    {
        return response()->json($itemcategory);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ItemCategory $itemcategory)
    {
        $itemcategory->status = 1;
        $itemcategory->update();
        return response()->json(['status'=>true,'messages'=>'Successfully Activated!']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ItemCategory $itemcategory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ItemCategory $itemcategory)
    {
        $itemcategory->status = 0;
        $itemcategory->update();
        return response()->json(['status'=>true,'messages'=>'Successfully Deactivated!']);
    }
}
