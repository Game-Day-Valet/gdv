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
        return TermsConditionResource::collection($terms);
    }
}


