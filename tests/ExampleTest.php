<?php

namespace JuanMontilla\Scytale\Tests;

use Orchestra\Testbench\TestCase;
use JuanMontilla\Scytale\ScytaleServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [ScytaleServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
