<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TermsConditionResource;
use App\Repositories\TermsConditionRepositoryInterface;
use App\Http\Controllers\Controller;

class TermsConditionController extends Controller
{
    protected $termsRepository;

    public function __construct(TermsConditionRepositoryInterface $termsRepository)
    {
        $this->termsRepository = $termsRepository;
    }

    public function index()
    {
        $terms = $this->termsRepository->getAll()->where('status', true);
        $color = optional($this->termsRepository->getAll()->first())->color;
        $hex = strtoupper(ltrim((string)($color ?: '#FFFFFF'), '#'));
        $topColor = '0xFF' . $hex;
        return response()->json([
            'data' => TermsConditionResource::collection($terms)->resolve(),
            'color' => $topColor,
        ]);
    }
}


