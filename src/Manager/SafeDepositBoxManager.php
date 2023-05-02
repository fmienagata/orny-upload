<?php

namespace UploadBundle\Manager;

use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\NativeHttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

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
    public function sendDocument(array $data = null, bool $verifypeer = false)
    {
        if ((!isset($data['customerCode'])) ||
            (isset($data['customerCode']) && empty($data['customerCode']))
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
        if (isset($data['esc']['certificate'])) {
            $filesystem = new Filesystem();
            $folder = sprintf('%s/%s', $this->parameterBag->get('kernel.cache_dir'), $data['customerCode']);
            $certificateFile = sprintf('%s/certificate.pem', $folder);
            $certificateFile000041 = sprintf('%s/certificate000041.pem', $folder);

            $pfx = sprintf('%s/certificate.pfx', $folder);
            $jks = sprintf('%s/certificate.jks', $folder);
            $p12 = sprintf('%s/certificate.p12', $folder);
            $filesystem->mkdir($folder);
            if ($filesystem->exists($certificateFile)) {
                $filesystem->remove($certificateFile);
            }
            $filesystem->touch($certificateFile);
            $filesystem->appendToFile($certificateFile, $data['esc']['certificate']);
            /*//convert the file pem to pfx
            exec(sprintf(
                'openssl pkcs12 -export -keypbe NONE -certpbe NONE -in %s -inkey %s -out %s',
                $certificateFile,
                $certificateFile,
                $pfx
            ));
            //convert the file pem to jks
            exec(sprintf(
                'openssl pkcs12 -export -keypbe NONE -certpbe NONE -in %s -inkey %s -out %s -name "certificate%s"',
                $certificateFile,
                $certificateFile,
                $p12,
                $data['customerCode']
            ));
            exec(sprintf(
                'keytool -importkeystore -srckeystore %s -srcstoretype pkcs12 -destkeystore %s -keypasswd "doca"',
                $p12,
                $jks
            ));*/
            $options['local_cert'] = $certificateFile;
            $options['local_pk'] = $certificateFile;
        }
        //$options['body'] = base64_encode(file_get_contents('/data/sites/api/src/Resources/pdf/1679585281772_Guide_Usage_Keepass_12.pdf'));

        $response = $this->client->request(
            'POST',
            //$this->getUrlEsc(),
            'https://ESCNODE-410:10443/ESCNODE_410/sessionOpen',
            $options
        );
dump($response);
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->toArray();

        if (empty($values = $content['data'] ?? [])) {
            return $values;
        }

        $parameters = $values['parameters'];

        //set legacy database url
        foreach ($parameters as $parameter) {
            switch ($parameter['type']) {
                case '_DATABASE_LEGACY_URL_':
                    $this->setLegacyDatabase($parameter);
                    break;
                case '_DATABASE_URL_':
                    $this->setDatabase($parameter);
                    break;
                case '_MONGODB_URL_':
                    $this->setMongodb($parameter);
                    break;
                case 'WEBVIS':
                    $this->setVis($parameter);
                    break;
                case 'ESCLASERLIKE':
                    $this->setEscLaserLike($parameter);
                    break;
            }

            if ($this->request->getCurrentRequest()) {
                $this->request->getCurrentRequest()->attributes->set('vault', $this);
            }
        }

        return $parameters;
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
                $urlEsc['box'],
                $urlEsc['room']
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
