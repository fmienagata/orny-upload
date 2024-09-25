<?php

namespace UploadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use UploadBundle\Manager\SafeDepositBoxManager;

class SafeDepositBoxController extends AbstractController
{
    public function __construct(
        private SafeDepositBoxManager $safeDepositBoxManager,
    ) {
    }

    #[Route(
        '/upload/safe_deposit_box',
        name: 'safe_deposit_box_post',
        methods: ['POST']
    )]
    public function Post(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $instance = $data['instance'];
        $coffre = $data['ESC'];

        $coffre = $this->safeDepositBoxManager->getRoomId($coffre);

        $data = $this->safeDepositBoxManager->createBox($coffre, $instance);

        if (array_key_exists('error', $data)) {
            return $this->json($data, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json($data, Response::HTTP_OK);
    }
}
