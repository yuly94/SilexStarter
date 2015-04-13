<?php

namespace SilexStarter\StaticProxy;

use Illuminate\Support\Facades\Facade as StaticProxy;
use Illuminate\Support\Str;
use Silex\Controller;
use Silex\ControllerCollection;

class Route extends StaticProxy{

    /** controllers context stack */
    protected static $contextStack = [];

    /** before handler stack */
    protected static $beforeHandlerStack = [];

    /** after handler stack */
    protected static $afterHandlerStack = [];


    protected static function getFacadeAccessor(){
        return 'controllers';
    }

    protected static function pushContext(ControllerCollection $context){
        static::$contextStack[] = $context;
    }

    protected static function popContext(){
        return array_pop(static::$contextStack);
    }

    protected static function getContext(){
        if(static::$contextStack){
            return end(static::$contextStack);
        }else{
            return static::$app['controllers'];
        }
    }

    protected static function pushBeforeHandler(\Closure $beforeHandler){
        static::$beforeHandlerStack[] = $beforeHandler;
    }

    protected static function popBeforeHandler(){
        return array_pop(static::$beforeHandlerStack);
    }

    protected static function getBeforeHandler(){
        return static::$beforeHandlerStack;
    }

    protected static function pushAfterHandler(\Closure $afterHandler){
        static::$afterHandlerStack[] = $afterHandler;
    }

    protected static function popAfterHandler(){
        return array_pop(static::$afterHandlerStack);
    }

    protected static function getAfterHandler(){
        return static::$afterHandlerStack;
    }

    protected static function applyRouteOptions(Controller $route, array $option){
        foreach (static::getBeforeHandler() as $before) {
            $route->before($before);
        }

        if(isset($options['before'])){
            $route->before($options['before']);
        }

        foreach (static::getAfterHandler() as $after) {
            $route->after($after);
        }

        if(isset($options['after'])){
            $route->after($options['after']);
        }

        if(isset($options['as'])){
            $route->bind($options['as']);
        }

        return $route;
    }

    public static function match($pattern, $to = null, array $options = []){
        $route = static::getContext()->match($pattern, $to);
        $route = static::applyRouteOptions($route, $options);
        return $route;
    }

    public static function get($pattern, $to = null, array $options = []){
        $route = static::getContext()->get($pattern, $to);
        $route = static::applyRouteOptions($route, $options);
        return $route;
    }

    public static function post($pattern, $to = null, array $options = []){
        $route = static::getContext()->post($pattern, $to);
        $route = static::applyRouteOptions($route, $options);
        return $route;
    }

    public static function put($pattern, $to = null, array $options = []){
        $route = static::getContext()->put($pattern, $to);
        $route = static::applyRouteOptions($route, $options);
        return $route;
    }

    public static function delete($pattern, $to = null, array $options = []){
        $route = static::getContext()->delete($pattern, $to);
        $route = static::applyRouteOptions($route, $options);
        return $route;
    }

    public static function patch($pattern, $to = null, array $options = []){
        $route = static::getContext()->patch($pattern, $to);
        $route = static::applyRouteOptions($route, $options);
        return $route;
    }

    /**
     * Grouping route into controller collection and mount to specific prefix
     * @param  [string]     $prefix             the route prefix
     * @param  [Closure]    $callable           the route collection handler
     * @return [Silex\ControllerCollection]     controller collection that already mounted to $prefix
     */
    public static function group($prefix, \Closure $callable, array $options = []){

        if(isset($options['before'])){
            static::pushBeforeHandler($options['before']);
        }

        if(isset($options['after'])){
            static::pushAfterHandler($options['after']);
        }

        /** push the context to be accessed to callable route */
        static::pushContext(static::$app['controllers_factory']);

        $callable();

        $routeCollection = static::popContext();

        if(isset($options['before'])){
            $before = static::popBeforeHandler();

            $routeCollection->before($before);
        }

        if(isset($options['after'])){
            $after = static::popAfterHandler();

            $routeCollection->after($after);
        }

        static::getContext()->mount($prefix, $routeCollection);

        return $routeCollection;
    }

    /**
     * [resource description]
     * @param  [type] $prefix     [description]
     * @param  [type] $controller [description]
     * @return [type]             [description]
     */
    public static function resource($prefix, $controller, array $options = []){
        $prefix             = '/'.ltrim($prefix, '/');
        $routeCollection    = static::$app['controllers_factory'];
        $routePrefixName    = Str::slug($prefix);

        $resourceRoutes     = [
            'get'           => [
                'pattern'       => '/',
                'method'        => 'get',
                'handler'       => "$controller:index"
            ],
            'get_paginate'  => [
                'pattern'       => "/page/{page}",
                'method'        => 'get',
                'handler'       => "$controller:index"
            ],
            'get_create'    => [
                'pattern'       => "/create",
                'method'        => 'get',
                'handler'       => "$controller:create"
            ],
            'get_edit'      => [
                'pattern'       => "/{id}/edit",
                'method'        => 'get',
                'handler'       => "$controller:edit"
            ],
            'get_show'      => [
                'pattern'       => "/{id}",
                'method'        => 'get',
                'handler'       => "$controller:show"
            ],
            'post'          => [
                'pattern'       => '/',
                'method'        => 'post',
                'handler'       => "$controller:store"
            ],
            'put'           => [
                'pattern'       => "/{id}",
                'method'        => 'put',
                'handler'       => "$controller:update"
            ],
            'delete'        => [
                'pattern'       => "/{id}",
                'method'        => 'delete',
                'handler'       => "$controller:destroy"
            ]
        ];

        foreach ($resourceRoutes as $routeName => $route) {
            $routeCollection->{$route['method']}($route['pattern'], $route['handler'])
                            ->bind($routePrefixName.'_'.$routeName);
        }

        foreach (static::getBeforeHandler() as $before) {
            $routeCollection->before($before);
        }

        if(isset($options['before'])){
            $routeCollection->before($options['before']);
        }

        foreach (static::getAfterHandler() as $after) {
            $routeCollection->after($after);
        }

        if(isset($options['after'])){
            $routeCollection->after($options['after']);
        }

        $currentContext = static::getContext();

        $currentContext->get($prefix, $resourceRoutes['get']['handler']);
        $currentContext->post($prefix, $resourceRoutes['post']['handler']);
        $currentContext->mount($prefix, $routeCollection);

        return $routeCollection;
    }

    /**
     * [controller description]
     * @param  [type] $prefix     [description]
     * @param  [type] $controller [description]
     * @return [type]             [description]
     */
    public static function controller($prefix, $controller, array $options = []){
        $prefix             = '/'.ltrim($prefix, '/');
        $class              = new \ReflectionClass($controller);
        $controllerMethods  = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        $routeCollection    = static::$app['controllers_factory'];
        $uppercase          = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $acceptedMethod     = ['get', 'post', 'put', 'delete', 'head', 'options'];

        foreach ($controllerMethods as $method) {
            $methodName = $method->name;

            if(substr($methodName, 0, 2) != '__'){
                $parameterCount = $method->getNumberOfParameters();

                /** search first method segment until uppercase found */
                $pos        = strcspn($methodName, $uppercase);

                /** the http method get, put, post, etc */
                $httpMethod = substr($methodName, 0, $pos);

                /** the url path, index => getIndex */
                if(in_array($httpMethod, $acceptedMethod)){
                    $urlPath    = Str::snake(strpbrk($methodName, $uppercase));
                }else{
                    $urlPath    = Str::snake($methodName);
                    $httpMethod = 'match';
                }

                /**
                 * Build the route
                 */
                if($urlPath == 'index'){
                    static::getContext()->{$httpMethod}($prefix, $controller.':'.$methodName);
                    $route = $routeCollection->{$httpMethod}('/', $controller.':'.$methodName);
                }else if($parameterCount){
                    $urlPattern = $urlPath;
                    $urlParams  = $method->getParameters();

                    foreach ($urlParams as $param) {
                        $urlPattern .= '/{'.$param->getName().'}';
                    }

                    $route = $routeCollection->{$httpMethod}($urlPattern, $controller.':'.$methodName);

                    foreach ($urlParams as $param) {
                        if($param->isDefaultValueAvailable()){
                            $route->value($param->getName(), $param->getDefaultValue());
                        }
                    }
                }else{
                    $route = $routeCollection->{$httpMethod}($urlPath, $controller.':'.$methodName);
                }

                $route->bind($prefix.'_'.$httpMethod.'_'.strtolower($urlPath));
            }
        }


        foreach (static::getBeforeHandler() as $before) {
            $routeCollection->before($before);
        }

        if(isset($options['before'])){
            $routeCollection->before($options['before']);
        }

        foreach (static::getAfterHandler() as $after) {
            $routeCollection->after($after);
        }

        if(isset($options['after'])){
            $routeCollection->after($options['after']);
        }

        static::getContext()->mount($prefix, $routeCollection);

        return $routeCollection;
    }
}