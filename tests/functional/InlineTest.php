<?php

namespace functional;

use Stack\CallableHttpKernel;
use Stack\Inline;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class InlineTest extends \PHPUnit_Framework_TestCase
{
    public function testWrappingApp()
    {
        $app = new CallableHttpKernel(function(Request $request) {
            if ('success' === $request->attributes->get('callable_middleware')) {
                return new Response('SUCCESS');
            }

            return new Response('FAILED', 500);
        });

        $app = new Inline($app, function(HttpKernelInterface $app, Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true) {
            $request->attributes->set('callable_middleware', 'success');

            $response = $app->handle($request, $type, $catch);

            $response->setContent('['.$response->getContent().']');

            return $response;
        });

        $response = $app->handle(Request::create('/'));
        $this->assertEquals('[SUCCESS]', $response->getContent());
    }
}
