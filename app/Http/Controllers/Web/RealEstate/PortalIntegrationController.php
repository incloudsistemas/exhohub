<?php

namespace App\Http\Controllers\Web\RealEstate;

class PortalIntegrationController extends PropertyController
{
    public function publishOnCanalPro()
    {
        set_time_limit(0);
        ini_set('output_buffering', 'off');
        ini_set('zlib.output_compression', false);

        $pageSize = 1000;
        $page = 0;

        $totalProperties = min(1000, $this->property->getWeb(statuses: $this->getPostStatusByUser())
            ->whereJsonContains('publish_on->canal_pro', true)
            ->count());

        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="grupozap.xml"');

        $xml = new \XMLWriter();
        $xml->openURI('php://output');
        $xml->startDocument('1.0', 'UTF-8');
        $xml->setIndent(true);
        $xml->startElement('ListingDataFeed');
        $xml->writeAttribute('xmlns', 'http://www.vivareal.com/schemas/1.0/VRSync');
        $xml->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $xml->writeAttribute('xsi:schemaLocation', 'http://www.vivareal.com/schemas/1.0/VRSync http://xml.vivareal.com/vrsync.xsd');

        $headerXml = view('web.portal-integration.publish-on.canal-pro.header')
            ->render();

        $xml->writeRaw($headerXml);

        while ($page * $pageSize < $totalProperties) {
            $properties = $this->property->getWeb(statuses: $this->getPostStatusByUser())
                ->whereJsonContains('publish_on->canal_pro', true)
                ->skip($page * $pageSize)
                ->take($pageSize)
                ->get();

            $listingsXml = view('web.portal-integration.publish-on.canal-pro.listings', compact('properties'))
                ->render();

            $xml->writeRaw($listingsXml);
            $page++;
        }

        $xml->endElement();
        $xml->endDocument();
        $xml->flush();
    }
}
