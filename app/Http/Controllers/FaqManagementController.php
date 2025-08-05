<?php

namespace App\Http\Controllers;

use App\Http\Requests\FaqRequest;
use App\Repositories\FaqRepositoryInterface;
use Illuminate\Http\Request;

class FaqManagementController extends Controller
{
    protected $faqRepository;

    public function __construct(FaqRepositoryInterface $faqRepository)
    {
        $this->faqRepository = $faqRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $faqs = $this->faqRepository->getAll();
        return view('faq_management.index', compact('faqs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('faq_management.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FaqRequest $request)
    {
        try {
            $faq = $this->faqRepository->create($request->validated());

            return redirect()->route('faq-management.index')
                ->with('success', 'FAQ created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create FAQ. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $faq = $this->faqRepository->find($id);
        return view('faq_management.show', compact('faq'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $faq = $this->faqRepository->find($id);
        return view('faq_management.edit', compact('faq'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FaqRequest $request, string $id)
    {
        try {
            $faq = $this->faqRepository->update($id, $request->validated());

            return redirect()->route('faq-management.index')
                ->with('success', 'FAQ updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update FAQ. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->faqRepository->delete($id);
            return redirect()->route('faq-management.index')
                ->with('success', 'FAQ deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to delete FAQ. Please try again.');
        }
    }
}
