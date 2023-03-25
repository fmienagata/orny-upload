<?php

namespace UploadBundle\Repository\Document;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use UploadBundle\Document\Ticket;

#[\Attribute]
class TicketRepository extends DocumentRepository
{
    public function __construct(DocumentManager $dm)
    {
        $uow = $dm->getUnitOfWork();
        $classMetadata = $dm->getClassMetadata(Ticket::class);

        parent::__construct($dm, $uow, $classMetadata);
    }
}
