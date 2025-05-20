<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Movements;
use App\Models\Supplier_Items;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the system
     */
    public function getNotifications()
    {
        try {
            // Get count of pending requests
            $pendingRequests = Movements::where(['notification' => 1, 'type' => 1])->count();
            
            // Get low stock items
            $lowStockItems = DB::table('supplier_items')
                ->join('items', 'items.id', '=', 'supplier_items.item_id')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'supplier_items.supplier_id')
                ->select(
                    'supplier_items.id',
                    'supplier_items.stock',
                    'supplier_items.status',
                    'items.item',
                    'items.image',
                    'suppliers.name as supplier_name'
                )
                ->where('supplier_items.stock', '<=', 5)
                ->where('supplier_items.status', '=', 1)
                ->orderBy('supplier_items.stock', 'asc')
                ->get();
            
            // Check for items that have exceeded their lifespan
            $this->checkItemLifespan();
            
            return response()->json([
                'success' => true,
                'pendingRequests' => $pendingRequests,
                'lowStockCount' => count($lowStockItems),
                'lowStockItems' => $lowStockItems
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getNotifications: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check items that have exceeded their lifespan and mark them as wasted
     */
    private function checkItemLifespan()
    {
        try {
            // Get items with age information
            $items = DB::select("
                SELECT 
                    TIMESTAMPDIFF(YEAR, supplier_items.date, CURDATE()) AS age, 
                    supplier_items.id, 
                    supplier_items.no_ofYears 
                FROM supplier_items 
                WHERE supplier_items.date IS NOT NULL
            ");
            
            foreach ($items as $item) {
                // If item age exceeds its lifespan
                if ($item->age > 0 && $item->age >= $item->no_ofYears) {
                    // Mark item as inactive (wasted)
                    DB::table('supplier_items')
                        ->where('id', $item->id)
                        ->update(['status' => 0]);
                    
                    // Update related movements
                    if (Movements::where('supplieritem_id', $item->id)->exists()) {
                        DB::table('movements')
                            ->where('supplieritem_id', $item->id)
                            ->update(['dateWasted' => Carbon::now()]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in checkItemLifespan: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
