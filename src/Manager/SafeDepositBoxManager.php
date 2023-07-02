<?php

namespace UploadBundle\Manager;

use Howtomakeaturn\PDFInfo\PDFInfo;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Archive GET : /EscBO2/764265cb4b5c4682a77ecae8209a31ac/468a2dbad8854f149fc4236f7d864e17/archiveGet
 * Archive POST : /EscBO2/764265cb4b5c4682a77ecae8209a31ac/ed66215af90c4f77b90c0e254879272e/archivePutNoControl
 */
class SafeDepositBoxManager
{
    public function __construct(
        private HttpClientInterface $client,
        private ParameterBagInterface $parameterBag,
        private string $urlEsc = '',
        private string $certificate = ''
    ) {}

    /**
     * @param array|null $data
     * @param bool       $verifypeer
     *
     * @return array|\string[][]|JsonResponse
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function createRoom(array $data = null, bool $verifypeer = false)
    {
        if ((!isset($data['escName'])) ||
            (isset($data['escName']) && empty($data['escName']))
        ) {
            return new JsonResponse([
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => 'No route found, empty uri found.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $options = [
            'verify_peer' => $verifypeer,
            'verify_host' =>  $verifypeer,
        ];
        if (isset($data['version'])) {
            $options['query']['version'] = $data['version'];
        }

        $filesystem = new Filesystem();
        $folder = sprintf('%s/%s', $this->parameterBag->get('kernel.cache_dir'), '9463');
        $utilisateurSysteme = sprintf('%s/utilisateurSysteme.pem', $folder);

        $filesystem->mkdir($folder);
        if ($filesystem->exists($utilisateurSysteme)) {
            /**
             * @todo replace it
             */
        }

        $options['local_cert'] = $utilisateurSysteme;

        //Open session esc
        $response = $this->client->request(
            'POST',
            sprintf(
                '%s://%s:%s/%s/roomCreate',
                $data['esc']['scheme'],
                $data['esc']['dns'],
                $data['esc']['port'],
                $data['esc']['name']
            ),
            $options
        );

        $headers = $response->getHeaders();

        if (empty($sessionId = $headers['esc-sessionid'] ?? [])) {
            return [];
        }

        $data['userIdCmd'] = $headers['esc-useridcmd'][0];
        $options['headers']['ESC-label'] = 'Vehicle';
        $options['headers']['ESC-sessionId'] = $headers['esc-sessionid'];
        /**
         * @todo change to uploaded pem file
         */
        $body = fopen(sprintf(
            '%s/config/esc/certificats/salle1.pem',
            $this->parameterBag->get('kernel.project_dir')
        ), 'r');

        $size = dump(fstat($body)['size'] ?? null);
        $options['headers']['Content-Disposition'] = "attachment; filename=file";
        $options['headers']['Content-Length'] = $size;
        //create room
        $responseRoom = $this->client->request(
            'POST',
            sprintf(
                '%s://%s:%s/%s/roomCreate',
                $data['esc']['scheme'],
                $data['esc']['dns'],
                $data['esc']['port'],
                $data['esc']['name']
            ),
            $options
        );

        $statusCode = $responseRoom->getStatusCode();
        switch ($statusCode) {
            case 500:
                return [];
        }
        $headersRoomCreated = $responseRoom->getHeaders();
        if (empty($roomId = $headersRoomCreated['esc-roomid'] ?? [])) {
            return [];
        }
        $data['roomId'] = $roomId[0];

        return $data;
    }

    /**
     * @param array      $credentials
     * @param array|null $data
     *
     * @return array|mixed|JsonResponse
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function sendDocument(array $data = null, UploadedFile $file = null, bool $verifypeer = false)
    {
        if ((!isset($data['cc'])) ||
            (isset($data['cc']) && empty($data['cc']))
        ) {
            return [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => 'No route found, empty uri found.'
            ];
        }

        $options = [
            'verify_peer' => $verifypeer,
            'verify_host' =>  $verifypeer,
        ];
        if (isset($data['version'])) {
            $options['query']['version'] = $data['version'];
        }
        if (isset($data['esc']['certificate'])) {
            $filesystem = new Filesystem();
            $folder = sprintf('%s/%s', $this->parameterBag->get('kernel.cache_dir'), $data['cc']);
            $certificateFile = sprintf('%s/certificate.pem', $folder);
            $filesystem->mkdir($folder);
            if ($filesystem->exists($certificateFile)) {
                $filesystem->remove($certificateFile);
            }
            $filesystem->touch($certificateFile);
            $filesystem->appendToFile($certificateFile, $data['esc']['certificate']);
            $options['local_cert'] = $certificateFile;
        }

        $response = $this->client->request(
            'POST',
            sprintf(
                '%s://%s:%s/%s/sessionOpen',
                $data['esc']['scheme'],
                $data['esc']['dns'],
                $data['esc']['port'],
                $data['esc']['name']
            ),
            $options
        );

        $statusCode = $response->getStatusCode();
        switch ($statusCode) {
            case 400:
            case 401:
            case 404:
            case 500:
                $data['error']['code'] = $statusCode;
                $data['error']['message'] = $response->getContent();
                return $data;
        }
        $headers = $response->getHeaders();

        if (empty($sessionId = $headers['esc-sessionid'][0] ?? [])) {
            return [];
        }

        $data['userIdCmd'] = $headers['esc-useridcmd'][0];
        $options['headers']['ESC-sessionId'] = $sessionId;
        $options['headers']['ESC-ucsId'] = '';
        $options['headers']['ESC-fileName'] = $file->getFilename();
        $options['headers']['ESC-archiveLabel'] = $file->getFilename();
        $body = fopen($file->getPathname(), 'r');
        $size = fstat($body)['size'] ?? null;
        $options['body'] = $body;
        $options['headers']['Content-Disposition'] = "attachment; filename=" . $file->getFilename();
        $options['headers']['Content-Length'] = $size;

        $responseArchive = $this->client->request(
            'POST',
            sprintf('%sarchivePutNoControl', $this->getUrlEsc()),
            $options
        );

        $statusCode = $responseArchive->getStatusCode();
        switch ($statusCode) {
            case 400:
            case 500:
                return [];
        }
        $headersArchiveCreated = $responseArchive->getHeaders();
        foreach ($headersArchiveCreated as $key => $item) {
            if (strpos($key, 'esc-') !== false) {
                $key = str_replace('esc-', '', $key);
                $data[$key] = trim($item[0]);
            }
        }
        $pdf = new PDFInfo($file->getPathname());
        $pagecount = $pdf->pages;
        $data['associatedDocuments'] = sprintf('E000000000000000/1/%s', $pagecount);
        $data['location'] = sprintf('S/E//%s', $data['archiveid']);
        $data['archiveId'] = $data['archiveid'];
        $data['pages'] = $pagecount;

        return $data;
    }

    /**
     * @param array|null $data
     * @param bool       $verifypeer
     *
     * @return array|JsonResponse|\Symfony\Contracts\HttpClient\ResponseInterface|null
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function findDocument(array $data = null, bool $verifypeer = false)
    {
        if ((!isset($data['cc'])) ||
            (isset($data['cc']) && empty($data['cc']))
        ) {
            return [
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => 'No route found, empty uri found.'
            ];
        }

        $options = [
            'verify_peer' => $verifypeer,
            'verify_host' =>  $verifypeer,
        ];
        if (isset($data['version'])) {
            $options['query']['version'] = $data['version'];
        }
        if (isset($data['esc']['certificate'])) {
            $filesystem = new Filesystem();
            $folder = sprintf('%s/%s', $this->parameterBag->get('kernel.cache_dir'), $data['cc']);
            $certificateFile = sprintf('%s/reader.pem', $folder);
            $filesystem->mkdir($folder);
            if ($filesystem->exists($certificateFile)) {
                $filesystem->remove($certificateFile);
            }
            $filesystem->touch($certificateFile);
            $filesystem->appendToFile($certificateFile, $data['esc']['certificate']);
            $options['local_cert'] = $certificateFile;
        }

        $response = $this->client->request(
            'POST',
            sprintf(
                '%s://%s:%s/%s/sessionOpen',
                $data['esc']['scheme'],
                $data['esc']['dns'],
                $data['esc']['port'],
                $data['esc']['name']
            ),
            $options
        );

        $statusCode = $response->getStatusCode();
        switch ($statusCode) {
            case 400:
            case 401:
            case 404:
            case 500:
                $data['error']['code'] = $statusCode;
                $data['error']['message'] = $response->getContent();
                return $data;
        }
        $headers = $response->getHeaders();

        if (empty($sessionId = $headers['esc-sessionid'][0] ?? [])) {
            return [];
        }

        $data['userIdCmd'] = $headers['esc-useridcmd'][0];
        $options['headers']['ESC-sessionId'] = $sessionId;
        $options['headers']['ESC-ucsId'] = '';
        $options['headers']['ESC-archiveId'] = $data["archiveId"];

        $url = sprintf('%sarchiveGet', $this->getUrlEsc());
        if ($data["type"] == "xml") {
            $url = sprintf('%sarchiveEvidenceGet', $this->getUrlEsc());
        }

        $responseArchive = $this->client->request(
            'POST',
            $url,
            $options
        );

        $statusCode = $responseArchive->getStatusCode();
        switch ($statusCode) {
            case 400:
            case 500:
                return [
                    'code' => $statusCode,
                    'message' => 'The safe deposit bax return an error.',
                ];
        }
        $headersArchiveCreated = $responseArchive->getHeaders();

        foreach ($headersArchiveCreated as $key => $item) {
            if (strpos($key, 'esc-') !== false) {
                $key = str_replace('esc-', '', $key);
                $data[$key] = trim($item[0]);
            }
        }

        return $responseArchive;
    }

    /**
     * @return HttpClientInterface
     */
    public function getClient(): HttpClientInterface
    {
        return $this->client;
    }

    /**
     * @param HttpClientInterface $client
     *
     * @return $this
     */
    public function setClient(HttpClientInterface $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrlEsc(): string
    {
        return $this->urlEsc;
    }

    /**
     * @param string|array $urlEsc
     *
     * @return $this
     */
    public function setUrlEsc(string|array $urlEsc): self
    {
        if (is_array($urlEsc)) {
            $urlEsc = sprintf(
                '%s://%s:%s/%s/%s/%s/',
                $urlEsc['scheme'],
                $urlEsc['dns'],
                $urlEsc['port'],
                $urlEsc['name'],
                $urlEsc['room'],
                $urlEsc['box']
            );
        }

        $this->urlEsc = $urlEsc;

        return $this;
    }

    /**
     * @return string
     */
    public function getCertificate(): string
    {
        return $this->certificate;
    }

    /**
     * @param string $certificate
     *
     * @return $this
     */
    public function setCertificate(string $certificate): self
    {
        $this->certificate = $certificate;

        return $this;
    }
}
