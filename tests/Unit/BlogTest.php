<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Blog;
use Database\Seeders\BlogSeeder;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_with_no_blogs()
    {
        $response = $this->get(route('blogs.index'));

        $response->assertStatus(200);

        $response->assertSee('Not have data');

        $response->assertViewHas('blogs');

        $response->assertDontSee('Error: Page Not Found');
    }

    public function test_home_page_with_blogs()
    {
        Blog::factory()->times(10)->create();

        $response = $this->get(route('blogs.index'));

        $response->assertStatus(200);

        $response->assertDontSee('Not have data');

        $response->assertViewHas('blogs');

        $response->assertDontSee('Error: Page Not Found');
    }

    public function test_edit_page_with_existing_blog()
    {
        $data = [
            'title' => 'Test Title',
            'info' => 'Test Info',
        ];

        $this->post(route('blogs.store'), $data);

        $blog = Blog::where(['title' => 'Test Title', 'info' => 'Test Info'])->first();

        $response = $this->get(route('blogs.edit', $blog->id));

        $response->assertStatus(200);

        $response->assertSee('Test Title');

        $response->assertSee('Test Info');
    }

    public function test_create_page()
    {
        $data = [
            'title' => 'Test Title',
            'info' => 'Test Info',
        ];

        $this->post(route('blogs.store'), $data);

        $blog = Blog::where(['title' => 'Test Title', 'info' => 'Test Info'])->first();

        $response = $this->get(route('blogs.edit', $blog->id));

        $response->assertStatus(200);

        $response->assertSee('Test Title');

        $response->assertSee('Test Info');
    }

    public function test_create_a_blog_success()
    {
        $data = [
            'title' => 'Test Title',
            'info' => 'Test Info',
        ];

        $response = $this->post(route('blogs.store'), $data);

        $response->assertSessionHas('success', 'Blog created successfully.');

        $response->assertRedirect(route('blogs.index'));

        $response->assertStatus(302);

        $this->assertDatabaseHas('blogs', ['title' => 'Test Title', 'info' => 'Test Info']);
    }

    public function test_create_a_blog_failed_null_title()
    {
        $response = $this->post(route('blogs.store'), [
            'title' => null,
            'info' => 'Blog content',
        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors('title', 'Title field is required.');
    }

    public function test_create_a_blog_failed_unique_title()
    {
        $this->assertDatabaseMissing('blogs', ['info' => 'Blog content']);

        $blog = Blog::factory()->create(['title' => 'Sample Title']);

        $response = $this->post(route('blogs.store'), [
            'title' => 'Sample Title',
            'info' => 'Blog content',
        ]);

        $response->assertSessionHasErrors('title', 'The title is not unique.');

        $response->assertStatus(302);
    }

    public function test_it_can_delete_a_blog()
    {
        $blog = Blog::factory()->create();
        $blog->delete();
        $this->assertModelMissing($blog);
    }

    public function test_it_can_create_a_blog()
    {
        $blog = Blog::factory()->create();
        $this->assertModelExists($blog);
    }

    public function test_blogs_seed_success(): void
    {
        $this->seed(BlogSeeder::class);

        $expectedCount = 50;

        $this->assertEquals($expectedCount, Blog::get()->count());
    }

    public function test_blogs_seed_failed_count(): void
    {
        $this->seed(BlogSeeder::class);

        $expectedCount = 60;

        $this->assertNotEquals($expectedCount, Blog::get()->count());
    }
}
