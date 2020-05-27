<?php

namespace mikecai\PhpSms;

interface BatchStatus
{
    /**
     * Request SMS Batch status.
     *
     * @param string $batchId
     */
    public function requestBatchSatus($batchId);
}
