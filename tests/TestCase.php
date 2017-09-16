<?php

namespace Sinevia\LaravelMediaManager\Test;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as TestMaster;

class TestCase extends TestMaster
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../../../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        return $app;
    }

    /**
     * @test
     */
    public function existsDir()
    {
        $app = app()->make('Sinevia\LaravelMediaManager\Controllers\MediaController');
        $true = $app->existsDir($app->disk, 'test');
        $this->assertTrue($true);
    }

    /**
     * @test
     */
    public function makeDir()
    {
        $app = app()->make('Sinevia\LaravelMediaManager\Controllers\MediaController');
        $true = $app->makeDir($app->disk, 'test');
        $this->assertTrue($true);
    }

    /**
     * @test
     */
    public function removeDir()
    {
        $app = app()->make('Sinevia\LaravelMediaManager\Controllers\MediaController');
        $true = $app->removeDir($app->disk, 'test');
        $this->assertTrue($true);
    }

    /**
     * @test
     */
    public function removeFile()
    {
        $app = app()->make('Sinevia\LaravelMediaManager\Controllers\MediaController');
        $true = $app->removeFile($app->disk, 'test/test.png');
        $this->assertTrue($true);
    }

    /**
     * @test
     */
    public function getDirectories()
    {
        $app = app()->make('Sinevia\LaravelMediaManager\Controllers\MediaController');
        $true = $app->getDirectories($app->disk, '/');
        $this->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function getFiles()
    {
        $app = app()->make('Sinevia\LaravelMediaManager\Controllers\MediaController');
        $true = $app->getFiles($app->disk, '/test/');
        $this->assertResponseStatus(200);
    }
}
