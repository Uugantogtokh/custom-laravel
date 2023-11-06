<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\Blog;

class CreatePageTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_create_page_from_index(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/blogs')
                    ->clickLink('Add Blog')
                    ->assertPathIs('/blogs/create');
        });
    }

    public function test_create_page_correct_structure(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/blogs/create')
                    ->assertTitle('Create Blog')
                    ->assertSee('Add a BLog')
                    ->assertSee('Title')
                    ->assertSee('Body')
                    ->assertSee('Create Blog');
        });
    }

    public function test_create_page_create_a_blog_success(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/blogs/create')
                    ->type('title', 'Test Title')
                    ->type('info', 'Test Info')
                    ->press('Create Blog')
                    ->assertPathIs('/blogs')
                    ->assertSee('Blog created successfully.');

            $browser->visit('/blogs')
                    ->assertSee('Test Title')
                    ->assertSee('Test Info');
        });
    }

    public function test_create_page_create_a_blog_success_exact_max_char_fields(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/blogs/create')
                    ->type('title', str_repeat('A', 20))
                    ->type('info', str_repeat('B', 100))
                    ->press('Create Blog')
                    ->assertPathIs('/blogs')
                    ->assertSee('Blog created successfully.');

            $browser->visit('/blogs')
                    ->assertSee(str_repeat('A', 20))
                    ->assertSee(str_repeat('B', 100));
        });
    }

    public function test_create_page_create_a_blog_failed_null_title(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/blogs/create')
                    ->type('title', '')
                    ->type('info', 'Test Info')
                    ->press('Create Blog')
                    ->assertPathIs('/blogs/create');
        });
    }

    public function test_create_page_create_a_blog_failed_null_info(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/blogs/create')
                    ->type('title', 'Test Title')
                    ->type('info', '')
                    ->press('Create Blog')
                    ->assertPathIs('/blogs/create');
        });
    }

    public function test_create_page_create_a_blog_failed_unique_title(): void
    {
        Blog::factory()->create([
            'title' => 'Test Title',
            'info' => 'Test Info',
        ]);
        $this->browse(function (Browser $browser) {
            $browser->visit('/blogs/create')
                    ->type('title', 'Test Title')
                    ->type('info', 'Test Info')
                    ->press('Create Blog')
                    ->waitForText('The title is not unique.')
                    ->assertDontSee('Test Title')
                    ->assertDontSee('Test Info')
                    ->assertPathIs('/blogs/create');
        });
    }

    public function test_create_page_create_a_blog_failed_exceed_max_char_title(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/blogs/create')
                    ->type('title', str_repeat('A', 21))
                    ->type('info', 'Test Info')
                    ->press('Create Blog')
                    ->waitForText('The title field must not be greater than 20 characters.')
                    ->assertDontSee('Test Info')
                    ->assertPathIs('/blogs/create');
        });
    }

    public function test_create_page_create_a_blog_failed_exceed_max_char_info(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/blogs/create')
                    ->type('title', 'Test Title')
                    ->type('info', str_repeat('A', 101))
                    ->press('Create Blog')
                    ->waitForText('The info field must not be greater than 100 characters.')
                    ->assertDontSee('Test Title')
                    ->assertPathIs('/blogs/create');
        });
    }
}
