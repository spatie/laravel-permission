<?php namespace Spatie\Permission\Commands;
 
use Illuminate\Routing\Route;
use Illuminate\Foundation\Console\RouteListCommand;
 
class CheckRoutePermission extends RouteListCommand
{
 
    /**
     * {@inheritdoc}
     */
    protected $name = 'permission:route:permission';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Table of all routes that do not have a permission';

    /**
     * {@inheritdoc}
     */
    protected $headers = ['method', 'uri', 'name', 'controller', 'action', 'middleware'];

    /**
     * {@inheritdoc}
     */
    protected function getRouteInformation(Route $route)
    {
        $actions = explode('@',$route->getActionName());
        $middleware = implode(',',$route->middleware());

        if(!strpos($middleware, 'permission')) {
            return $this->filterRoute([
                'method' => implode('|', $route->methods()),
                'uri'    => $route->uri(),
                'name'   => is_string($route->getName()) ? "<fg=green>{$route->getName()}</>" : "-",
                'controller' => isset($actions[0]) ? "<fg=cyan>{$actions[0]}</>" : "-",
                'action' => isset($actions[1]) ? "<fg=red>{$actions[1]}</>" : "-",
                'middleware' => $middleware
            ]);
        }
    }
}
