<?php

declare(strict_types=1);

namespace WF\Hypernova;

class Response
{
    public ?\Exception $error = null;

    /**
     * @var \WF\Hypernova\JobResult[]
     */
    public $results;
}
