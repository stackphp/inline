# Stack/Session

Inline stack middleware.

Enables the usage of callables as stack middlewares.

## Example

Here is a contrived example showing how a callable can be used to easily act
as a stack middleware for a silex application:

    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\HttpKernelInterface;

    $app = new Silex\Application();

    $app->get('/', function(Request $request) {
        if ('success' === $request->attributes->get('callable_middleware')) {
            return new Response('SUCCESS');
        }

        return new Response('FAILED', 500);
    });

    $inlineMiddleware = function(
        HttpKernelInterface $app,
        Request $request,$type = HttpKernelInterface::MASTER_REQUEST, $catch = true
    ) {
        $request->attributes->set('callable_middleware', 'success');

        $response = $app->handle($request, $type, $catch);

        $response->setContent('['.$response->getContent().']');

        return $response;
    };

    $stack = (new Stack\Stack())
        ->push('Stack\Inline', $inlineMiddleware);

    $app = $stack->resolve($app);

## Usage

The method signature for the callable is similar to `HttpKernelInterface::handle`
except that it requires an `HttpKernelInterface` instance as its first argument.
A simple passthru inline middleware would look like this:

    $app = new Stack\Inline($app, function(
        HttpKernelInterface $app,
        Request $request,$type = HttpKernelInterface::MASTER_REQUEST, $catch = true
    ) {
        return $app->handle($request, $type, $catch);
    });
