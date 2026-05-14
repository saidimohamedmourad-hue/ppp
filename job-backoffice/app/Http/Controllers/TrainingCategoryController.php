<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrainingCategoryRequest;
use App\Http\Requests\TrainingCategoryUpdateRequest;
use App\Models\TrainingCategory;
use Illuminate\Http\Request;

class TrainingCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = TrainingCategory::latest();
        if ($request->input('archived') == 'true') {
            $query->onlyTrashed();
        }

        $categories = $query->paginate(5)->onEachSide(1);

        return view('training-category.index', compact('categories'));
    }

    public function create()
    {
        return view('training-category.create');
    }

    public function store(TrainingCategoryRequest $request)
    {
        $validated = $request->validated();
        TrainingCategory::create($validated);
        return redirect()->route('training-category.index')->with('success', 'Category created successfully.');
    }

    public function show(string $id)
    {
        $category = TrainingCategory::findOrFail($id);
        return view('training-category.show', compact('category'));
    }

    public function edit(string $id)
    {
        $category = TrainingCategory::findOrFail($id);
        return view('training-category.edit', compact('category'));
    }

    public function update(TrainingCategoryUpdateRequest $request, string $id)
    {
        $validated = $request->validated();
        $category = TrainingCategory::findOrFail($id);
        $category->update($validated);
        return redirect()->route('training-category.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(string $id)
    {
        $category = TrainingCategory::findOrFail($id);
        $category->delete();
        return redirect()->route('training-category.index')->with('success', 'Category archived successfully.');
    }

    public function restore(string $id)
    {
        $category = TrainingCategory::withTrashed()->findOrFail($id);
        $category->restore();
        return redirect()->route('training-category.index', ['archived' => 'true'])->with('success', 'Category restored successfully.');
    }
}
