<?php

namespace UploadBundle\Manager;

use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\NativeHttpClient;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

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
            $pfx = sprintf('%s/certificate.pfx', $folder);
            $jks = sprintf('%s/certificate.jks', $folder);
            $p12 = sprintf('%s/certificate.p12', $folder);
            $filesystem->mkdir($folder);
            if ($filesystem->exists($certificateFile)) {
                $filesystem->remove($certificateFile);
            }
            $filesystem->touch($certificateFile);
            $filesystem->appendToFile($certificateFile, $data['esc']['certificate']);
            //convert the file pem to pfx
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
            ));
            /*$options['local_cert'] = $certificateFile;
            $options['local_pk'] = $certificateFile;*/
            $options['http_version'] = 1.1;

        }
        $options['body'] = base64_encode(file_get_contents('/data/sites/api/src/Resources/pdf/1679585281772_Guide_Usage_Keepass_12.pdf'));
        //dump($options);

        $result = exec("/data/sites/api/bin/curl7.19.7 -V 2>&1", $output, $return_var);dump($result);

        $result = exec(sprintf(
            'JVMARGS="$JVMARGS -Djavax.net.debug=ssl,handshake" && \
            JVMARGS="$JVMARGS -Djavax.net.ssl.keyStore=%s -Djavax.net.ssl.keyStorePassword=doca" && \
            java -jar /data/sites/api/bin/jcurl-v1.1.1.jar -e hc https://192.168.186.75:4010/EscBO2/sessionOpen 2>&1',
                $jks
            ),
            $output,
            $return_var
        );dump($result);

        /**
         *
         *
        $ffi = FFI::cdef(
            'unsigned long GetCurrentProcessId(void);',
            "C:\\windows\\system32\\kernel32.dll"
        );
        var_dump($ffi->GetCurrentProcessId());
         *

        $ffi = \FFI::load(
            "/data/sites/api/bin/PAC.Coffre.1.1.0/PAC.Coffre.dll"
        );
         *
        */

        $result = exec(sprintf(
            "/data/sites/api/bin/curl7.19.7 --location --request POST 'https://192.168.186.75:4010/EscBO2/sessionOpen' --tlsv1.2 --cert %s 2>&1",
            $certificateFile
        ), $output, $return_var);dump($result);

        $ch = curl_init();
        try {
            curl_setopt($ch, CURLOPT_URL, "https://192.168.186.75:4010/EscBO2/sessionOpen");
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS | CURLPROTO_HTTP);
            curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTPS);
            curl_setopt($ch, CURLOPT_SSLVERSION, 3);
            curl_setopt($ch, CURLOPT_CAINFO, $certificateFile);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2TLS);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_SSL_FALSESTART, true);
            curl_setopt($ch, CURLOPT_SSL_OPTIONS,CURLSSLOPT_ALLOW_BEAST);
            curl_setopt($ch, CURLOPT_USE_SSL, CURLUSESSL_ALL);

            $response = curl_exec($ch);dump($response);

            if (curl_errno($ch)) {
                return curl_error($ch);
            }

            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if($http_code == intval(200)){
                return $response;
            }
            else{
                return "Ressource introuvable : " . $http_code;
            }
        } catch (\Throwable $th) {
            throw $th;
        } finally {
            curl_close($ch);
        }


        $guzzleClient = new Client();
        $responseGuzzle = $guzzleClient->request(
            'POST',
            'https://192.168.186.75:4010/EscBO2/sessionOpen',
            [
                'cert' => [$certificateFile, $certificateFile]
            ]
        );
       dump($responseGuzzle);
        $client = new CurlHttpClient();
        $response = $client->request('POST',
            'https://192.168.186.75:4010/EscBO2/sessionOpen',
            $options);
        $statusCode = $response->getStatusCode();

        $response = $this->client->request(
            'POST',
            $this->getUrlEsc(),
            $options
        );

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
