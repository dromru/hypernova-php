<?php

namespace WF\Hypernova\Tests;

use PHPUnit\Framework\TestCase;
use WF\Hypernova\Job;
use WF\Hypernova\JobResult;
use WF\Hypernova\Plugins\BasePlugin;

class BasePluginTest extends TestCase
{
    public function testPrepareRequest(): void
    {
        $plugin = new BasePlugin();

        $job = Job::fromArray(['name' => 'foo', 'data' => ['bar' => 'baz']]);
        $jobs = [$job];

        $this->assertEquals($jobs, $plugin->prepareRequest($jobs, $jobs));
    }

    public function testAfterResponse(): void
    {
        $plugin = new BasePlugin();

        $jobResult = $this->makeJobResult();
        $this->assertEquals([$jobResult], $plugin->afterResponse([$jobResult]));
    }

    public function testGetViewData(): void
    {
        $plugin = new BasePlugin();

        $data = ['foo' => 'bar'];

        $this->assertEquals($data, $plugin->getViewData('id1', $data));
    }

    public function testShouldSendRequest(): void
    {
        $plugin = new BasePlugin();

        $this->assertTrue($plugin->shouldSendRequest([$this->makeJob()]));
    }

    private function makeJobResult(): JobResult
    {
        return JobResult::fromServerResult(
            ['html' => '<div>stuff</div>', 'error' => null, 'success' => true],
            $this->makeJob()
        );
    }

    private function makeJob(): Job
    {
        return Job::fromArray(['name' => 'foo', 'data' => []]);
    }
}
