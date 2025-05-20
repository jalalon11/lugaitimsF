<?php

namespace App\Http\Controllers;

use App\Models\RequestingItems;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Movements;
class RequestingItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.requestingItems');
    }
    public function requesteditems_report($dateRequest, $user_id)
    {
        // $data = DB::select('select distinct movements.status as req_status, items.*, supplier_items.*, suppliers.*, movements.*, users.*, users.id as purchaser_id, positions.*, departments.*
        //                         from positions, departments, users, movements, items, supplier_items, suppliers
        //                         where departments.id = users.department_id
        //                         and positions.id = users.position_id
        //                         and items.id = supplier_items.item_id
        //                         and suppliers.id = supplier_items.supplier_id
        //                         and supplier_items.id = movements.supplieritem_id
        //                         and movements.user_id = users.id
        //                         and movements.user_id = '.$user_id.' and date_format(movements.created_at, "%m-%d-%Y") = "'.$dateRequest.'" and (movements.type = 3 || movements.type = 7) order by movements.created_at desc ');
        $data = DB::select('SELECT DISTINCT
                                movements.status AS req_status,
                                items.*,
                                supplier_items.*,
                                suppliers.*,
                                movements.*,
                                users.*,
                                users.id AS purchaser_id,
                                positions.*
                            FROM movements
                            LEFT JOIN users ON users.id = movements.user_id
                            LEFT JOIN positions ON users.position_id = positions.id
                            LEFT JOIN supplier_items ON supplier_items.id = movements.supplieritem_id
                            LEFT JOIN suppliers ON suppliers.id = supplier_items.supplier_id
                            LEFT JOIN items ON items.id = supplier_items.item_id
                            WHERE movements.user_id = '.$user_id.'
                                AND date_format(movements.created_at, "%m-%d-%Y") = "'.$dateRequest.'"
                                AND movements.totalReleased <> 0
                            ORDER BY movements.created_at DESC
                               ');

                                return view('reports.requestingitems', compact('data'));
    }
    public function datatable()
    {
        return Datatables::of($this->get())
                ->addColumn('action', function($data){
                    $html = "";
                    if($data['notification'] == 1)
                    $html = "<button class = 'btn btn-warning btn-sm btn-flat view' data-user_id = ".$data['purchaser_id']." data-date = ".$data['dateRequest']." /><i class = 'fas fa-eye'>".$data['notification']."</i>";
                    if($data['notification'] == 0)
                    $html = "<button class = 'btn btn-primary btn-sm btn-flat view' data-user_id = ".$data['purchaser_id']." data-date = ".$data['dateRequest']." /><i class = 'fas fa-eye'>".$data['notification']."</i>";

                    return $html;
                })
                ->addColumn('dateRequest', function($data){
                    // return date('m-d-Y', strtotime($data['dateRequest']));
                    return $data['dateRequest'];
                })
                ->rawColumns(['action', 'status', 'dateRequest'])
                ->make(true);
    }
    public function savePartial(Request $request)
    {
        $releasedItems = $request->releasedItems;
        for($i = 0; $i<count($releasedItems); $i++)
        {
            $movement = Movements::find($releasedItems[$i]['movement_id']);
            $totalReleased = $movement->totalReleased + (int) $releasedItems[$i]['totalReleased'];


            if($totalReleased == $movement->qty)
            {
                $movement->type = 3; //Fully Released
                $movement->notification = 0;
            }
            if($totalReleased < $movement->qty)
            {
                $movement->type = 7;//Partially Released
                $movement->notification = 1;
            }
            $movement->totalReleased = $totalReleased;
            $movement->dateReleased = Carbon::now();
            $movement->update();
        }
        return response()->json([
            'status'=>true,
            'message'=> 'Requested Item/s has been successfully processed!'
        ]);
    }
    public function realtime_notification()
    {
        $notif = Movements::where(['notification'=>1, 'type'=>1])->count();
        $lowstock = DB::select("SELECT supplier_items.id as lowstocks, items.*, supplier_items.* from supplier_items, items where items.id = supplier_items.item_id and supplier_items.stock <=5 order by supplier_items.updated_at desc");
        $years = DB::select("SELECT TIMESTAMPDIFF(YEAR, supplier_items.date, CURDATE())  AS age, id, no_ofYears FROM supplier_items");
        foreach($years as $y)
        {
            if($y->age > 0)
            {
                if($y->age >= $y->no_ofYears)
                {
                    DB::table('supplier_items')->where('id', $y->id)->update(array('status'=>0));

                    // Check if movements exist for this item and update them
                    if (Movements::where('supplieritem_id', $y->id)->exists()) {
                        DB::table('movements')->where('supplieritem_id', $y->id)->update(array('dateWasted'=>Carbon::now()));
                    }
                }
            }
        }
        $data = ['notif'=>$notif, 'lowstock'=>count($lowstock), 'lowstocks'=>$lowstock];
        return response()->json($data);
    }
    public function resetNotification(Request $request)
    {
        $result = DB::select('select * from movements where date_format(movements.created_at, "%m-%d-%Y") = "'.$request->dateRequest.'" and notification = 1 and user_id = '.$request->user_id.'');
        foreach($result as $res)
        {
            if($res->type != 7)
            {
                $movement = Movements::find($res->id);
                $movement->notification = 0;
                $movement->update();
            }
        }
        return response()->json([
            'data'=>$result,
            'status'=>true,
        ]);
    }
    public function create()
    {
        //
    }
    public function store(Request $request)
    {
        //
    }
    public function show(RequestingItems $requestingItems)
    {
        //
    }
    public function get_requestingItemsData()
    {
        $sql = DB::select('select distinct date_format(movements.created_at, "%m-%d-%Y") as dateRequest, user_id from movements order by date_format(movements.created_at, "%m-%d-%Y") desc');
        return $sql;
    }
    public function get_allUserInfo($user_id)
    {
        $sql = DB::select('select users.*, users.id as purchaser_id, positions.*, departments.*
                        from positions, departments, users
                        where departments.id = users.department_id
                        and positions.id = users.position_id
                        and users.id = '.$user_id.'');
        return $sql;
    }
    public function get()
    {
        $data = [];
        foreach($this->get_requestingItemsData() as $req)
        {
            $result = DB::select('select * from movements where date_format(movements.created_at, "%m-%d-%Y") = "'.$req->dateRequest.'" and user_id = '.$req->user_id.'');
            $notification = 0;
            foreach($result as $notif)
            {
                if($notif->notification == 1) $notification = 1;
            }
            $userinfo = $this->get_allUserInfo($req->user_id);
            $data[] = [
              'purchaser_id'=>$req->user_id,
              'dateRequest'=>$req->dateRequest,
              'fullname'=>$userinfo[0]->fullname,
              'notification'=>$notification,
              'department_name'=>$userinfo[0]->department_name,
            ];
        }
        return $data;
    }
    public function get_purchaserRequest(Request $request, $user_id)
    {
        $data = DB::select('select distinct movements.id as movement_id, movements.status as req_status, items.*, supplier_items.*, suppliers.*, movements.*, users.*, users.id as purchaser_id, positions.*, departments.*, date_format(movements.created_at, "%m-%d-%Y %H:%m:%s") as dateTransact
                                from positions, departments, movements, users, items, supplier_items, suppliers
                                where departments.id = users.department_id
                                and positions.id = users.position_id
                                and items.id = supplier_items.item_id
                                and suppliers.id = supplier_items.supplier_id
                                and supplier_items.id = movements.supplieritem_id
                                and users.id = movements.user_id
                                and movements.user_id = '.$user_id.' and date_format(movements.created_at, "%m-%d-%Y") = "'.$request->dateRequest.'" order by movements.created_at desc');
        return response()->json($data);
    }
    public function get_current()
    {
        $data = DB::select('select distinct users.*, users.id as purchaser_id, movements.id as req_id, date_format(movements.created_at, "%m-%d-%Y") as date, departments.*
                            from users, movements, departments
                            where users.id = movements.user_id
                            and departments.id = users.department_id
                            order by movements.created_at asc');

        return $data;
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RequestingItems $requestingItems)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RequestingItems $requestingItems)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RequestingItems $requestingItems)
    {
        //
    }
}
