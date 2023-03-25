<?php

namespace UploadBundle\Manager;

class SafeDepositBoxManager
{
    public function __construct(
        private HttpClientInterface $client
    ) {}
}