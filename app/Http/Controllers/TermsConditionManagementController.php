<?php

namespace App\Http\Controllers;

use App\Http\Requests\FaqRequest;
use App\Repositories\TermsConditionRepositoryInterface;

class TermsConditionManagementController extends Controller
{
    protected $termsRepository;

    public function __construct(TermsConditionRepositoryInterface $termsRepository)
    {
        $this->termsRepository = $termsRepository;
    }

    public function index()
    {
        $termsConditions = $this->termsRepository->getAll();

        return view('terms_condition_management.index', compact('termsConditions'));
    }

    public function create()
    {
        return view('terms_condition_management.create');
    }

    public function store(FaqRequest $request)
    {
        try {
            $this->termsRepository->create($request->validated());
            return redirect()->route('terms-condition-management.index')
                ->with('success', 'Terms & Conditions created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create Terms & Conditions. Please try again.');
        }
    }

    public function show(string $id)
    {
        $terms = $this->termsRepository->find($id);
        return view('terms_condition_management.show', compact('terms'));
    }

    public function edit(string $id)
    {
        $terms = $this->termsRepository->find($id);
        return view('terms_condition_management.edit', compact('terms'));
    }

    public function update(FaqRequest $request, string $id)
    {
        try {
            $this->termsRepository->update($id, $request->validated());
            return redirect()->route('terms-condition-management.index')
                ->with('success', 'Terms & Conditions updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update Terms & Conditions. Please try again.');
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->termsRepository->delete($id);
            return redirect()->route('terms-condition-management.index')
                ->with('success', 'Terms & Conditions deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to delete Terms & Conditions. Please try again.');
        }
    }
}


