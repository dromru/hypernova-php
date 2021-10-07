<?php

declare(strict_types=1);

namespace WF\Hypernova\Tests;

use PHPUnit\Framework\TestCase;
use WF\Hypernova\Job;

class JobTest extends TestCase
{
    /**
     * @dataProvider badFactoryDataProvider
     */
    public function testThrowsMalformedException(array $arr): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Job::fromArray($arr);
    }

    public function badFactoryDataProvider(): array
    {
        return [
            [[]],
            [[1, 2, 3]],
            [['foo' => 'bar', 'baz' => 'quux']]
        ];
    }

    public function testFactoryPopulates(): void
    {
        $job = Job::fromArray(['name' => 'my_component', 'data' => ['some' => 'data']]);

        $this->assertEquals('my_component', $job->name);
        $this->assertEquals(['some' => 'data'], $job->data);
    }

    public function testFactoryPopulatesWithMetadata(): void
    {
        $job = Job::fromArray(['name' => 'my_component', 'data' => ['some' => 'data'], 'metadata' => ['some_other' => 'metadata']]);

        $this->assertEquals('my_component', $job->name);
        $this->assertEquals(['some' => 'data'], $job->data);
        $this->assertEquals(['some_other' => 'metadata'], $job->metadata);
    }
}
