<?php

namespace UploadBundle\Controller;

use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use UploadBundle\Document\Ticket;
use UploadBundle\Repository\Document\TicketRepository;
#[AsController]
class TicketController extends AbstractController
{
    public function __construct(
        private TicketRepository $repository
    ) {}
}
