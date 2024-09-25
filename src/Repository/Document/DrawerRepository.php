<?php

namespace UploadBundle\Repository\Document;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use UploadBundle\Document\Drawer;

#[\Attribute]
class DrawerRepository extends DocumentRepository
{
    public function __construct(DocumentManager $dm)
    {
        $uow = $dm->getUnitOfWork();
        $classMetadata = $dm->getClassMetadata(Drawer::class);

        parent::__construct($dm, $uow, $classMetadata);
    }

    /**
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function saveDrawer(array $data = [], ?Drawer $drawer = null): Drawer
    {
        if (empty($data)) {
            return $drawer;
        }

        if (empty($drawer)) {
            $drawer = new Drawer();
        }

        if (isset($data['roomId'])) {
            $oldDrawer = $this->dm->getRepository(Drawer::class)->findOneBy([
                'roomId' => $data['roomId'],
            ]);

            if (!empty($oldDrawer)) {
                $drawer = $oldDrawer;
                unset($data['escName']);
                unset($data['userIdCmd']);
                unset($data['timestamp']);
            }
        }

        foreach ($data as $property => $value) {
            $method = sprintf('set%s', ucfirst($property));
            if (method_exists(Drawer::class, $method)) {
                if ('timestamp' === $property) {
                    $value = new \DateTime($value);
                }
                $drawer->$method($value);
            }
        }

        $this->dm->persist($drawer);
        $this->dm->flush();

        return $drawer;
    }
}
