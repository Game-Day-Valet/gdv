<?php


namespace App\Http\Controllers\Api;

use App\Http\Requests\FaqRequest;
use App\Http\Resources\FaqResource;
use App\Repositories\FaqRepositoryInterface;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;

class FaqController extends Controller
{
    protected $faqRepository;

    public function __construct(FaqRepositoryInterface $faqRepository)
    {
        $this->faqRepository = $faqRepository;
    }

    public function index()
    {
        $faqs = $this->faqRepository->getAll()->where('status', true); // Only enabled FAQs
        $color = optional($this->faqRepository->getAll()->first())->color; // page-level color (nullable)
        $hex = strtoupper(ltrim((string)($color ?: '#FFFFFF'), '#'));
        $topColor = '0xFF' . $hex;
        return response()->json([
            'data' => FaqResource::collection($faqs)->resolve(),
            'color' => $topColor,
        ]);
    }

    public function store(FaqRequest $request)
    {
        try {
            $data = $request->validated();
            $faq = $this->faqRepository->create($data);
            return new FaqResource($faq);
        } catch (Exception $e) {
            throw new Exception('FAQ creation failed: ' . $e->getMessage(), 403);
        }
    }

    public function update(FaqRequest $request, $id)
    {
        try {
            $data = $request->validated();
            $faq = $this->faqRepository->update($id, $data);
            return new FaqResource($faq);
        } catch (Exception $e) {
            throw new Exception('FAQ update failed: ' . $e->getMessage(), 403);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $this->faqRepository->delete($id);
            return response()->json(['message' => 'FAQ deleted successfully'], 200);
        } catch (Exception $e) {
            throw new Exception('FAQ deletion failed: ' . $e->getMessage(), 403);
        }
    }
}
