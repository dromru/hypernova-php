<?php
/**
 * Created by PhpStorm.
 * User: beroberts
 * Date: 1/14/17
 * Time: 1:04 PM
 */

namespace WF\Hypernova;

final class JobResult
{
    /**
     * @var string
     */
    public $error;

    /**
     * @var string rendered HTML
     */
    public $html;

    /**
     * @var bool
     */
    public $success;

    /**
     * @var Job
     */
    public $originalJob;

    /**
     * @var array
     */
    public $meta;

    /**
     * @var float
     */
    public $duration;

    public static function fromServerResult(?array $serverResult, Job $originalJob): self
    {
        if (empty($serverResult['html']) && empty($serverResult['error'])) {
            throw new \InvalidArgumentException('Server result malformed');
        }

        $res = new self();

        $res->error = $serverResult['error'];
        $res->html = $serverResult['html'];
        $res->success = $serverResult['success'];
        $res->meta = isset($serverResult['meta']) ? $serverResult['meta'] : [];
        $res->duration = isset($serverResult['duration']) ? $serverResult['duration'] : null;
        $res->originalJob = $originalJob;

        return $res;
    }

    /**
     * Convenience: plugins can always pass around JobResults from afterResponse.
     * This lets consumers cast whatever comes out to string to get the markup.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->html;
    }
}
