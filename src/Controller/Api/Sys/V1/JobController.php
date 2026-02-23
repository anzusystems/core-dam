<?php

declare(strict_types=1);

namespace App\Controller\Api\Sys\V1;

use AnzuSystems\CommonBundle\Controller\AbstractJobController;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/job', name: 'sys_job_v1_')]
final class JobController extends AbstractJobController
{
    protected function getViewAcl(): string
    {
        return '';
    }

    protected function getCreateAcl(): string
    {
        return '';
    }

    protected function getDeleteAcl(): string
    {
        return '';
    }
}
