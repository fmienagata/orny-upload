<?php

namespace UploadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use UploadBundle\Manager\SafeDepositBoxManager;

#[AsController]
class LogLifeCycleInjectController extends AbstractController
{
    use \UploadBundle\Trait\UploadTrait;

    public function __construct(
        private SafeDepositBoxManager $safeDepositBoxManager
    ) {}

    #[Route(
        '/upload/log_lifecycle_inject',
        name: '_upload_/log_lifecycle_inject_post',
        methods: ['POST']
    )]
    public function Post(Request $request, SluggerInterface $slugger): JsonResponse
    {
        $data = \json_decode($request->getContent(), true);
        if ($request->getContentTypeFormat() === 'form') {
            $data = $request->request->all();
            foreach ($data as $k => $v) {
                if (($k == 'paperless' || $k == 'ticket' || $k == 'esc') && is_string($v)) {
                    $data[$k] = is_string($v) ? $this->jsonMinify($v) : $v;
                    $data[$k] = \json_decode($data[$k], true);
                }
            }
        }

        $this->safeDepositBoxManager->setUrlEsc($data['esc']);
        $this->safeDepositBoxManager->setCertificate($data['esc']['certificate']);

        $saveLog = $this->safeDepositBoxManager->saveLog($data);

        if ($saveLog["code"] != 200) {
            return $this->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => $saveLog['message'],
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([$saveLog['message']], Response::HTTP_OK);
    }
}
