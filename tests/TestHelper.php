<?php

namespace Spatie\Permission\Test;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TestHelper
{
    /**
     * @param string $middleware
     * @param object $parameter
     *
     * @return int
     */
    public function testMiddleware( string $middleware, object $parameter): int
    {
        try {
            return $middleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, $parameter)->status();
        } catch (HttpException $e) {
            return $e->getStatusCode();
        }
    }
}
