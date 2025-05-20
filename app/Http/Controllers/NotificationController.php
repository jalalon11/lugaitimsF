<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the system
     */
    public function getNotifications()
    {
        // Set content type to JSON to ensure proper response
        header('Content-Type: application/json');

        try {
            // Use simple queries with error handling
            try {
                // Get count of pending requests using direct DB query
                $pendingRequests = DB::table('movements')
                    ->where('notification', 1)
                    ->where('type', 1)
                    ->count();
            } catch (\Exception $e) {
                Log::error('Error counting pending requests: ' . $e->getMessage());
                $pendingRequests = 0;
            }

            // Get low stock items with simple query and error handling
            try {
                $lowStockItems = DB::table('supplier_items')
                    ->join('items', 'items.id', '=', 'supplier_items.item_id')
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'supplier_items.supplier_id')
                    ->select(
                        'supplier_items.id',
                        'supplier_items.stock',
                        'supplier_items.status',
                        'items.item',
                        'items.image',
                        DB::raw('COALESCE(suppliers.name, "Unknown") as supplier_name')
                    )
                    ->where('supplier_items.stock', '<=', 5)
                    ->where('supplier_items.status', '=', 1)
                    ->orderBy('supplier_items.stock', 'asc')
                    ->get()
                    ->toArray();
            } catch (\Exception $e) {
                Log::error('Error fetching low stock items: ' . $e->getMessage());
                $lowStockItems = [];
            }

            // Return simple JSON response
            return response()->json([
                'success' => true,
                'pendingRequests' => $pendingRequests,
                'lowStockCount' => count($lowStockItems),
                'lowStockItems' => $lowStockItems
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getNotifications: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            // Return a simple error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching notifications',
                'lowStockCount' => 0,
                'lowStockItems' => []
            ]);
        }
    }
}
