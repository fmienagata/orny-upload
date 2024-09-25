<?php

namespace UploadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use UploadBundle\Repository\Document\TicketRepository;

#[AsController]
class TicketController extends AbstractController
{
    public function __construct(
        private TicketRepository $repository,
    ) {
    }
}
