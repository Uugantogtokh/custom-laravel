<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class YoutubeTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     */
    public function testExample(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('https://intranet.unimedia.mn/')
                    ->click('#my-signin2');
            $window = collect($browser->driver->getWindowHandles())->last();
            $browser->driver->switchTo()->window($window);
            $browser->type('input[name=identifier]', 'uugantogtokh.otgonbaatar@unimedia.mn')
                    ->press('Next');
            $browser->waitFor('input[type=password]')
                    ->type('input[type=password]', 'Nomunugn_012')
                    ->press('Next')
                    ->pause(1000)
                    ->press('Allow')
                    ->screenshot('intranet');
        });
    }
}
