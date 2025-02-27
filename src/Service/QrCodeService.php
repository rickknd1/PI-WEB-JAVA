<?php

namespace App\Service;

use App\Entity\Community;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\SvgWriter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QrCodeService
{
    /**
     * @var BuilderInterface
     */
    protected $builder;

    public function __construct(BuilderInterface $builder)
    {
        $this->builder = $builder;
    }

    public function generateQrCode(Community $community)
    {
        $url = 'http://127.0.0.1:8000/community/';

        $encoding = new Encoding('UTF-8');

        $result = $this->builder
            ->build(
                data: $url . $community->getId(),
                size: 200,
                margin: 10,
                encoding: $encoding,
                labelText: $community->getNom(),
                writer : new SvgWriter()
            );

        return $result->getString();
    }
    public function downloadQrCode(Community $community): Response
    {
        $svgContent = $this->generateQrCode($community);

        $response = new StreamedResponse(function () use ($svgContent) {
            echo $svgContent;
        });

        $response->headers->set('Content-Type', 'image/svg+xml');
        $response->headers->set('Content-Disposition', 'attachment; filename="qrcode_' . $community->getId() . '.svg"');

        return $response;
    }
}

