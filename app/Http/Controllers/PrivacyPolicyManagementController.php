<?php

namespace App\Http\Controllers;

use App\Http\Requests\FaqRequest;
use App\Repositories\PrivacyPolicyRepositoryInterface;
use Illuminate\Http\Request;

class PrivacyPolicyManagementController extends Controller
{
    protected $privacyPolicyRepository;

    public function __construct(PrivacyPolicyRepositoryInterface $privacyPolicyRepository)
    {
        $this->privacyPolicyRepository = $privacyPolicyRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $privacyPolicies = $this->privacyPolicyRepository->getAll();
        return view('privacy_policy_management.index', compact('privacyPolicies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('privacy_policy_management.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FaqRequest $request)
    {
        try {
            $privacyPolicy = $this->privacyPolicyRepository->create($request->validated());

            return redirect()->route('privacy-policy-management.index')
                ->with('success', 'Privacy Policy created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create Privacy Policy. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $privacyPolicy = $this->privacyPolicyRepository->find($id);
        return view('privacy_policy_management.show', compact('privacyPolicy'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $privacyPolicy = $this->privacyPolicyRepository->find($id);
        return view('privacy_policy_management.edit', compact('privacyPolicy'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FaqRequest $request, string $id)
    {
        try {
            $privacyPolicy = $this->privacyPolicyRepository->update($id, $request->validated());

            return redirect()->route('privacy-policy-management.index')
                ->with('success', 'Privacy Policy updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update Privacy Policy. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->privacyPolicyRepository->delete($id);
            return redirect()->route('privacy-policy-management.index')
                ->with('success', 'Privacy Policy deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to delete Privacy Policy. Please try again.');
        }
    }
}
