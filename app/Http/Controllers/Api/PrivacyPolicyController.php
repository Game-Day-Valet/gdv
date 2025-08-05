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
        return PrivacyPolicyResource::collection($privacyPolicies);
    }
}
