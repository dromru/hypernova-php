<?php

namespace WF\Hypernova\Plugins;

use WF\Hypernova\JobResult;

class DevModePlugin extends BasePlugin
{
    public function afterResponse($jobResults): array
    {
        return array_map([$this, 'wrapErrors'], $jobResults);
    }

    /**
     * @param JobResult $jobResult
     *
     * @return string|JobResult
     */
    protected function wrapErrors(JobResult $jobResult)
    {
        if (!$jobResult->error) {
            return $jobResult;
        }

        list($message, $formattedStack) = $this->formatError($jobResult->error);

        // technically for purity we should make a new JobResult here rather than mutating the old one, but... eh.
        $jobResult->html = sprintf(
            '<div style="background-color: #ff5a5f; color: #fff; padding: 12px;">
                <p style="margin: 0">
                  <strong>Development Warning!</strong>
                  The <code>%s</code> component failed to render with Hypernova. Error stack:
                </p>
                <ul style="padding: 0 20px">
                    %s
                    %s
                </ul>
            </div>
            %s',
            $jobResult->originalJob->name,
            $message,
            $formattedStack,
            $jobResult->html
        );

        return $jobResult;
    }

    /**
     * @param mixed $error
     */
    protected function formatError($error): array
    {
        return [
            !empty($error['message']) ? '<li><strong>' . $error['message'] . '</strong></li>' : '',
            !empty($error['stack']) ? '<li>' . implode('</li><li>', $error['stack']) . '</li>' : ''
        ];
    }
}
