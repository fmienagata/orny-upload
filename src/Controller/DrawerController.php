<?php

namespace UploadBundle\Controller;

use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use UploadBundle\Document\Drawer;
use UploadBundle\Manager\SafeDepositBoxManager;
use UploadBundle\Repository\Document\DrawerRepository;

#[AsController]
class DrawerController extends AbstractController
{
    public function __construct(
        private DrawerRepository $repository,
        private SafeDepositBoxManager $safeDepositBoxManager,
    ) {
    }

    #[Route(
        '/upload/drawers',
        name: '_upload_/drawers_post',
        methods: ['POST'],
        defaults: [
            '_api_resource_class' => Drawer::class,
            '_api_operation_name' => '_upload_/drawers_post',
        ]
    )]
    public function Post(Request $request)
    {
        $data = \json_decode($request->getContent(), true);
        if ('form' === $request->getContentTypeFormat()) {
            $data = $request->request->all();
        }

        try {
            $dataWithRoom = $this->safeDepositBoxManager->createRoom($data);
            $drawer = $this->repository->saveDrawer($dataWithRoom);
        } catch (MongoDBException $mongoDBException) {
            return $this->json([
                'code' => $mongoDBException->getCode(),
                'message' => $mongoDBException->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($drawer, Response::HTTP_CREATED);
    }
}
