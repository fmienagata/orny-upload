<?php

namespace UploadBundle\Document;

use ApiPlatform\Doctrine\Odm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\Types\Type;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Symfony\Component\Serializer\Annotation\Groups;
use UploadBundle\Controller\DrawerController;

/**
 * @example :
 * ESC-roomId: bebd850304a643dc8c8d4c981f7b0295
 * ESC-timestamp: 2023-05-06T14:34:28.378+02:00
 * ESC-escName: EscDgtt
 * ESC-userIdCmd: 82c76c4af7e14f41989d0102dd755918
 */
#[ODM\Document(
    collection: 'drawers',
    repositoryClass: "UploadBundle\Repository\Document\DrawerRepository"
)]
#[ApiResource(
    normalizationContext: ['groups' => ['drawers:read']],
    denormalizationContext: ['groups' => ['drawers:write']],
    operations: [
        new GetCollection(),
        new Post(
            name: '_upload_/drawers_post',
            normalizationContext: ['groups' => ['drawers:read']],
            denormalizationContext: ['groups' => ['drawers:write']],
            controller: DrawerController::class,
            inputFormats: [
                'multipart' => ['multipart/form-data'],
                'json' => ['application/json'],
            ],
            openapi: new Model\Operation(
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'roomId' => ['type' => 'string'],
                                    'timestamp' => ['type' => 'date'],
                                    'escName' => ['type' => 'string'],
                                    'userIdCmd' => ['type' => 'string'],
                                    'certificate' => ['type' => 'string'],
                                ],
                            ],
                            'example' => [
                                'roomId' => 'bebd850304a643dc8c8d4c981f7b0295',
                                'timestamp' => '2023-05-06T14:34:28.378+02:00',
                                'escName' => 'EscDgtt',
                                'userIdCmd' => '82c76c4af7e14f41989d0102dd755918',
                                'certificate' => '-----BEGIN CERTIFICATE-----\nMIIDNTCCAh0CFF+e8YMbGATfj3Cc5Tf6C4uMi0J/MA0GCSqGSIb3DQEBCwUAMFcx\nCzAJBgNVBAYTAkZSMRYwFAYDVQQIDA1JbGUtZGUtRnJhbmNlMQ4wDAYDVQQHDAVQ\nYXJpczESMBAGA1UECgwJRE9DQVBPU1RFMQwwCgYDVQQLDANFU0MwHhcNMjMwNDI5\nMTUyMjU1WhcNMzMwNDI2MTUyMjU1WjBXMQswCQYDVQQGEwJGUjEWMBQGA1UECAwN\nSWxlLWRlLUZyYW5jZTEOMAwGA1UEBwwFUGFyaXMxEjAQBgNVBAoMCURPQ0FQT1NU\nRTEMMAoGA1UECwwDRVNDMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA\n0OOVSNFNVOOHIef0ObyV3xRpIb8u4gS7VjAxr4gzo0Qmv8ZRrUzpsC/yu72TZ72X\n6TkzMLHo/yHPFXdrBPdFmXxN/zepD1olTo8Nz3GSCYErMg1s3WNNeTdqY2/4+buc\nWlfeeAXc3N9+RQbQt+g+8CLV1WGqoJngMiHMFD+Q4XogNG+o8Zqdkup5Oi54KJ4n\nLgsLF4ttEvEuqjnxp3qqs56dl6DLnHeyWjw7nxm5EgeFD5BXEG31xPK8NGljG65C\n6CQOBQSKpJn5KIpxUmDGFqQwMlUst4RQRXTkSBXCihRz+T9GDkRM+m/IA4mxm18S\n0+znQuXGAnAX+a9FA3nDRwIDAQABMA0GCSqGSIb3DQEBCwUAA4IBAQA+gdwu3hvB\ntc33LlBUD659x8sXAPPISMe8As3WfoGFLuqSB4herqGj9bUu0jPPZLv1JVkURMG5\nF4ReSAba4FNn6ihP+3Dq1Upb3Ne+8ALQO2ID3HmcJ0B2vp/deLmVJm8XMnFEQuSu\n9voDPjkmtxHxw4zNRymtZ/uy2Dy6SIapGT1mNaxN2YKflZ9Ncc0teuxRrKtSDUv7\ntzh69KeoxbL2O9qk8sV8C5QoavbffrejFEmnYaN2oZGTR3Tl2bR0wh8LnIEfrJdi\nTBD+ftlNbYHot8pum2819aR7iC/1CX64ZPH0pyageAe/fmPjmNLDvOYWSC+1vHIJ\naaut66U+Dlnq\n-----END CERTIFICATE-----\n-----BEGIN RSA PRIVATE KEY-----\nMIIEpAIBAAKCAQEA0OOVSNFNVOOHIef0ObyV3xRpIb8u4gS7VjAxr4gzo0Qmv8ZR\nrUzpsC/yu72TZ72X6TkzMLHo/yHPFXdrBPdFmXxN/zepD1olTo8Nz3GSCYErMg1s\n3WNNeTdqY2/4+bucWlfeeAXc3N9+RQbQt+g+8CLV1WGqoJngMiHMFD+Q4XogNG+o\n8Zqdkup5Oi54KJ4nLgsLF4ttEvEuqjnxp3qqs56dl6DLnHeyWjw7nxm5EgeFD5BX\nEG31xPK8NGljG65C6CQOBQSKpJn5KIpxUmDGFqQwMlUst4RQRXTkSBXCihRz+T9G\nDkRM+m/IA4mxm18S0+znQuXGAnAX+a9FA3nDRwIDAQABAoIBAEX8CbcE0LjhJhQZ\nB30Ujs3Xo8V3rThyR2V/7hWWN1665ZSY2CKMwzqRRezafflizvOHg3wMDzjzVdMm\nWEeopLvgpq0cM9r948PnL8RRk5D1osg5MUEXFQzIoS9Bg5P/5ElsaQ+jPzUM99we\nhW0oNXX05dmAyE7YXwHMyvf5uAuiaQC/j4OxlF3FQL1TXBpUz9wjUH15233vs3MJ\ni1YI/nuGyUCF798V0kN+PdMX/5BKNXzQAM5wgeRHrmZjF5zGkEVAnxLe9zGEAusM\nyARpoM3ovgeYd6SJM/+uiDEQFIAh0Aq1LmR4Ckd9h5vciJkCNmsM1V8aB2d4O2pk\nUJG70skCgYEA7uTamTNxtdke4YyNilzufX+G71qDmwHI+p0eYR0q31hjI5TOikQZ\nnghWt3Ds1micwmMGdSul1fsSL/OkLONam+D6tLhofpGc9ONoWkhe690z37abxqZc\ndbDB3J4tYdDFoCnxzGZEzXfqeVWa3gtgK+4zMN2pOh4iiER6uAbbhXsCgYEA39i1\n7PLtkjCtKvYot9E/o7N+EsBrV+2bJXr11MxAg8xPsjdX9TLx+L4Ke9oReo3JmTyy\nw4oswB22H7qUVi3mxGBPBAe3FYA1ehXG9haLgm+0qTqUKBZApYh2c1E/lOteYPAP\n7+JV3ECRL9Yez2TS9xy3/mE7laIPVno5ipKhwaUCgYEAp6RFmpi1RS12NEVT1jLc\n0Yi6giD8lbws82Fqvi+ftFfsSj3UqsabS4ECiXxfVx7WUrr2/yaAG6cy3h+VqEJr\nhVPd2xBFk/xPLrKeuixrIA18p5MXwdFgtN4zTMRbiHbfGEQXAJ8LzTxHeJoQMZ2+\nAVYI6lPCu5wGE6YqkBWP2x0CgYEAvHbvJSKbVzxYcBV0zt8EX8Wd3hfXyN4qg/H0\nzEd0AgOhDgF+stlD1RPcK868bJy4IaZzNCM/7jiVwD0M60oWQzx7Cn+cbCPX4oQ3\nUbZFm3qFdv31A5NItpYgdJLxn2ZTvfYg1O2RMLHEC6yRoDfZT2kEmM4wppkdpMef\nbsZqHmkCgYBgKArnn761BDekXXWZDBc5aAJJZKvA/lQu71gvUreNuBiiQrrC/Lec\nPesv5uYAB+HgZpXR29e1/WywSD6zNXuPAzcECShRBJqqjohq2zyUfXOE05sPGpo4\nMAAfYiibcVmCwgtlIXlz9WxGpbVFbZ5n5XT1HQwI5U0vxybQ58yFlw==\n-----END RSA PRIVATE KEY-----',
                            ],
                        ],
                    ])
                ),
                responses: [new Model\Response(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'id' => ['type' => 'string'],
                                    'roomId' => ['type' => 'string'],
                                    'timestamp' => ['type' => 'date'],
                                    'escName' => ['type' => 'string'],
                                    'userIdCmd' => ['type' => 'string'],
                                    'certificate' => ['type' => 'string'],
                                    'createdAt' => ['type' => 'date'],
                                    'updatedAt' => ['type' => 'date'],
                                ],
                            ],
                            'example' => [
                                'id' => '645660dc47b3c56c99031f84',
                                'roomId' => 'bebd850304a643dc8c8d4c981f7b0295',
                                'timestamp' => '2023-05-06T14:34:28.378+02:00',
                                'escName' => 'EscDgtt',
                                'userIdCmd' => '82c76c4af7e14f41989d0102dd755918',
                                'certificate' => '-----BEGIN CERTIFICATE-----\nMIIDNTCCAh0CFF+e8YMbGATfj3Cc5Tf6C4uMi0J/MA0GCSqGSIb3DQEBCwUAMFcx\nCzAJBgNVBAYTAkZSMRYwFAYDVQQIDA1JbGUtZGUtRnJhbmNlMQ4wDAYDVQQHDAVQ\nYXJpczESMBAGA1UECgwJRE9DQVBPU1RFMQwwCgYDVQQLDANFU0MwHhcNMjMwNDI5\nMTUyMjU1WhcNMzMwNDI2MTUyMjU1WjBXMQswCQYDVQQGEwJGUjEWMBQGA1UECAwN\nSWxlLWRlLUZyYW5jZTEOMAwGA1UEBwwFUGFyaXMxEjAQBgNVBAoMCURPQ0FQT1NU\nRTEMMAoGA1UECwwDRVNDMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA\n0OOVSNFNVOOHIef0ObyV3xRpIb8u4gS7VjAxr4gzo0Qmv8ZRrUzpsC/yu72TZ72X\n6TkzMLHo/yHPFXdrBPdFmXxN/zepD1olTo8Nz3GSCYErMg1s3WNNeTdqY2/4+buc\nWlfeeAXc3N9+RQbQt+g+8CLV1WGqoJngMiHMFD+Q4XogNG+o8Zqdkup5Oi54KJ4n\nLgsLF4ttEvEuqjnxp3qqs56dl6DLnHeyWjw7nxm5EgeFD5BXEG31xPK8NGljG65C\n6CQOBQSKpJn5KIpxUmDGFqQwMlUst4RQRXTkSBXCihRz+T9GDkRM+m/IA4mxm18S\n0+znQuXGAnAX+a9FA3nDRwIDAQABMA0GCSqGSIb3DQEBCwUAA4IBAQA+gdwu3hvB\ntc33LlBUD659x8sXAPPISMe8As3WfoGFLuqSB4herqGj9bUu0jPPZLv1JVkURMG5\nF4ReSAba4FNn6ihP+3Dq1Upb3Ne+8ALQO2ID3HmcJ0B2vp/deLmVJm8XMnFEQuSu\n9voDPjkmtxHxw4zNRymtZ/uy2Dy6SIapGT1mNaxN2YKflZ9Ncc0teuxRrKtSDUv7\ntzh69KeoxbL2O9qk8sV8C5QoavbffrejFEmnYaN2oZGTR3Tl2bR0wh8LnIEfrJdi\nTBD+ftlNbYHot8pum2819aR7iC/1CX64ZPH0pyageAe/fmPjmNLDvOYWSC+1vHIJ\naaut66U+Dlnq\n-----END CERTIFICATE-----\n-----BEGIN RSA PRIVATE KEY-----\nMIIEpAIBAAKCAQEA0OOVSNFNVOOHIef0ObyV3xRpIb8u4gS7VjAxr4gzo0Qmv8ZR\nrUzpsC/yu72TZ72X6TkzMLHo/yHPFXdrBPdFmXxN/zepD1olTo8Nz3GSCYErMg1s\n3WNNeTdqY2/4+bucWlfeeAXc3N9+RQbQt+g+8CLV1WGqoJngMiHMFD+Q4XogNG+o\n8Zqdkup5Oi54KJ4nLgsLF4ttEvEuqjnxp3qqs56dl6DLnHeyWjw7nxm5EgeFD5BX\nEG31xPK8NGljG65C6CQOBQSKpJn5KIpxUmDGFqQwMlUst4RQRXTkSBXCihRz+T9G\nDkRM+m/IA4mxm18S0+znQuXGAnAX+a9FA3nDRwIDAQABAoIBAEX8CbcE0LjhJhQZ\nB30Ujs3Xo8V3rThyR2V/7hWWN1665ZSY2CKMwzqRRezafflizvOHg3wMDzjzVdMm\nWEeopLvgpq0cM9r948PnL8RRk5D1osg5MUEXFQzIoS9Bg5P/5ElsaQ+jPzUM99we\nhW0oNXX05dmAyE7YXwHMyvf5uAuiaQC/j4OxlF3FQL1TXBpUz9wjUH15233vs3MJ\ni1YI/nuGyUCF798V0kN+PdMX/5BKNXzQAM5wgeRHrmZjF5zGkEVAnxLe9zGEAusM\nyARpoM3ovgeYd6SJM/+uiDEQFIAh0Aq1LmR4Ckd9h5vciJkCNmsM1V8aB2d4O2pk\nUJG70skCgYEA7uTamTNxtdke4YyNilzufX+G71qDmwHI+p0eYR0q31hjI5TOikQZ\nnghWt3Ds1micwmMGdSul1fsSL/OkLONam+D6tLhofpGc9ONoWkhe690z37abxqZc\ndbDB3J4tYdDFoCnxzGZEzXfqeVWa3gtgK+4zMN2pOh4iiER6uAbbhXsCgYEA39i1\n7PLtkjCtKvYot9E/o7N+EsBrV+2bJXr11MxAg8xPsjdX9TLx+L4Ke9oReo3JmTyy\nw4oswB22H7qUVi3mxGBPBAe3FYA1ehXG9haLgm+0qTqUKBZApYh2c1E/lOteYPAP\n7+JV3ECRL9Yez2TS9xy3/mE7laIPVno5ipKhwaUCgYEAp6RFmpi1RS12NEVT1jLc\n0Yi6giD8lbws82Fqvi+ftFfsSj3UqsabS4ECiXxfVx7WUrr2/yaAG6cy3h+VqEJr\nhVPd2xBFk/xPLrKeuixrIA18p5MXwdFgtN4zTMRbiHbfGEQXAJ8LzTxHeJoQMZ2+\nAVYI6lPCu5wGE6YqkBWP2x0CgYEAvHbvJSKbVzxYcBV0zt8EX8Wd3hfXyN4qg/H0\nzEd0AgOhDgF+stlD1RPcK868bJy4IaZzNCM/7jiVwD0M60oWQzx7Cn+cbCPX4oQ3\nUbZFm3qFdv31A5NItpYgdJLxn2ZTvfYg1O2RMLHEC6yRoDfZT2kEmM4wppkdpMef\nbsZqHmkCgYBgKArnn761BDekXXWZDBc5aAJJZKvA/lQu71gvUreNuBiiQrrC/Lec\nPesv5uYAB+HgZpXR29e1/WywSD6zNXuPAzcECShRBJqqjohq2zyUfXOE05sPGpo4\nMAAfYiibcVmCwgtlIXlz9WxGpbVFbZ5n5XT1HQwI5U0vxybQ58yFlw==\n-----END RSA PRIVATE KEY-----',
                                'createdAt' => '2023-05-06T14:16:07.130Z',
                                'updatedAt' => '2023-05-06T14:16:07.130Z',
                            ],
                        ],
                    ])
                )]
            )
        ),
        new Get(),
        new Delete(),
        new Patch(),
        new Put(),
    ]
)]
#[ODM\UniqueIndex(
    keys: [
        'roomId' => 'asc',
        'escName' => 'asc',
    ],
)]
#[ApiFilter(SearchFilter::class)]
class Drawer
{
    use TimestampableDocument;

    #[ApiProperty(identifier: true)]
    #[Groups(['drawers:read'])]
    #[ODM\Id]
    private string $id;

    #[ODM\Field(type: Type::STRING)]
    #[Groups(['drawers:read', 'drawers:write'])]
    private string $roomId;

    #[ODM\Field(type: 'date')]
    #[Groups(['drawers:read', 'drawers:write'])]
    private \DateTime $timestamp;

    #[ODM\Field(type: 'string')]
    #[Groups(['drawers:read', 'drawers:write'])]
    private string $escName;

    #[ODM\Field(type: 'string')]
    #[Groups(['drawers:read', 'drawers:write'])]
    private string $userIdCmd;

    #[ODM\Field(type: 'string')]
    #[Groups(['drawers:read', 'drawers:write'])]
    private string $certificate;

    #[ODM\Field(type: 'date')]
    #[Groups(['drawers:read'])]
    #[Gedmo\Timestampable(on: 'create')]
    protected $createdAt;

    #[ODM\Field(type: 'date')]
    #[Groups(['drawers:read'])]
    #[Gedmo\Timestampable(on: 'update', field: 'certificate')]
    protected $updatedAt;

    public function getId(): string
    {
        return $this->id;
    }

    public function getRoomId(): string
    {
        return $this->roomId;
    }

    /**
     * @return $this
     */
    public function setRoomId(string $roomId): self
    {
        $this->roomId = $roomId;

        return $this;
    }

    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    /**
     * @return $this
     */
    public function setTimestamp(\DateTime $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getEscName(): string
    {
        return $this->escName;
    }

    /**
     * @return $this
     */
    public function setEscName(string $escName): self
    {
        $this->escName = $escName;

        return $this;
    }

    public function getUserIdCmd(): string
    {
        return $this->userIdCmd;
    }

    /**
     * @return $this
     */
    public function setUserIdCmd(string $userIdCmd): self
    {
        $this->userIdCmd = $userIdCmd;

        return $this;
    }

    public function getCertificate(): string
    {
        return $this->certificate;
    }

    /**
     * @return $this
     */
    public function setCertificate(string $certificate): self
    {
        $this->certificate = $certificate;

        return $this;
    }
}
