<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PrivacyPolicyResource;
use App\Repositories\PrivacyPolicyRepositoryInterface;
use App\Http\Controllers\Controller;

class PrivacyPolicyController extends Controller
{
    protected $privacyPolicyRepository;

    public function __construct(PrivacyPolicyRepositoryInterface $privacyPolicyRepository)
    {
        $this->privacyPolicyRepository = $privacyPolicyRepository;
    }

    public function index()
    {
        $privacyPolicies = $this->privacyPolicyRepository->getAll()->where('status', true); // Only enabled privacy policies
        $color = optional($this->privacyPolicyRepository->getAll()->first())->color;
        $hex = strtoupper(ltrim((string)($color ?: '#FFFFFF'), '#'));
        $topColor = '0xFF' . $hex;
        return response()->json([
            'data' => PrivacyPolicyResource::collection($privacyPolicies)->resolve(),
            'color' => $topColor,
        ]);
    }
}
