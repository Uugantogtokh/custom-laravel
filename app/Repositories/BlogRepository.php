<?php

namespace App\Repositories;

use App\Interfaces\BlogRepositoryInterface;
use App\Models\Blog;

class BlogRepository implements BlogRepositoryInterface
{
    public function getAllBlogs()
    {
        return Blog::all();
    }

    public function getBlogById($blogId)
    {
        return Blog::findOrFail($blogId);
    }

    public function deleteBlog($blogId)
    {
        Blog::destroy($blogId);
    }

    public function createBlog(array $blogDetails)
    {
        return Blog::create($blogDetails);
    }

    public function updateBlog($blogId, array $newDetails)
    {
        return Blog::whereId($blogId)->update($newDetails);
    }

    public function getFulfilledBlogs()
    {
        return Blog::where('is_fulfilled', true);
    }
}
