<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Blog;
use Database\Seeders\BlogSeeder;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page()
    {
        $response = $this->get(route('blogs.index'));
        $response->assertStatus(200);
    }

    public function test_create_page()
    {
        $data = [
            'title' => 'Test Post',
            'content' => 'Test Info',
        ];

        $response = $this->post(route('blogs.store'), $data);

        $response->assertStatus(302);
    }

    public function test_edit_page()
    {
        $blog = Blog::factory()->create();

        $response = $this->get(route('blogs.edit', $blog->id));
        $response->assertStatus(200);
    }

    public function test_it_can_create_a_blog()
    {
        $blog = Blog::factory()->create();
        $this->assertModelExists($blog);
    }

    public function test_blogs_can_be_created(): void
    {
        $this->seed(BlogSeeder::class);

        $expectedCount = 50;

        $this->assertEquals($expectedCount, Blog::get()->count());
    }

    public function test_it_can_delete_a_blog()
    {
        $blog = Blog::factory()->create();
        $blog->delete();
        $this->assertModelMissing($blog);
    }

    public function test_it_can_update_a_blog()
    {
        $blog = Blog::factory()->create();

        $data = [
            'title' => 'Updated Title',
            'info' => 'Updated Info.',
        ];

        $response = $this->put(route('blogs.update', $blog->id), $data);

        $response->assertStatus(302);
    }
}
