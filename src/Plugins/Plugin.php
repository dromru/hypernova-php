<?php

namespace WF\Hypernova\Plugins;

use WF\Hypernova\Job;

interface Plugin
{
    /**
     * @param string $name
     * @param array $data
     *
     * @return array
     */
    public function getViewData($name, array $data);

    /**
     * @param Job[]   $jobs
     * @param Job[]   $originalJobs
     * @return Job[]
     */
    public function prepareRequest(array $jobs, array $originalJobs);

    /**
     * @param Job[] $jobs
     * @return bool
     */
    public function shouldSendRequest($jobs);

    /**
     * @param Job[] $jobs
     *
     * @return void
     */
    public function willSendRequest($jobs);

    /**
     * @param \Exception|mixed $error
     * @param Job[] $jobOrJobs
     *
     * @return void
     */
    public function onError($error, array $jobOrJobs);

    /**
     * @param \WF\Hypernova\JobResult $jobResult
     *
     * @return void
     */
    public function onSuccess($jobResult);

    /**
     * @param \WF\Hypernova\JobResult[] $jobResults
     *
     * @return \WF\Hypernova\JobResult[]
     */
    public function afterResponse($jobResults);
}
