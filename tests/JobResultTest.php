<?php
/**
 * Created by PhpStorm.
 * User: beroberts
 * Date: 1/15/17
 * Time: 12:07 AM
 */

namespace WF\Hypernova\Tests;

use WF\Hypernova\Job;
use WF\Hypernova\JobResult;

class JobResultTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider badServerResultProvider
     */
    public function testFromServerResultBadData(?array $data): void
    {
        $this->expectException(\InvalidArgumentException::class);
        JobResult::fromServerResult($data, new Job('foo', []));
    }

    public function badServerResultProvider(): array
    {
        return [
            [null],
            [['something' => 'foobar']],
            [['error' => null, 'html' => null]]
        ];
    }

    public function testFromServerResultPopulates(): void
    {
        $originalJob = new Job('foo', []);
        $jobResult = JobResult::fromServerResult(['success' => true, 'html' => '<div>data</div>', 'error' => null], $originalJob);

        $this->assertEquals('<div>data</div>', $jobResult->html);
        $this->assertEmpty($jobResult->error);
        $this->assertEquals($originalJob, $jobResult->originalJob);
    }

    public function testToString(): void
    {
        $originalJob = new Job('foo', []);
        $jobResult = JobResult::fromServerResult(['success' => true, 'html' => '<div>data</div>', 'error' => null], $originalJob);

        $this->assertEquals('<div>data</div>', (string) $jobResult);
    }
}
