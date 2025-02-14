<?php

declare(strict_types=1);

namespace WF\Hypernova\Tests;

use GuzzleHttp\Handler\MockHandler;
use PHPUnit\Framework\TestCase;
use WF\Hypernova\Job;
use WF\Hypernova\JobResult;
use WF\Hypernova\Plugins\Plugin;
use WF\Hypernova\Renderer;
use WF\Hypernova\Plugins\BasePlugin;
use WF\Hypernova\Response;

class RendererTest extends TestCase
{
    public static string $rawServerResponse = '{"success":true,"error":null,"results":{"myView":{"name":"my_component","html":"<div data-hypernova-key=\"my_component\" data-hypernova-id=\"54f9f349-c59b-46b1-9e4e-e3fa17cc5d63\"><div>My Component</div></div>\n<script type=\"application/json\" data-hypernova-key=\"my_component\" data-hypernova-id=\"54f9f349-c59b-46b1-9e4e-e3fa17cc5d63\"><!--{\"foo\":{\"bar\":[],\"baz\":[]}}--></script>","meta":{},"duration":2.501506,"statusCode":200,"success":true,"error":null},"myOtherView":{"name":"my_component","html":"<div data-hypernova-key=\"my_component\" data-hypernova-id=\"54f9f349-c59b-46b1-9e4e-e3fa17cc5d63\"><div>My Component</div></div>\n<script type=\"application/json\" data-hypernova-key=\"my_component\" data-hypernova-id=\"54f9f349-c59b-46b1-9e4e-e3fa17cc5d63\"><!--{\"foo\":{\"bar\":[],\"baz\":[]}}--></script>","meta":{},"duration":2.501506,"statusCode":200,"success":true,"error":null}}}';

    public static string $rawErrorResponse = '{"success":true,"error":null,"results":{"myView":{"name":"nonexistent_component","html":null,"meta":{},"duration":0.521553,"statusCode":404,"success":false,"error":{"name":"ReferenceError","message":"Component \"nonexistent_component\" not registered","stack":["ReferenceError: Component \"nonexistent_component\" not registered","at YOUR-COMPONENT-DID-NOT-REGISTER_nonexistent_component:1:1","at notFound (/src/hypernova/node_modules/hypernova/lib/utils/BatchManager.js:27:15)","at /src/hypernova/node_modules/hypernova/lib/utils/BatchManager.js:178:19","at tryCatcher (/src/hypernova/node_modules/bluebird/js/release/util.js:16:23)","at Promise._settlePromiseFromHandler (/src/hypernova/node_modules/bluebird/js/release/promise.js:510:31)","at Promise._settlePromise (/src/hypernova/node_modules/bluebird/js/release/promise.js:567:18)","at Promise._settlePromiseCtx (/src/hypernova/node_modules/bluebird/js/release/promise.js:604:10)","at Async._drainQueue (/src/hypernova/node_modules/bluebird/js/release/async.js:138:12)","at Async._drainQueues (/src/hypernova/node_modules/bluebird/js/release/async.js:143:10)","at Immediate.Async.drainQueues (/src/hypernova/node_modules/bluebird/js/release/async.js:17:14)","atrunCallback (timers.js:574:20)","at tryOnImmediate (timers.js:554:5)","at processImmediate [as _immediateCallback] (timers.js:533:5)"]}}}}';

    private static string $serverErrorResponse = '{"success":true,"error":"something went wrong","results":{"id1":{"name":"my_component","html":"<div data-hypernova-key=\"my_component\" data-hypernova-id=\"54f9f349-c59b-46b1-9e4e-e3fa17cc5d63\"><div>My Component</div></div>\n<script type=\"application/json\" data-hypernova-key=\"my_component\" data-hypernova-id=\"54f9f349-c59b-46b1-9e4e-e3fa17cc5d63\"><!--{\"foo\":{\"bar\":[],\"baz\":[]}}--></script>","meta":{},"duration":2.501506,"statusCode":200,"success":true,"error":null},"myOtherView":{"name":"my_component","html":"<div data-hypernova-key=\"my_component\" data-hypernova-id=\"54f9f349-c59b-46b1-9e4e-e3fa17cc5d63\"><div>My Component</div></div>\n<script type=\"application/json\" data-hypernova-key=\"my_component\" data-hypernova-id=\"54f9f349-c59b-46b1-9e4e-e3fa17cc5d63\"><!--{\"foo\":{\"bar\":[],\"baz\":[]}}--></script>","meta":{},"duration":2.501506,"statusCode":200,"success":true,"error":null}}}';

    private Renderer $renderer;
    private Job $defaultJob;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->renderer = new Renderer('http://localhost:8080/batch');
        $this->defaultJob = new Job('my_component', ['foo' => ['bar' => [], 'baz' => []]]);
    }

    public function testCreateJobs(): void
    {
        $plugin = $this->createMock(BasePlugin::class);

        $job = ['name' => 'foo', 'data' => ['someData' => []]];

        $plugin->expects($this->once())
            ->method('getViewData')
            ->with($this->equalTo($job['name']), $this->equalTo($job['data']))
            ->willReturn($job['data']);

        $this->renderer->addPlugin($plugin);
        $this->renderer->addJob('id1', $job);

        $this->assertArrayHasKey('id1', $this->callInternalMethodOfThing($this->renderer, 'createJobs'));
    }

    public function testCreateJobsWithMetadata(): void
    {
        $plugin = $this->createMock(BasePlugin::class);

        $job = ['name' => 'foo', 'data' => ['someData' => []], 'metadata' => ['some_other' => 'foo']];

        $plugin->expects($this->once())
            ->method('getViewData')
            ->with($this->equalTo($job['name']), $this->equalTo($job['data']))
            ->willReturn($job['data']);
        $this->renderer->addPlugin($plugin);
        $this->renderer->addJob('id1', $job);
        $createdJobs = $this->callInternalMethodOfThing($this->renderer, 'createJobs');
        $this->assertObjectHasAttribute('metadata', $createdJobs['id1']);
        $this->assertEquals('foo', $createdJobs['id1']->metadata['some_other']);
    }

    public function testMultipleJobsGetCreated(): void
    {
        $plugin = $this->createMock(BasePlugin::class);

        for ($i = 0; $i < 5; $i++) {
            $this->renderer->addJob('id' . $i, $this->defaultJob);
        }

        $plugin->expects($this->exactly(5))
            ->method('getViewData');

        $this->renderer->addPlugin($plugin);

        $this->callInternalMethodOfThing($this->renderer, 'createJobs');
    }

    public function testPrepareRequestCallsPlugin(): void
    {
        $plugin = $this->createMock(BasePlugin::class);

        $plugin->expects($this->exactly(2))
            ->method('prepareRequest')
            ->with($this->equalTo([$this->defaultJob]))
            ->willReturn([$this->defaultJob]);

        $this->renderer->addPlugin($plugin);
        $this->renderer->addPlugin($plugin);

        $allJobs = [$this->defaultJob];

        $this->assertEquals($allJobs, $this->callInternalMethodOfThing($this->renderer, 'prepareRequest', [$allJobs])[1]);
    }

    public function testShouldSend(): void
    {
        $pluginDontSend = $this->createMock(BasePlugin::class);
        $pluginDoSend = $this->createMock(BasePlugin::class);

        $pluginDoSend->method('prepareRequest')->will($this->returnArgument(0));
        $pluginDontSend->method('prepareRequest')->will($this->returnArgument(0));

        $pluginDontSend->expects($this->once())
            ->method('shouldSendRequest')
            ->willReturn(false);

        $pluginDoSend->expects($this->never())
            ->method('shouldSendRequest');

        $this->renderer->addPlugin($pluginDontSend);
        $this->renderer->addPlugin($pluginDoSend);

        $result = $this->callInternalMethodOfThing($this->renderer, 'prepareRequest', [[$this->defaultJob]]);
        $this->assertEquals([$this->defaultJob], $result[1]);
        $this->assertFalse($result[0]);
    }

    public function testRenderShouldNotSend(): void
    {
        $renderer = $this->getMockedRenderer(false);

        $plugin = $this->createMock(BasePlugin::class);

        foreach (['willSendRequest', 'onError', 'onSuccess', 'afterResponse'] as $methodThatShouldNotBeCalled) {
            $plugin->expects($this->never())
                ->method($methodThatShouldNotBeCalled);
        }

        $renderer->addPlugin($plugin);

        /**
         * @var Response $response
         */
        $response = $renderer->render();

        $this->assertInstanceOf(Response::class, $response);
        self::assertNull($response->error);

        $this->assertStringStartsWith('<div data-hypernova-key="my_component"', $response->results['id1']->html);
    }

    public function testGetViewDataHandlesExceptions(): void
    {
        $plugin = $this->createMock(BasePlugin::class);

        $plugin->expects($this->once())
            ->method('getViewData')
            ->willThrowException(new \Exception('something went wrong'));

        $plugin->expects($this->once())
            ->method('onError');

        $this->renderer->addJob('id1', $this->defaultJob);
        $this->renderer->addPlugin($plugin);

        $this->assertEquals(['id1' => $this->defaultJob], $this->callInternalMethodOfThing($this->renderer, 'createJobs'));
    }


    /**
     * @dataProvider errorPluginProvider
     */
    public function testPrepareRequestErrorsCauseFallback(Plugin $plugin): void
    {
        $renderer = $this->getMockBuilder(Renderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['createJobs'])
            ->getMock();

        $renderer->expects($this->once())
            ->method('createJobs')
            ->willReturn(['id1' => $this->defaultJob]);

        $renderer->addPlugin($plugin);

        /**
         * @var Response $response
         */
        $response = $renderer->render();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->error);

        $this->assertStringStartsWith('<div data-hypernova-key="my_component"', $response->results['id1']->html);
    }

    public function errorPluginProvider(): array
    {
        $pluginThatThrowsInPrepareRequest = $this->createMock(BasePlugin::class);

        $pluginThatThrowsInPrepareRequest->expects($this->once())
            ->method('prepareRequest')
            ->willThrowException(new \Exception('Exception in prepare request'));

        $pluginThatThrowsInShouldSendRequest = $this->createMock(BasePlugin::class);

        $pluginThatThrowsInShouldSendRequest->expects($this->once())
            ->method('shouldSendRequest')
            ->willThrowException(new \Exception('Exception in should send request'));

        foreach ([$pluginThatThrowsInPrepareRequest, $pluginThatThrowsInShouldSendRequest] as $plugin) {
            foreach (['willSendRequest', 'onError', 'onSuccess', 'afterResponse'] as $methodThatShouldNotBeCalled) {
                $plugin->expects($this->never())
                    ->method($methodThatShouldNotBeCalled);
            }
        }

        return [
            [$pluginThatThrowsInPrepareRequest],
            [$pluginThatThrowsInShouldSendRequest]
        ];
    }

    public function testWillSendRequest(): void
    {
        $renderer = $this->getMockedRenderer(true);

        $plugin = $this->createMock(BasePlugin::class);

        $plugin->expects($this->once())
            ->method('willSendRequest')
            ->with($this->equalTo(['id1' => $this->defaultJob]));

        $renderer->addPlugin($plugin);

        $renderer->render();
    }

    public function testOnSuccess(): void
    {
        $renderer = $this->getMockedRenderer(true);

        $plugin = $this->createMock(BasePlugin::class);

        $plugin->expects($this->exactly(2))
            ->method('onSuccess');

        $renderer->addPlugin($plugin);

        $renderer->render();
    }

    public function testAfterResponse(): void
    {
        $renderer = $this->getMockedRenderer(true);

        $plugin = $this->createMock(BasePlugin::class);

        $plugin->expects($this->once())
            ->method('afterResponse');

        $renderer->addPlugin($plugin);

        $renderer->render();
    }

    public function testOnErrorInFinalize(): void
    {
        $renderer = $this->getMockedRenderer(true, 200, 'doRequest');

        $renderer->expects($this->once())
            ->method('doRequest')
            ->willReturn(['id1' => JobResult::fromServerResult(
                ['success' => false, 'error' => 'an error!', 'html' => null],
                $this->defaultJob
            )]);

        $plugin = $this->createMock(BasePlugin::class);

        $plugin->expects($this->once())
            ->method('onError')
            ->with($this->equalTo('an error!'), $this->anything());

        $renderer->addPlugin($plugin);

        $renderer->render();
    }

    public function testExceptionInMakeRequest(): void
    {
        $renderer = $this->getMockedRenderer(true, 500, 'fallback');

        $renderer->expects($this->once())
            ->method('fallback');

        $renderer->render();
    }

    public function testServerMissingResponse(): void
    {
        $reallyBadServerError = '{"success":true,"error":"something went wrong","results":{}';

        $renderer = $this->getMockedRenderer(true, 200, [], $reallyBadServerError);

        $response = $renderer->render();

        $this->assertStringStartsWith('<div data-hypernova-key="my_component"', $response->results['id1']->html);
    }

    public function testServerTopLevelError(): void
    {
        $renderer = $this->getMockedRenderer(true, 200, [], self::$serverErrorResponse);

        $plugin = $this->createMock(BasePlugin::class);

        $plugin->expects($this->atLeastOnce())
            ->method('onError')
            ->with($this->equalTo('something went wrong'), $this->anything());

        $renderer->addPlugin($plugin);

        $response = $renderer->render();

        $this->assertStringStartsWith('<div data-hypernova-key="my_component"', $response->results['id1']->html);
    }

    /**
     * @param string|array $additionalMockMethods
     * @return \PHPUnit\Framework\MockObject\MockObject|Renderer
     */
    private function getMockedRenderer(
        bool $shouldSendRequest,
        int $clientResponseCode = 200,
        $additionalMockMethods = [],
        ?string $clientResponse = null
    ) {
        // Secret sauce so we don't have to mock our HTTP client
        $mockHandler = new MockHandler(
            [
                new \GuzzleHttp\Psr7\Response(
                    $clientResponseCode,
                    [],
                    $clientResponseCode === 200 ? ($clientResponse ?: self::$rawServerResponse) : null
                )
            ]
        );
        $handler = \GuzzleHttp\HandlerStack::create($mockHandler);

        $renderer = $this->getMockBuilder(Renderer::class)
            ->setConstructorArgs(['http://localhost:8080/batch', [], ['handler' => $handler]])
            ->setMethods(array_merge(['prepareRequest'], (array)$additionalMockMethods))
            ->getMock();

        $renderer->expects($this->once())
            ->method('prepareRequest')
            ->willReturn([$shouldSendRequest, ['id1' => $this->defaultJob]]);

        $renderer->addJob('myView', $this->defaultJob);
        $renderer->addJob('myOtherView', $this->defaultJob);

        return $renderer;
    }

    /**
     * Because I don't believe you should refrain from testing private/protected members.
     *
     * @param mixed $instance
     * @param string $methodName
     * @param array $args
     * @return mixed
     */
    private function callInternalMethodOfThing($instance, $methodName, $args = [])
    {
        $reflector = new \ReflectionObject($instance);
        $method = $reflector->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invoke($instance, ...$args);
    }
}
