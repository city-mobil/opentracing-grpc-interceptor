<?php
declare(strict_types=1);

namespace OpenTracingInterceptor;

use OpenTracing\Mock\MockTracer;
use OpenTracing\Tags;
use PHPUnit\Framework\TestCase;
use const OpenTracing\Formats\TEXT_MAP;

class ClientInterceptorTest extends TestCase
{
    private const OPERATION_NAME = 'test_name';

    public function testSuccessInterceptUnaryUnary()
    {
        $injector = function ($spanContext, &$carrier) {
        };
        $tracer = new MockTracer([TEXT_MAP => $injector]);

        $rootSpan = $tracer->startSpan(self::OPERATION_NAME);

        $method = "Some\Grpc\Method";
        $arguments = 'dummyArgs';
        $deserialize = 'dummyDeserialize';
        $metadata = [
            'someMeta' => ['foo', 'bar']
        ];
        $options = [
            'child_of' => $rootSpan
        ];

        $passMethod = null;
        $passArgument = null;
        $passDeserialize = null;
        $passMetadata = [];
        $passOption = [];

        $continuation = function ($method, $arguments, $deserialize, $metadata, $options) use (&$passMethod, &$passArgument, &$passDeserialize, &$passMetadata, &$passOption) {
            $passMethod = $method;
            $passArgument = $arguments;
            $passDeserialize = $deserialize;
            $passMetadata = $metadata;
            $passOption = $options;
        };

        $interceptor = new ClientInterceptor($tracer);

        $interceptor->interceptUnaryUnary(
            $method,
            $arguments,
            $deserialize,
            $metadata,
            $options,
            $continuation
        );

        $this->assertEquals($method, $passMethod);
        $this->assertEquals($arguments, $passArgument);
        $this->assertEquals($deserialize, $passDeserialize);
        $this->assertEquals($options, $passOption);

        $span = $tracer->getSpans()[1];
        $this->assertEquals($span->getOperationName(), $method);

        $tags = $span->getTags();
        $this->assertArrayHasKV($tags, Tags\SPAN_KIND, Tags\SPAN_KIND_RPC_CLIENT);
        $this->assertArrayHasKV($tags, Tags\COMPONENT, ClientInterceptor::COMPONENT);
    }

    public function testFailInterceptUnaryUnary()
    {
        $injector = function ($spanContext, &$carrier) {
        };
        $tracer = new MockTracer([TEXT_MAP => $injector]);

        $rootSpan = $tracer->startSpan(self::OPERATION_NAME);

        $method = "Some\Grpc\Method";
        $arguments = 'dummyArgs';
        $deserialize = 'dummyDeserialize';
        $metadata = [
            'someMeta' => ['foo', 'bar']
        ];
        $options = [
            'child_of' => $rootSpan
        ];
        $errorMessage = "some error happen";

        $passMethod = null;
        $passArgument = null;
        $passDeserialize = null;
        $passMetadata = [];
        $passOption = [];

        $continuation = function ($method, $arguments, $deserialize, $metadata, $options) use (&$passMethod, &$passArgument, &$passDeserialize, &$passMetadata, &$passOption, $errorMessage) {
            $passMethod = $method;
            $passArgument = $arguments;
            $passDeserialize = $deserialize;
            $passMetadata = $metadata;
            $passOption = $options;
            throw new \Exception($errorMessage);
        };

        $interceptor = new ClientInterceptor($tracer);

        try {
            $interceptor->interceptUnaryUnary(
                $method,
                $arguments,
                $deserialize,
                $metadata,
                $options,
                $continuation
            );
        } catch (\Exception $ex) {
        }

        $this->assertEquals($method, $passMethod);
        $this->assertEquals($arguments, $passArgument);
        $this->assertEquals($deserialize, $passDeserialize);
        $this->assertEquals($options, $passOption);


        $span = $tracer->getSpans()[1];
        $this->assertEquals($span->getOperationName(), $method);

        $tags = $span->getTags();

        $this->assertArrayHasKey(Tags\COMPONENT, $tags);
        $this->assertArrayHasKV($tags, Tags\SPAN_KIND, Tags\SPAN_KIND_RPC_CLIENT);
        $this->assertArrayHasKV($tags, Tags\COMPONENT, ClientInterceptor::COMPONENT);
        $this->assertArrayHasKV($tags, Tags\ERROR, true);
        $this->assertArrayHasKV($tags, 'error_message', $errorMessage);
    }

    /**
     * @param array $array
     * @param $key
     * @param $value
     * @throw InvalidArgumentException
     * @throw ExpectationFailedException
     */
    private function assertArrayHasKV(array $array, $key, $value)
    {
        $this->assertArrayHasKey($key, $array);
        $this->assertEquals($array[$key], $value);
    }
}
