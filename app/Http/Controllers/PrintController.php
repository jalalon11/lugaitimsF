<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Models\ItemCategory;
use App\Models\Movements;
use Carbon\Carbon;
class PrintController extends Controller
{
    public function inspectionReport($item_id)
    {
        return view('pages.inspection');
    }
    public function filterReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'itemtype'=>'required',
            'datefrom'=>'required',
            '_supplier'=>'required',
        ]);
        $status = 0; $message = ""; $url = "";
        if($validator->fails())
            $message = $validator->messages();
        else
        {
            $datefrom = date('Y-m-d', strtotime($request->datefrom));
            $data = $this->report_data($request->_supplier, $datefrom, $request->itemtype);
         
            if(empty($data)) 
            {
                $message = "No transaction on this date.";
                $status = 2;
            }
            else
            {
                $array = [
                    'datefrom'=>$datefrom,
                    'itemtype'=>$request->itemtype,
                    '_supplier'=>$request->_supplier,
                ];
                $array = serialize($array);
                $url = "/print/filter/report/page/".$array."";
                $status = 1;
            }
        }
        return response()->json([
            'status'=>$status,
            'messages'=>$message,
            'url'=>$url,
        ]);
    }
    public function itemprofile($supplieritem_id)
    {
        $sql = DB::select('SELECT TIMESTAMPDIFF(YEAR,supplier_items.date, CURDATE())  AS age, suppliers.contact_number as supp_contactNo, suppliers.id as supplier_id, items.*, itemcategories.*, suppliers.*, supplier_items.*, itemcategories.id as itemcategory_id, items.id as item_id, supplier_items.id as supplieritem_id, date_format(supplier_items.date, "%m-%d-%Y")  as date
        FROM items, suppliers, supplier_items, itemcategories
        WHERE itemcategories.id = supplier_items.category_id 
        AND items.id = supplier_items.item_id
        AND suppliers.id = supplier_items.supplier_id
        AND supplier_items.id = '.$supplieritem_id.'');

        return view('reports.itemprofile', compact('sql'));
    }
    public function filterPage(Request $request, $data)
    {
        $unserializeArray = unserialize($data);
        $data = $this->report_data($unserializeArray['_supplier'], $unserializeArray['datefrom'], $unserializeArray['itemtype']);
        $userinfo = DB::select('select users.*, users.id as purchaser_id, positions.*, departments.*
                                from positions, departments, users
                                where departments.id = users.department_id
                                and positions.id = users.position_id
                                and users.id = '.$data[0]->user_id.'');

    
        return view('pages.requisition', compact('data', 'userinfo'));
    }
    public function report_data($supplier, $datefrom, $itemtype)
    {
        // $sql = DB::select('SELECT suppliers.*, items.*, movements.*, users.*, departments.*, supplier_items.*
        //                 FROM suppliers, items, supplier_items, movements, users, departments
        //                 WHERE suppliers.id = supplier_items.supplier_id
        //                 AND items.id = supplier_items.item_id
        //                 AND supplier_items.id = movements.supplieritem_id
        //                 AND movements.user_id = users.id
        //                 AND departments.id = users.department_id
        //                 AND users.id = '.$supplier.' AND DATE(movements.created_at) = "'.$datefrom.'" AND movements.type = '.$itemtype.'');
        // return $sql;

        $whereClause = " WHERE  users.id = ? 
                        AND DATE(m.created_at) = ?";
        $whereValues = [$supplier, $datefrom];

        if($itemtype == 5) {
            $whereClause = " WHERE users.id = ? 
                            AND DATE(m.created_at) = ?
                            AND m.type = ?";
            $whereValues = [$supplier, $datefrom, $itemtype];
        }

        $sql = DB::select('SELECT suppliers.*, items.*, m.*, users.*, departments.*, supplier_items.*
                            FROM movements m
                            LEFT JOIN users on users.id = m.user_id
                            LEFT JOIN supplier_items on supplier_items.id = m.supplieritem_id
                            LEFT JOIN items on items.id = supplier_items.item_id
                            LEFT JOIN suppliers on suppliers.id = supplier_items.supplier_id
                            LEFT JOIN itemcategories on itemcategories.id = supplier_items.category_id
                            LEFT JOIN departments on departments.id = users.id
                            '.$whereClause, $whereValues);
        return $sql;
    } 
    // public function report_data($supplier, $datefrom, $itemtype)
    // {
    //     $sql = DB::select('SELECT suppliers.*, items.*, movements.*, users.*, departments.*, supplier_items.*
    //                     FROM suppliers, items, supplier_items, movements, users, departments
    //                     WHERE suppliers.id = supplier_items.supplier_id
    //                     AND items.id = supplier_items.item_id
    //                     AND supplier_items.id = movements.supplieritem_id
    //                     AND movements.user_id = users.id
    //                     AND departments.id = users.department_id
    //                     AND movements.type > 1 AND movements.type < 5
    //                     AND users.id = '.$supplier.' AND DATE(movements.created_at) = "'.$datefrom.'" AND movements.type = '.$itemtype.'');
    //     return $sql;
    // } 
    public function get_report($month, $year, $category, $week_number)
    {
        $m = $month[0];
        $sql = $this->get_monthlyFromDB($month, $year, $category);
      
        if($week_number != 0)
            $sql = $this->get_weeklyFromDB($year, $category, $month, $week_number);
        else
        {
            if($month == "Q1" || $month == "Q2" || $month == "Q3" || $month == "Q4")
            {
                $month = $month[1];
                $sql = $this->get_quarterlyFromDB($month, $year, $category);
            }
            if($month === "N" AND $year !== "")
                $sql = $this->get_yearlyFromDB($year, $category);
        }
        return response()->json($sql);
    }
    public function get_reportPrint($month, $year, $category, $week_number)
    {
        $m = $month[0];
        $data = $this->get_monthlyFromDB($month, $year, $category);
       
        if($week_number != 0)
            $data = $this->get_weeklyFromDB($year, $category, $month, $week_number);
        else
        {
            if($month == "Q1" || $month == "Q2" || $month == "Q3" || $month == "Q4")
            {
                $quarter = $month;
                $month = $month[1];
                $data = $this->get_quarterlyFromDB($month, $year, $category);
                $month = $quarter;
            } 
                
            if($month === "N" AND $year !== "")
                $data = $this->get_yearlyFromDB($year, $category);
            if($month == 1) $month = "JANUARY";
            if($month == 2) $month = "FEBRUARY";
            if($month == 3) $month = "MARCH";
            if($month == 4) $month = "APRIL";
            if($month == 5) $month = "MAY";
            if($month == 6) $month = "JUNE";
            if($month == 7) $month = "JULY";
            if($month == 8) $month = "AUGUST";
            if($month == 9) $month = "SEPTEMBER";
            if($month == 10) $month = "OCTOBER";
            if($month == 11) $month = "NOVEMBER";
            if($month == 12) $month = "DECEMBER";
        }
         
        $category = ItemCategory::where('id', $category)->get();
        return view('reports.monthlyreport', compact('data', 'month', 'year', 'category', 'week_number'));
    }
    public function get_monthlyFromDB($month, $year, $category)
    {
        // $sql = DB::select('SELECT distinct date_format(movements.created_at, "%m-%d-%Y") as dateRequest, movements.*, itemcategories.*, items.*, users.*, departments.*, supplier_items.*, suppliers.*
        //                         FROM suppliers, items, itemcategories, supplier_items, movements, users, departments
        //                         WHERE suppliers.id = supplier_items.supplier_id
        //                         AND itemcategories.id = supplier_items.category_id
        //                         AND items.id = supplier_items.item_id
        //                         AND supplier_items.id = movements.supplieritem_id
        //                         AND departments.id = users.department_id
        //                         AND movements.user_id = users.id
        //                         AND movements.type > 1 AND movements.type < 5
        //                         AND MONTH(movements.created_at) = "'.$month.'" AND YEAR(movements.created_at) =  "'.$year.'" AND itemcategories.id = '.$category.' AND supplier_items.status != 0');
        // return $sql;
        $sql = DB::select('SELECT distinct date_format(m.created_at, "%m-%d-%Y") as dateRequest, m.*, itemcategories.*, items.*, users.*, departments.*, supplier_items.*, suppliers.*
                        FROM movements m
                        LEFT JOIN users on users.id = m.user_id
                        LEFT JOIN supplier_items on supplier_items.id = m.supplieritem_id
                        LEFT JOIN items on items.id = supplier_items.item_id
                        LEFT JOIN suppliers on suppliers.id = supplier_items.supplier_id
                        LEFT JOIN itemcategories on itemcategories.id = supplier_items.category_id
                        LEFT JOIN departments on departments.id = users.id
                        WHERE MONTH(m.created_at) = "'.$month.'" AND YEAR(m.created_at) =  "'.$year.'" AND itemcategories.id = '.$category.' AND supplier_items.status != 0');
    return $sql;    
}
    public function get_yearlyFromDB($year, $category)
    {
        $sql = DB::select('SELECT distinct date_format(m.created_at, "%m-%d-%Y") as dateRequest, m.*, itemcategories.*, items.*, users.*, departments.*, supplier_items.*, suppliers.*
                        FROM movements m
                        LEFT JOIN users on users.id = m.user_id
                        LEFT JOIN supplier_items on supplier_items.id = m.supplieritem_id
                        LEFT JOIN items on items.id = supplier_items.item_id
                        LEFT JOIN suppliers on suppliers.id = supplier_items.supplier_id
                        LEFT JOIN itemcategories on itemcategories.id = supplier_items.category_id
                        LEFT JOIN departments on departments.id = users.id
                        WHERE YEAR(movements.created_at) =  "'.$year.'" AND itemcategories.id = '.$category.' AND supplier_items.status != 0');
        return $sql;
    }
    public function get_quarterlyFromDB($quarter, $year, $category)
    {
        $sql = DB::select('SELECT distinct date_format(m.created_at, "%m-%d-%Y") as dateRequest, m.*, itemcategories.*, items.*, users.*, departments.*, supplier_items.*, suppliers.*
                        FROM movements m
                        LEFT JOIN users on users.id = m.user_id
                        LEFT JOIN supplier_items on supplier_items.id = m.supplieritem_id
                        LEFT JOIN items on items.id = supplier_items.item_id
                        LEFT JOIN suppliers on suppliers.id = supplier_items.supplier_id
                        LEFT JOIN itemcategories on itemcategories.id = supplier_items.category_id
                        LEFT JOIN departments on departments.id = users.id
                        WHERE QUARTER(m.created_at) = "'.$quarter.'" AND YEAR(m.created_at) =  "'.$year.'" AND itemcategories.id = '.$category.' AND supplier_items.status != 0');
        return $sql;
    }
    public function get_weeklyFromDB($year, $category, $month, $week_number)
    {
        $firstDayOfMonth = Carbon::createFromDate($year, $month, 1);
        $startOfWeek = $firstDayOfMonth->copy()->addWeeks($week_number -1)->startOfWeek();
        $endOfWeek = $startOfWeek->copy()->endOfWeek();

        $sql = Movements::select(
                                    DB::raw('DISTINCT DATE_FORMAT(movements.created_at, "%m-%d-%Y") AS dateRequest'),
                                    'movements.*',
                                    'itemcategories.*',
                                    'items.*',
                                    'users.*',
                                    'departments.*',
                                    'supplier_items.*',
                                    'suppliers.*'
                                )
                                ->join('supplier_items', 'supplier_items.id', '=', 'movements.supplieritem_id')
                                ->join('suppliers', 'suppliers.id', '=', 'supplier_items.supplier_id')
                                ->join('items', 'items.id', '=', 'supplier_items.item_id')
                                ->join('itemcategories', 'itemcategories.id', '=', 'supplier_items.category_id')
                                ->join('users', 'users.id', '=', 'movements.user_id')
                                ->join('departments', 'departments.id', '=', 'users.department_id')
                                ->whereBetween('movements.created_at', [$startOfWeek, $endOfWeek])
                                ->where('itemcategories.id', $category)
                                ->where('supplier_items.status', '!=', 0)
                                ->get();
    
        return $sql;
    }
    public function monthlyreport_page()
    {
        return view('pages.monthlyreport');
    }
}
