<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\Blog;

class HomePageTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_home_page_with_no_blogs(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/blogs')
                    ->assertSee('Blogs')
                    ->assertSee('Not have data')
                    ->assertSee('Add Blog')
                    ->assertSee('Actions')
                    ->assertSee('Previous')
                    ->assertSee('1')
                    ->assertSee('Next');
        });
    }

    public function test_home_page_with_blogs(): void
    {
        $blog = Blog::factory()->create([
            'title' => 'Test Title',
            'info' => 'Test Content',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/blogs')
                    ->assertSee('Test Title')
                    ->assertSee('Test Content')
                    ->assertSee('Edit')
                    ->assertSee('Delete');
        });
    }

    public function test_home_page_blogs_order_success(): void
    {
        Blog::factory(10)->create();

        $this->browse(function (Browser $browser) {
            $browser->visit('/blogs');

            $browser->refresh();

            $titles = $browser->script('return Array.from(document.querySelectorAll(".blog-title")).map(e => e.innerText);');

            $this->assertTrue($this->isSorted($titles));
        });
    }

    public function test_home_page_blogs_order_failed(): void
    {
        Blog::factory(10)->create();

        $this->browse(function (Browser $browser) {
            $browser->visit('/blogs');

            $browser->refresh();

            $titlesOnPage = $browser->script('return Array.from(document.querySelectorAll(".blog-title")).map(e => e.innerText);');

            $titlesInDatabase = Blog::orderBy('title', 'asc')->pluck('title')->toArray();

            $this->assertNotEquals($titlesInDatabase, $titlesOnPage);
        });
    }

    public function test_home_page_pagination_success(): void
    {
        Blog::factory(20)->create(); // Create 20 blogs

        $this->browse(function (Browser $browser) {
            $browser->visit('/blogs')
                ->assertVisible('.pagination')
                ->assertSee('Previous')
                ->assertSee('Next');

            $browser->assertSeeIn('.pagination', '1');
            $browser->assertSeeIn('.pagination', '2');

            $browser->click('.pagination .page-item.active + .page-item a')
                    ->assertQueryStringHas('page', '2');

            $browser->assertVisible('.table tbody tr', 10);

            $browser->assertVisible('.table tbody tr:first-child td', 4);
            $browser->assertVisible('.table tbody tr:last-child td', 4);
        });
    }

    // sort function
    protected function isSorted(array $values): bool {
        $sorted = $values;

        sort($sorted);

        return $values === $sorted;
    }
}
