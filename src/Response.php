<?php

declare(strict_types=1);

namespace WF\Hypernova;

class Response
{
    public ?\Exception $error;

    /**
     * @var \WF\Hypernova\JobResult[]
     */
    public $results;
}
