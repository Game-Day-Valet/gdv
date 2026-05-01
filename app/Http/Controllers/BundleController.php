<?php

namespace App\Http\Controllers;

use App\Enums\ItemStatus;
use App\Http\Requests\BundleRequest;
use App\Models\Item;
use App\Repositories\BundleRepositoryInterface;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;

class BundleController extends Controller
{
    protected $bundleRepository;

    public function __construct(BundleRepositoryInterface $bundleRepository)
    {
        $this->bundleRepository = $bundleRepository;
    }

    public function index()
    {
        $bundles = $this->bundleRepository->getAllAvailable();
        return view('bundle_management.index', compact('bundles'));
    }

    public function create()
    {
        $statuses = ItemStatus::cases();
        $items = Item::where('status', ItemStatus::AVAILABLE->value)->get();
        return view('bundle_management.create', compact('statuses', 'items'));
    }

    public function store(BundleRequest $request)
    {
        try {
            $data = $request->only(['name', 'description', 'price', 'status', 'is_most_popular']);
            $data['is_most_popular'] = $request->has('is_most_popular') ? (bool) $request->input('is_most_popular') : false;
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image');
            }
            $this->bundleRepository->create($data, $request->input('items', []));
            return redirect()->route('bundle-management.index')->with('success', 'Bundle created successfully.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['items' => $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $bundle = $this->bundleRepository->find($id);
        $statuses = ItemStatus::cases();
        $items = Item::where('status', ItemStatus::AVAILABLE->value)->get();
        return view('bundle_management.edit', compact('bundle', 'statuses', 'items'));
    }

    public function update(BundleRequest $request, $id)
    {
        try {
            $data = $request->only(['name', 'description', 'price', 'status', 'is_most_popular']);
            $data['is_most_popular'] = $request->has('is_most_popular') ? (bool) $request->input('is_most_popular') : false;
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image');
            }
            $this->bundleRepository->update($id, $data, $request->input('items', []));
            return redirect()->route('bundle-management.index')->with('success', 'Bundle updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['items' => $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $this->bundleRepository->delete($id);
            return redirect()->route('bundle-management.index')->with('success', 'Bundle deleted successfully.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|integer|exists:bundles,id',
            'orders.*.sort_order' => 'required|integer|min:0'
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['orders'] as $order) {
                \App\Models\Bundle::where('id', $order['id'])->update(['sort_order' => (int)$order['sort_order']]);
            }
        });

        return response()->json(['success' => true]);
    }
}
