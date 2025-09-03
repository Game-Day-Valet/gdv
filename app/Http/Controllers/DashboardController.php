<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Item;
use App\Models\Bundle;
use App\Repositories\RentalRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\TournamentRepositoryInterface;

class DashboardController extends Controller
{
    protected $rentalRepository;

    public function __construct(RentalRepositoryInterface $rentalRepository)
    {
        $this->rentalRepository = $rentalRepository;
    }

    public function index()
    {
        // Get rental statistics
        $totalRentals = Rental::count();
        $pendingRentals = Rental::where('status', 'pending')->count();
        $deliveredRentals = Rental::where('status', 'delivered')->count();
        $returnedRentals = Rental::where('status', 'returned')->count();
        $pickedUpRentals = Rental::where('status', 'picked_up')->count();

        // Calculate percentages for trends
        $totalRentalsLastMonth = Rental::where('created_at', '>=', now()->subMonth())->count();
        $totalRentalsThisMonth = Rental::where('created_at', '>=', now()->startOfMonth())->count();

        $pendingRentalsLastMonth = Rental::where('status', 'pending')
            ->where('created_at', '>=', now()->subMonth())->count();
        $pendingRentalsThisMonth = Rental::where('status', 'pending')
            ->where('created_at', '>=', now()->startOfMonth())->count();

        $deliveredRentalsLastMonth = Rental::where('status', 'delivered')
            ->where('created_at', '>=', now()->subMonth())->count();
        $deliveredRentalsThisMonth = Rental::where('status', 'delivered')
            ->where('created_at', '>=', now()->startOfMonth())->count();

        $returnedRentalsLastMonth = Rental::where('status', 'returned')
            ->where('created_at', '>=', now()->subMonth())->count();
        $returnedRentalsThisMonth = Rental::where('status', 'returned')
            ->where('created_at', '>=', now()->startOfMonth())->count();

        // Calculate trend percentages
        $totalTrend = $totalRentalsLastMonth > 0 ? round((($totalRentalsThisMonth - $totalRentalsLastMonth) / $totalRentalsLastMonth) * 100, 1) : 0;
        $pendingTrend = $pendingRentalsLastMonth > 0 ? round((($pendingRentalsThisMonth - $pendingRentalsLastMonth) / $pendingRentalsLastMonth) * 100, 1) : 0;
        $deliveredTrend = $deliveredRentalsLastMonth > 0 ? round((($deliveredRentalsThisMonth - $deliveredRentalsLastMonth) / $deliveredRentalsLastMonth) * 100, 1) : 0;
        $returnedTrend = $returnedRentalsLastMonth > 0 ? round((($returnedRentalsThisMonth - $returnedRentalsLastMonth) / $returnedRentalsLastMonth) * 100, 1) : 0;

        // Calculate top rental items
        $topRentalItems = $this->getTopRentalItems();
        // Calculate top rental bundles
        $topRentalBundles = $this->getTopRentalBundles();

        $todaysTournaments = app(TournamentRepositoryInterface::class)->getTodaysTournaments();

        $stats = [
            'total_rentals' => $totalRentals,
            'pending_rentals' => $pendingRentals,
            'delivered_rentals' => $deliveredRentals,
            'returned_rentals' => $returnedRentals,
            'picked_up_rentals' => $pickedUpRentals,
            'total_trend' => $totalTrend,
            'pending_trend' => $pendingTrend,
            'delivered_trend' => $deliveredTrend,
            'returned_trend' => $returnedTrend,
            'top_rental_items' => $topRentalItems,
            'top_rental_bundles' => $topRentalBundles,
        ];

        return view('index', compact('stats', 'todaysTournaments'));
    }

    private function getTopRentalItems()
    {
        $rentals = Rental::whereNotNull('items')->get();
        $itemCounts = [];
        foreach ($rentals as $rental) {
            if (is_array($rental->items)) {
                foreach ($rental->items as $item) {
                    $itemId = $item['item_id'] ?? null;
                    $quantity = $item['quantity'] ?? 1;
                    if ($itemId) {
                        if (!isset($itemCounts[$itemId])) {
                            $itemCounts[$itemId] = 0;
                        }
                        $itemCounts[$itemId] += $quantity;
                    }
                }
            }
        }
        arsort($itemCounts);
        $topItemIds = array_slice(array_keys($itemCounts), 0, 10, true);
        $items = Item::whereIn('id', $topItemIds)->get()->keyBy('id');
        $topItems = [];
        foreach ($topItemIds as $itemId) {
            if (isset($items[$itemId])) {
                $topItems[] = [
                    'name' => $items[$itemId]->name,
                    'total_rentals' => $itemCounts[$itemId],
                    'percentage' => $this->calculatePercentage($itemCounts[$itemId], array_sum($itemCounts))
                ];
            }
        }
        return $topItems;
    }

    private function getTopRentalBundles()
    {
        $rentals = Rental::whereNotNull('bundles')->get();
        $bundleCounts = [];
        foreach ($rentals as $rental) {
            if (is_array($rental->bundles)) {
                foreach ($rental->bundles as $b) {
                    $bundleId = null;
                    $qty = 1;
                    if (is_array($b) && isset($b['bundle_id'])) {
                        $bundleId = $b['bundle_id'];
                        $qty = isset($b['quantity']) ? (int) $b['quantity'] : 1;
                    } elseif (is_numeric($b)) {
                        $bundleId = $b;
                        $qty = 1;
                    }
                    if ($bundleId) {
                        if (!isset($bundleCounts[$bundleId])) {
                            $bundleCounts[$bundleId] = 0;
                        }
                        $bundleCounts[$bundleId] += $qty;
                    }
                }
            }
        }
        arsort($bundleCounts);
        $topBundleIds = array_slice(array_keys($bundleCounts), 0, 5, true);
        $bundles = \App\Models\Bundle::with('items')->whereIn('id', $topBundleIds)->get()->keyBy('id');
        $topBundles = [];
        foreach ($topBundleIds as $bundleId) {
            if (isset($bundles[$bundleId])) {
                $bundle = $bundles[$bundleId];
                $items = $bundle->items->map(function($item) {
                    return [
                        'name' => $item->name,
                        'price' => $item->price
                    ];
                })->toArray();
                $topBundles[] = [
                    'name' => $bundle->name,
                    'total_rentals' => $bundleCounts[$bundleId],
                    'items' => $items
                ];
            }
        }
        return $topBundles;
    }

    private function calculatePercentage($value, $total)
    {
        if ($total == 0) return 0;
        return round(($value / $total) * 100, 1);
    }
}
