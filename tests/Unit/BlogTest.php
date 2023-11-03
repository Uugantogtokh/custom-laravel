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

        $response->assertDontSee('Error: Page Not Found');
    }

    public function test_edit_page_with_non_existent_blog()
    {
        $nonExistentBlogId = 999;

        $response = $this->get(route('blogs.edit', $nonExistentBlogId));

        $response->assertStatus(404);
    }

    public function test_create_page()
    {
        $response = $this->get(route('blogs.create'));

        $response->assertStatus(200);
        $response->assertDontSee('Error: Page Not Found');

        $response->assertSee('Add a BLog');
        $response->assertSee('Title');
        $response->assertSee('Body');
        $response->assertSee('Create Blog');
        $response->assertSessionHasNoErrors();
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

        $response = $this->get(route('blogs.index'));

        $response->assertSee('Test Title');
        $response->assertSee('Test Info');
    }

    public function test_create_a_blog_failed_null_title()
    {
        $response = $this->post(route('blogs.store'), [
            'title' => null,
            'info' => 'Blog content',
        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors('title');

        $this->assertEquals('The title field is required.', session('errors')->first('title'));

        $this->assertDatabaseMissing('blogs', ['info' => 'Blog content']);

        $this->assertEquals(0, Blog::count());
    }

    public function test_create_a_blog_failed_unique_title()
    {
        $blog = Blog::factory()->create(['title' => 'Sample Title']);

        $response = $this->post(route('blogs.store'), [
            'title' => 'Sample Title',
            'info' => 'Blog content',
        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors('title');

        $this->assertEquals('The title is not unique.', session('errors')->first('title'));

        $this->assertDatabaseMissing('blogs', [
            'title' => 'Sample Title',
            'info' => 'Blog content',
        ]);

        $this->assertDatabaseHas('blogs', ['title' => 'Sample Title']);

        $this->assertEquals(1, Blog::count());
    }

    public function test_update_existent_blog()
    {
        $blog = Blog::factory()->create();

        $data = [
            'title' => 'Updated Title',
            'info' => 'Updated Info',
        ];

        $response = $this->put(route('blogs.update', $blog->id), $data);

        $response->assertSessionHas('success', 'Blog updated successfully.');
        $response->assertRedirect(route('blogs.index'));
        $this->assertDatabaseHas('blogs', ['title' => 'Updated Title', 'info' => 'Updated Info']);
    }

    public function test_update_non_existent_blog()
    {
        $nonExistentBlogId = 999;

        $data = [
            'title' => 'Updated Title',
            'info' => 'Updated Info',
        ];

        $response = $this->put(route('blogs.update', $nonExistentBlogId), $data);

        $response->assertNotFound();
    }

    public function test_it_can_delete_a_blog()
    {
        $blog = Blog::factory()->create();

        $this->assertDatabaseHas('blogs', ['id' => $blog->id]);

        $blog->delete();

        $this->assertDatabaseMissing('blogs', ['id' => $blog->id]);

        $deletedBlog = Blog::find($blog->id);

        $this->assertNull($deletedBlog);

        $response = $this->get('/blogs/' . $blog->id);

        $response->assertStatus(404);
    }

    public function test_delete_non_existent_blog()
    {
        $nonExistentBlogId = 999;

        $response = $this->delete(route('blogs.destroy', $nonExistentBlogId));

        $response->assertNotFound();
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

    public function test_blogs_factory_success_count(): void
    {
        Blog::factory()->times(30)->create();

        $expectedCount = 30;

        $this->assertEquals($expectedCount, Blog::get()->count());

        Blog::factory()->times(40)->create();

        $expectedCount += 40;

        $this->assertEquals($expectedCount, Blog::get()->count());
    }

    public function test_blogs_factory_failed_count(): void
    {
        Blog::factory()->times(30)->create();

        $expectedCount = 30;

        $this->assertEquals($expectedCount, Blog::get()->count());

        Blog::factory()->times(40)->create();

        // Missed additional count
        $expectedCount += 30;

        $this->assertNotEquals($expectedCount, Blog::get()->count());
    }

    public function test_blogs_pagination_in_home_page(): void
    {
        $blogs_10 = Blog::factory(10)->create();

        $blogs_20 = Blog::factory(10)->create();

        // Get the first page
        $response = $this->get('/blogs');

        $response->assertStatus(200);

        foreach ($blogs_10 as $blog) {
            $response->assertSeeText($blog->title);
        }

        // Get the second page
        $response = $this->get('/blogs?page=2');

        $response->assertStatus(200);

        foreach ($blogs_20 as $blog) {
            $response->assertSeeText($blog->title);
        }
    }
}
