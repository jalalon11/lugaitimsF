<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        // Add logging for debugging
        Log::info('Report request received', [
            'month' => $month,
            'year' => $year,
            'category' => $category,
            'week_number' => $week_number
        ]);

        $sql = [];

        if($week_number != 0) {
            // Weekly report
            Log::info('Generating weekly report');
            $sql = $this->get_weeklyFromDB($year, $category, $month, $week_number);
        } else {
            if($month == "Q1" || $month == "Q2" || $month == "Q3" || $month == "Q4") {
                // Quarterly report
                Log::info('Generating quarterly report');
                $quarter_num = $month[1];
                $sql = $this->get_quarterlyFromDB($quarter_num, $year, $category);
            } else if($month === "N" AND $year !== "") {
                // Yearly report
                Log::info('Generating yearly report');
                $sql = $this->get_yearlyFromDB($year, $category);
            } else {
                // Monthly report
                Log::info('Generating monthly report');
                $sql = $this->get_monthlyFromDB($month, $year, $category);
            }
        }

        Log::info('Report data count: ' . count($sql));
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
                        LEFT JOIN departments on departments.id = users.department_id
                        WHERE MONTH(m.created_at) = "'.$month.'" AND YEAR(m.created_at) =  "'.$year.'" AND itemcategories.id = '.$category.' AND supplier_items.status != 0 AND m.type IN (2, 3, 5)');
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
                        LEFT JOIN departments on departments.id = users.department_id
                        WHERE YEAR(m.created_at) =  "'.$year.'" AND itemcategories.id = '.$category.' AND supplier_items.status != 0 AND m.type IN (2, 3, 5)');
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
                        LEFT JOIN departments on departments.id = users.department_id
                        WHERE QUARTER(m.created_at) = "'.$quarter.'" AND YEAR(m.created_at) =  "'.$year.'" AND itemcategories.id = '.$category.' AND supplier_items.status != 0 AND m.type IN (2, 3, 5)');
        return $sql;
    }
    public function get_weeklyFromDB($year, $category, $month, $week_number)
    {
        // Simplified approach: Calculate week ranges based on 7-day periods from the start of the month
        $firstDayOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();

        // Calculate start and end dates for the specified week
        $startOfWeek = $firstDayOfMonth->copy()->addDays(($week_number - 1) * 7);
        $endOfWeek = $startOfWeek->copy()->addDays(6)->endOfDay();

        // Ensure we don't go beyond the current month
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();
        if ($endOfWeek->gt($lastDayOfMonth)) {
            $endOfWeek = $lastDayOfMonth->copy()->endOfDay();
        }

        // Log the date range for debugging
        Log::info('Weekly report date range', [
            'start' => $startOfWeek->format('Y-m-d H:i:s'),
            'end' => $endOfWeek->format('Y-m-d H:i:s'),
            'month' => $month,
            'year' => $year,
            'week_number' => $week_number,
            'category' => $category
        ]);

        // Use raw SQL query to match the pattern of other methods and ensure consistency
        // Include: type 2 (partially released), type 3 (released), type 5 (cancelled)
        // Exclude: type 1 (requesting), type 4 (wasted)
        $sql = DB::select('SELECT DISTINCT DATE_FORMAT(m.created_at, "%m-%d-%Y") as dateRequest, m.*, itemcategories.*, items.*, users.*, departments.*, supplier_items.*, suppliers.*
                        FROM movements m
                        LEFT JOIN users on users.id = m.user_id
                        LEFT JOIN supplier_items on supplier_items.id = m.supplieritem_id
                        LEFT JOIN items on items.id = supplier_items.item_id
                        LEFT JOIN suppliers on suppliers.id = supplier_items.supplier_id
                        LEFT JOIN itemcategories on itemcategories.id = supplier_items.category_id
                        LEFT JOIN departments on departments.id = users.department_id
                        WHERE m.created_at BETWEEN ? AND ?
                        AND itemcategories.id = ?
                        AND supplier_items.status != 0
                        AND m.type IN (2, 3, 5)',
                        [$startOfWeek->format('Y-m-d H:i:s'), $endOfWeek->format('Y-m-d H:i:s'), $category]);

        Log::info('Weekly report query result count: ' . count($sql));
        return $sql;
    }
    public function monthlyreport_page()
    {
        return view('pages.monthlyreport');
    }
}
