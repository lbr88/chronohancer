<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::all();
        return view('tags.index', compact('tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);
        
        if (!isset($validated['color'])) {
            $validated['color'] = '#' . substr(md5($validated['name']), 0, 6);
        }

        $tag = Tag::create($validated);

        if ($request->wantsJson()) {
            return response()->json($tag, 201);
        }

        return redirect()->route('tags.index')->with('success', 'Tag created successfully.');
    }

    public function update(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
        ]);

        $tag->update($validated);

        if ($request->wantsJson()) {
            return response()->json($tag);
        }

        return redirect()->route('tags.index')->with('success', 'Tag updated successfully.');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();
        
        return redirect()->route('tags.index')->with('success', 'Tag deleted successfully.');
    }
}
