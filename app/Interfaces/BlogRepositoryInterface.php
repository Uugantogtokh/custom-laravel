<?php

namespace App\Interfaces;

interface BlogRepositoryInterface
{
    public function getAllBlogs();
    public function getBlogById($orderId);
    public function deleteBlog($orderId);
    public function createBlog(array $orderDetails);
    public function updateBlog($orderId, array $newDetails);
}
