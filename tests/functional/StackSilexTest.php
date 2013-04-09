<?php

namespace functional;

use Silex\Application;
use Stack\Builder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class StackSilexTest extends \PHPUnit_Framework_TestCase
{
    public function testStackedSilexApplication()
    {
        $app = new Application();

        $app->get('/', function(Request $request) {
            if ('success' === $request->attributes->get('callable_middleware')) {
                return new Response('SUCCESS');
            }

            return new Response('FAILED', 500);
        });

        $inlineMiddleware = function(
            HttpKernelInterface $app,
            Request $request,
            $type = HttpKernelInterface::MASTER_REQUEST,
            $catch = true
        ) {
            $request->attributes->set('callable_middleware', 'success');

            $response = $app->handle($request, $type, $catch);
            $response->setContent('['.$response->getContent().']');

            return $response;
        };

        $stack = (new Builder())
            ->push('Stack\Inline', $inlineMiddleware);

        $app = $stack->resolve($app);

        $response = $app->handle(Request::create('/'));

        $this->assertEquals('[SUCCESS]', $response->getContent());
    }
}
