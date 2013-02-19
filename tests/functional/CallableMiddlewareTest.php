<?php

namespace functional;

use Stack\CallableHttpKernel;
use Stack\CallableMiddleware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class CallableMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    public function testSomething()
    {
        $app = new CallableHttpKernel(function(Request $request) {
            if ('success' === $request->attributes->get('callable_middleware')) {
                return new Response('SUCCESS');
            }

            return new Response('FAILED', 500);
        });

        $app = new CallableMiddleware($app, function(HttpKernelInterface $app, Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true) {
            $request->attributes->set('callable_middleware', 'success');

            $response = $app->handle($request, $type, $catch);

            $response->setContent('['.$response->getContent().']');

            return $response;
        });

        $client = new Client($app);

        $client->request('GET', '/');

        $this->assertEquals('[SUCCESS]', $client->getResponse()->getContent());
    }
}
