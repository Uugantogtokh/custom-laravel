<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Blog;
use Database\Seeders\BlogSeeder;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_with_no_blogs()
    {
        $response = $this->get(route('blogs.index'));

        $response->assertSessionHasNoErrors();

        $responseData = $response->original->getData();

        $this->assertEquals(0, count($responseData['blogs']));

        $this->assertEquals(0, Blog::count());
    }

    public function test_home_page_with_blogs()
    {
        Blog::factory()->times(10)->create();

        $response = $this->get(route('blogs.index'));

        $response->assertSessionHasNoErrors();

        $responseData = $response->original->getData()['blogs'];

        $blogsCountInDatabase = Blog::count();

        $this->assertEquals($blogsCountInDatabase, count($responseData));
    }
    // home page iin order zuv ajillaj baigaa eseh
    public function test_home_page_blogs_order_success()
    {
        Blog::factory()->count(10)->create();

        $response = $this->get(route('blogs.index'));

        $response->assertSessionHasNoErrors();

        $blogs = Blog::orderBy('created_at', 'desc')->get();

        $titles = $blogs->pluck('title')->all();
        $contents = $blogs->pluck('content')->all();

        $response->assertSeeInOrder($titles);

        $response->assertSeeInOrder($contents);
    }
    // home page iin order buruu ajillaj baigaa esehiig shalgah
    public function test_home_page_blogs_order_failed()
    {
        Blog::factory()->count(10)->create();

        $response = $this->get(route('blogs.index'));

        $response->assertSessionHasNoErrors();

        $blogs = Blog::orderBy('title', 'desc')->get();

        $titles = $blogs->pluck('title')->all();

        $titleString = implode('', $titles);

        $this->assertFalse(str_contains($response->getContent(), $titleString));
    }
    // uusgesen data edit page deer baigaa eseh
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

        $response->assertSee([
            $blog->title,
            $blog->info,
        ]);
    }

    public function test_edit_page_with_non_existent_blog()
    {
        $nonExistentBlogId = 999;

        $response = $this->get(route('blogs.edit', $nonExistentBlogId));

        $response->assertStatus(404);
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

        $response->assertSessionHasErrors('title');

        $this->assertEquals('The title field is required.', session('errors')->first('title'));

        $this->assertDatabaseMissing('blogs', ['info' => 'Blog content']);

        $this->assertEquals(0, Blog::count());
    }

    public function test_create_a_blog_failed_not_string_title()
    {
        $response = $this->post(route('blogs.store'), [
            'title' => 1,
            'info' => 'Blog content',
        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors('title');

        $this->assertEquals('The title field must be a string.', session('errors')->first('title'));

        $this->assertDatabaseMissing('blogs', ['info' => 'Blog content']);

        $this->assertEquals(0, Blog::count());
    }

    public function test_create_a_blog_failed_exceeding_max_char_title()
    {
        $response = $this->post(route('blogs.store'), [
            'title' => str_repeat('A', 21),
            'info' => 'Blog content',
        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors('title');

        $this->assertEquals('The title field must not be greater than 20 characters.', session('errors')->first('title'));

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

    public function test_create_a_blog_failed_null_info()
    {
        $response = $this->post(route('blogs.store'), [
            'title' => 'Sample Title',
            'info' => null,
        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors('info');

        $this->assertEquals('The info field is required.', session('errors')->first('info'));

        $this->assertDatabaseMissing('blogs', ['title' => 'Sample Title']);

        $this->assertEquals(0, Blog::count());
    }

    public function test_create_a_blog_failed_exceed_max_char_info()
    {
        $response = $this->post(route('blogs.store'), [
            'title' => 'Sample Title',
            'info' => str_repeat('A', 101),
        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors('info');

        $this->assertEquals('The info field must not be greater than 100 characters.', session('errors')->first('info'));

        $this->assertDatabaseMissing('blogs', ['title' => 'Sample Title']);

        $this->assertEquals(0, Blog::count());
    }

    public function test_create_a_blog_failed_null_field()
    {
        $response = $this->post(route('blogs.store'), [
            'title' => null,
            'info' => null,
        ]);

        $response->assertStatus(302);

        $response->assertSessionHasErrors('title', 'info');

        $this->assertEquals('The title field is required.', session('errors')->first('title'));

        $this->assertEquals('The info field is required.', session('errors')->first('info'));

        $this->assertEquals(0, Blog::count());
    }

    public function test_create_a_blog_and_update_non_fillable_field()
    {
        $data = [
            'title' => 'Test Title',
            'info' => 'Test Info',
            'created_at' => '2024/10/23', // not now
            'updated_at' => '2024/10/23', // not now
        ];

        $response = $this->post(route('blogs.store'), $data);

        $response->assertSessionHas('success', 'Blog created successfully.');

        $this->assertEquals(1, Blog::count());

        $this->assertDatabaseMissing('blogs', ['created_at' => '2024/10/23']);

        $this->assertDatabaseMissing('blogs', ['updated_at' => '2024/10/23']);
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

    public function test_update_existent_blog_non_fillable_field()
    {
        $blog = Blog::factory()->create();

        $this->assertDatabaseCount('blogs', 1);

        $data = [
            'title' => 'Updated Title',
            'info' => 'Updated Info',
            'created_at' => '2024/10/25', //not now
            'updated_at' => '2024/10/25', //not now
        ];

        $response = $this->put(route('blogs.update', $blog->id), $data);

        $response->assertSessionHas('success', 'Blog updated successfully.');

        $this->assertDatabaseCount('blogs', 1);

        $this->assertDatabaseMissing('blogs', ['created_at' => '2024/10/25', 'updated_at' => '2024/10/25']);
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

    public function test_blogs_seed_success()
    {
        $this->seed(BlogSeeder::class);

        $expectedCount = 50;

        $this->assertEquals($expectedCount, Blog::get()->count());
    }

    public function test_blogs_seed_failed_count()
    {
        $this->seed(BlogSeeder::class);

        $expectedCount = 60;

        $this->assertNotEquals($expectedCount, Blog::get()->count());
    }

    public function test_blogs_factory_success_count()
    {
        Blog::factory()->times(30)->create();

        $expectedCount = 30;

        $this->assertEquals($expectedCount, Blog::get()->count());

        Blog::factory()->times(40)->create();

        $expectedCount += 40;

        $this->assertEquals($expectedCount, Blog::get()->count());
    }

    public function test_blogs_factory_failed_count()
    {
        Blog::factory()->times(30)->create();

        $expectedCount = 30;

        $this->assertEquals($expectedCount, Blog::get()->count());

        Blog::factory()->times(40)->create();

        // Missed additional count
        $expectedCount += 30;

        $this->assertNotEquals($expectedCount, Blog::get()->count());
    }

    public function test_blogs_pagination_in_home_page()
    {
        $blogs_10 = Blog::factory(10)->create();
        $blogs_20 = Blog::factory(10)->create();

        // Get the first page
        $response = $this->get('/blogs');

        $response->assertStatus(200);

        $titles_10 = $blogs_10->pluck('title')->all();
        $titles_20 = $blogs_20->pluck('title')->all();

        $response->assertSeeTextInOrder($titles_10);

        // Get the second page
        $response = $this->get('/blogs?page=2');

        $response->assertStatus(200);

        $response->assertSeeTextInOrder($titles_20);
    }

    public function test_blogs_pagination_in_home_page_with_non_existent_page()
    {
        $blogs_10 = Blog::factory(10)->create();

        // Get the first page
        $response = $this->get('/blogs');

        $blogsInResponse = $response->original->getData()['blogs'];

        $titles_10 = $blogs_10->pluck('title')->all();

        $response->assertSeeTextInOrder($titles_10);

        $this->assertEquals(count($titles_10), count($blogsInResponse));

        // Get the non existent page
        $response = $this->get('/blogs?page=2');

        $blogsInResponse = $response->original->getData()['blogs'];

        $this->assertEquals(0, count($blogsInResponse));
    }
}
