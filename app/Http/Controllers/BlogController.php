<?php

namespace App\Http\Controllers;

use App\Interfaces\BlogRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Rules\UniqueValue;

class BlogController extends Controller
{
    protected $blogRepository;

    public function __construct(BlogRepositoryInterface $blogRepository)
    {
        $this->blogRepository = $blogRepository;
    }

    public function index()
    {
        $blogs = Blog::paginate(10);
        $message = 'Not have data';
        $startNumber = ($blogs->currentPage() - 1) * $blogs->perPage() + 1;

        return view('blogs.index', compact('blogs', 'message', 'startNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => [
                'required',
                'max:255',
                new UniqueValue('blogs', 'title'),
                'string',
            ],
            'info' => 'required|string',
        ]);

        $blogDetails = [
            'title' => $request->input('title'),
            'info' => $request->input('info'),
        ];

        $this->blogRepository->createBlog($blogDetails);

        return redirect()->route('blogs.index')
            ->with('success', 'Blog created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => [
                'required',
                'max:255',
                new UniqueValue('blogs', 'title'),
                'string',
            ],
            'info' => 'required|string',
        ]);

        $newDetails = [
            'title' => $request->input('title'),
            'info' => $request->input('info'),
        ];

        $this->blogRepository->updateBlog($id, $newDetails);

        return redirect()->route('blogs.index')
            ->with('success', 'Blog updated successfully.');
    }

    public function destroy($id)
    {
        $this->blogRepository->deleteBlog($id);

        return redirect()->route('blogs.index')
            ->with('success', 'Blog deleted successfully');
    }

    public function create()
    {
        return view('blogs.create');
    }

    public function show($id)
    {
        $this->blogRepository->getBlogById($id);

        return view('blogs.show', compact('blog'));
    }

    public function edit($id)
    {
        $blog = $this->blogRepository->getBlogById($id);

        return view('blogs.edit', compact('blog'));
    }
}
