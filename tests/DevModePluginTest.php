<?php

namespace WF\Hypernova\Tests;

use PHPUnit\Framework\TestCase;
use WF\Hypernova\Job;
use WF\Hypernova\JobResult;
use WF\Hypernova\Plugins\DevModePlugin;

class DevModePluginTest extends TestCase
{
    public function testAfterResponseWithErrors(): void
    {
        $response = json_decode(\WF\Hypernova\Tests\RendererTest::$rawErrorResponse, true);
        $jobResults = array_map(function ($jobResult) {
            return JobResult::fromServerResult($jobResult, Job::fromArray(['name' => 'nonexistent_component', 'data' => []]));
        }, $response['results']);

        $plugin = new DevModePlugin();

        $pluginJobResults = $plugin->afterResponse($jobResults);

        $myJobResultMarkup = (string) $pluginJobResults['myView'];

        $this->assertStringContainsString(
            'The <code>nonexistent_component</code> component failed to render with Hypernova',
            $myJobResultMarkup
        );
        $this->assertStringContainsString(
            'ReferenceError: Component "nonexistent_component" not registered',
            $myJobResultMarkup
        );
        $this->assertStringContainsString(
            'at processImmediate [as _immediateCallback] (timers.js:533:5)',
            $myJobResultMarkup
        );
    }

    public function testAfterResponseWithoutErrors(): void
    {
        $response = json_decode(\WF\Hypernova\Tests\RendererTest::$rawServerResponse, true);

        $jobResults = array_map(function ($jobResult) {
            return JobResult::fromServerResult($jobResult, Job::fromArray(['name' => 'my_component', 'data' => ['foo' => ['bar' => [], 'baz' => []]]]));
        }, $response['results']);

        $plugin = new DevModePlugin();

        $pluginJobResults = $plugin->afterResponse($jobResults);

        $myJobResultMarkup = (string) $pluginJobResults['myView'];

        $this->assertStringNotContainsString('failed to render', $myJobResultMarkup);
        $this->assertStringContainsString('<div>My Component</div>', $myJobResultMarkup);
        $this->assertStringContainsString(
            '<script type="application/json" data-hypernova-key="my_component"',
            $myJobResultMarkup
        );
    }
}
