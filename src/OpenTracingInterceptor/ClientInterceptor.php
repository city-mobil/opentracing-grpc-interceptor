<?php

declare(strict_types=1);

namespace OpenTracingInterceptor;

use Grpc\Interceptor;
use OpenTracing\Tags;
use OpenTracing\Tracer;
use const OpenTracing\Formats\TEXT_MAP;

class ClientInterceptor extends Interceptor
{
    const COMPONENT = "gRPC";
    /**
     * @var Tracer
     */
    private $tracer;

    public function __construct(Tracer $tracer)
    {
        $this->tracer = $tracer;
    }

    public function interceptUnaryUnary(
        $method,
        $argument,
        $deserialize,
        array $metadata = [],
        array $options = [],
        $continuation
    ) {
        $spanOptions = [
            'tags' => [
                Tags\SPAN_KIND => Tags\SPAN_KIND_RPC_CLIENT,
                Tags\COMPONENT => self::COMPONENT,
            ],
        ];
        if (!empty($options['child_of'])) {
            $spanOptions['child_of'] = $options['child_of'];
        }
        $span = $this->tracer->startSpan(
            $method,
            $spanOptions
        );
        $metadataWriter = new MetadataWriter($metadata);
        $this->tracer->inject($span->getContext(), TEXT_MAP, $metadataWriter);

        try {
            return $continuation($method, $argument, $deserialize, $metadata, $options);
        } catch (\Exception $ex) {
            $span->setTag(Tags\ERROR, true);
            $span->setTag('error_message', $ex->getMessage());
            throw $ex;
        } finally {
            $span->finish();
        }
    }

    public function interceptStreamUnary($method, $deserialize, array $metadata = [], array $options = [], $continuation)
    {
        return parent::interceptStreamUnary($method, $deserialize, $metadata, $options, $continuation);
    }

    public function interceptUnaryStream($method, $argument, $deserialize, array $metadata = [], array $options = [], $continuation)
    {
        return parent::interceptUnaryStream($method, $argument, $deserialize, $metadata, $options, $continuation);
    }

    public function interceptStreamStream($method, $deserialize, array $metadata = [], array $options = [], $continuation)
    {
        return parent::interceptStreamStream($method, $deserialize, $metadata, $options, $continuation);
    }
}
