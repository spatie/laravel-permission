<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Route as Router;


use Spatie\Permission\Models\Permission;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:permission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this commend will read all of the routes and add them to permissions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // if you want to exclude some of the routes add here
        $exept=collect(["register"=>"GET|HEAD",
      "register"=>"POST",
      "password.request"=>"GET|HEAD",
      "password.email"=>"POST",
      "password.reset"=>"GET|HEAD",
      "password.reset"=>"POST",
      "login"=>"GET|HEAD",
      "logout"=>"GET|HEAD",
    ]);
        $routes = collect(Route::getRoutes())->map(function ($route) {
            return [
                'host'   => $route->domain(),
                'method' => implode('|', $route->methods()),
                'uri'    => $route->uri(),
                'name'   => $route->getName(),
                'action' => $route->getActionName(),
            ];
        });
        $bar = $this->output->createProgressBar(count($routes));

        foreach ($routes as $route) {
            if (!isset($exept[$route['name']]) && $route['name']!=null) {
                Permission::firstOrCreate(['name' => $route['name']]);
            }
            $bar->advance();
        }
        $bar->finish();
    }
}
