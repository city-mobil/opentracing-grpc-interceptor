# opentracing-grpc-interceptor

Grpc client interceptor for PHP makes it easy to add OpenTracing support to gRPC-based calls.

Currently supports only `UnaryUnary` calls.

### Usage example
```php
use Grpc\Internal\InterceptorChannel;
use OpenTracingInterceptor\ClientInterceptor;
use Acme\Tracer;
use Foo\Bar\SomeGrpcClient;
use Foo\Bar\SomeGrpcMethodArgs;

$host = 'grpc.server.com:1313';
$tracer =  new Tracer();

$channel = SomeGrpcClient::getDefaultChannel($host, [
    'credentials' => ChannelCredentials::createInsecure(),
]);
$openTracingInterceptor = new ClientInterceptor($tracer);
$interceptor = new InterceptorChannel($channel, $openTracingInterceptor);

$rootSpan = $tracer->startSpan("rootSpan");

$client = new SomeGrpcClient($host, [], $interceptor);
$args = new SomeGrpcMethodArgs();
$options = [
    'child_of' => $rootSpan
];
$request = $client->SomeGrpcMethodCall($args, [], $options);

/** @var $result GrpcCallResponse */
[$result, $code] = $request->wait();
echo $result->getPayload();

$rootSpan->finish();
$tracer->flush();
```

## Reference

[OpenTracing](https://opentracing.io/)

[Jaeger](https://uber.github.io/jaeger/)

## Disclaimer

All information and source code are provided AS-IS, without express or implied warranties. 
Use of the source code or parts of it is at your sole discretion and risk. 
Citymobil LLC takes reasonable measures to ensure the relevance of the information posted in this repository, but it does not assume responsibility for maintaining or updating this repository or its parts outside the framework established by the company independently and without notifying third parties.
