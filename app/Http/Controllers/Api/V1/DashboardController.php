<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Lead;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

class DashboardController extends Controller
{
    #[OA\Get(
        path: '/api/v1/dashboard/seller',
        operationId: 'sellerDashboard',
        summary: 'Get seller dashboard statistics (Cached)',
        security: [['bearerAuth' => []]],
        tags: ['Dashboard'],
        responses: [
            new OA\Response(response: 200, description: 'Dashboard stats fetched successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function sellerDashboard(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Cache for 5 minutes (300 seconds)
        $cacheKey = "seller_dashboard_stats_{$userId}";

        $stats = Cache::remember($cacheKey, 300, function () use ($userId) {
            
            // 1. Total Products Count
            $totalProducts = Product::where('user_id', $userId)->count();

            // 2. Total Leads Received
            $totalLeads = Lead::where('seller_id', $userId)->count();

            // 2b. Total Enquiries Received
            $totalEnquiries = Lead::where('seller_id', $userId)->where('source', Lead::SOURCE_INQUIRY_FORM)->count();

            // 3. Total Product Views (Sum)
            $totalViews = Product::where('user_id', $userId)->sum('views_count');

            // 4. Total Unread Chats
            $unreadChats = Conversation::where('seller_id', $userId)
                ->where('seller_unread_count', '>', 0)
                ->count();

            // 5. Most Viewed Product
            $mostViewedProduct = Product::where('user_id', $userId)
                ->with('primaryImage')
                ->orderByDesc('views_count')
                ->first();

            // 5b. Lowest Viewed Product
            $lowestViewedProduct = Product::where('user_id', $userId)
                ->with('primaryImage')
                ->orderBy('views_count', 'asc')
                ->first();



            return [
                'total_products' => $totalProducts,
                'total_leads_received' => $totalLeads,
                'total_enquiries_received' => $totalEnquiries,
                'total_product_views' => (int) $totalViews,
                'total_unread_chats' => $unreadChats,
                'most_viewed_product' => $mostViewedProduct ? $mostViewedProduct->toSellerListArray() : null,
                'lowest_viewed_product' => $lowestViewedProduct ? $lowestViewedProduct->toSellerListArray() : null,
            ];
        });

        return ApiResponse::success('Dashboard stats fetched', [
            'dashboard' => $stats
        ]);
    }
}
