<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\Blog;

class EditPageTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_edit_page_from_index(): void
    {
        $this->browse(function (Browser $browser) {
            $blog = Blog::factory()->create([
                'title' => 'Test Title',
                'info' => 'Test Info',
            ]);

            $browser->visit('/blogs')
                ->click('.btn.btn-primary.btn-sm.me-2', 1)
                ->waitForText('Update Blog', 10)
                ->assertPathIs("/blogs/{$blog->id}/edit");
        });
    }

    public function test_edit_page_correct_structure(): void
    {
        $this->browse(function (Browser $browser) {
            $blog = Blog::factory()->create([
                'title' => 'Test Title',
                'info' => 'Test Info',
            ]);

            $browser->visit("/blogs/{$blog->id}/edit")
                ->assertTitle('Edit Blog')
                ->assertInputValue('title', 'Test Title')
                ->assertInputValue('info', 'Test Info')
                ->assertSee('Update Blog')
                ->assertSee('Add Blog');
        });
    }

    public function test_edit_page_update_a_blog_success(): void
    {
        $this->browse(function (Browser $browser) {
            $blog = Blog::factory()->create([
                'title' => 'Test Title',
                'info' => 'Test Info',
            ]);
            $browser->visit("/blogs/{$blog->id}/edit")
                    ->assertInputValue('title', 'Test Title')
                    ->assertInputValue('info', 'Test Info')
                    ->type('title', 'Updated Title')
                    ->type('info', 'Updated Info')
                    ->press('Update Blog')
                    ->assertPathIs('/blogs')
                    ->assertSee('Blog updated successfully.');

            $browser->visit('/blogs')
                    ->assertSee('Updated Title')
                    ->assertSee('Updated Info');
        });
    }

    public function test_edit_page_update_a_blog_success_exact_max_char_fields(): void
    {
        $this->browse(function (Browser $browser) {
            $blog = Blog::factory()->create([
                'title' => 'Test Title',
                'info' => 'Test Info',
            ]);
            $browser->visit("/blogs/{$blog->id}/edit")
                    ->assertInputValue('title', 'Test Title')
                    ->assertInputValue('info', 'Test Info')
                    ->type('title', str_repeat('A', 20))
                    ->type('info', str_repeat('B', 100))
                    ->press('Update Blog')
                    ->assertPathIs('/blogs')
                    ->assertSee('Blog updated successfully.');

            $browser->visit('/blogs')
                    ->assertSee(str_repeat('A', 20))
                    ->assertSee(str_repeat('B', 100));
        });
    }

    public function test_edit_page_update_a_blog_failed_null_title(): void
    {
        $this->browse(function (Browser $browser) {
            $blog = Blog::factory()->create([
                'title' => 'Test Title',
                'info' => 'Test Info',
            ]);
            $browser->visit("/blogs/{$blog->id}/edit")
                    ->assertInputValue('title', 'Test Title')
                    ->assertInputValue('info', 'Test Info')
                    ->type('title', '')
                    ->press('Update Blog')
                    ->assertPathIs("/blogs/{$blog->id}/edit");
        });
    }

    public function test_edit_page_update_a_blog_failed_null_info(): void
    {
        $this->browse(function (Browser $browser) {
            $blog = Blog::factory()->create([
                'title' => 'Test Title',
                'info' => 'Test Info',
            ]);
            $browser->visit("/blogs/{$blog->id}/edit")
                    ->assertInputValue('title', 'Test Title')
                    ->assertInputValue('info', 'Test Info')
                    ->type('info', '')
                    ->press('Update Blog')
                    ->assertPathIs("/blogs/{$blog->id}/edit");
        });
    }

    public function test_edit_page_update_a_blog_failed_unique_title(): void
    {
        Blog::factory()->create([
            'title' => 'Test Title 1',
            'info' => 'Test Info 1',
        ]);
        $this->browse(function (Browser $browser) {
            $blog = Blog::factory()->create([
                'title' => 'Test Title 2',
                'info' => 'Test Info 2',
            ]);
            $browser->visit("/blogs/{$blog->id}/edit")
                    ->assertInputValue('title', 'Test Title 2')
                    ->assertInputValue('info', 'Test Info 2')
                    ->type('title', 'Test Title 1')
                    ->press('Update Blog')
                    ->waitForText('The title is not unique.')
                    ->assertInputValue('title', 'Test Title 2')
                    ->assertInputValue('info', 'Test Info 2')
                    ->assertPathIs("/blogs/{$blog->id}/edit");
        });
    }

    public function test_edit_page_update_a_blog_failed_exceed_max_char_title(): void
    {
        $this->browse(function (Browser $browser) {
            $blog = Blog::factory()->create([
                'title' => 'Test Title',
                'info' => 'Test Info',
            ]);
            $browser->visit("/blogs/{$blog->id}/edit")
                    ->assertInputValue('title', 'Test Title')
                    ->assertInputValue('info', 'Test Info')
                    ->type('title', str_repeat('A', 21))
                    ->press('Update Blog')
                    ->waitForText('The title field must not be greater than 20 characters.')
                    ->assertInputValue('title', 'Test Title')
                    ->assertInputValue('info', 'Test Info')
                    ->assertPathIs("/blogs/{$blog->id}/edit");
        });
    }

    public function test_edit_page_update_a_blog_failed_exceed_max_char_info(): void
    {
        $this->browse(function (Browser $browser) {
            $blog = Blog::factory()->create([
                'title' => 'Test Title',
                'info' => 'Test Info',
            ]);
            $browser->visit("/blogs/{$blog->id}/edit")
                    ->assertInputValue('title', 'Test Title')
                    ->assertInputValue('info', 'Test Info')
                    ->type('info', str_repeat('A', 101))
                    ->press('Update Blog')
                    ->waitForText('The info field must not be greater than 100 characters.')
                    ->assertInputValue('title', 'Test Title')
                    ->assertInputValue('info', 'Test Info')
                    ->assertPathIs("/blogs/{$blog->id}/edit");
        });
    }
}
