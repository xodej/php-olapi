<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class Util.
 */
class Util
{
    /**
     * Converts array to csv.
     *
     * @param mixed[]     $data       array of data
     * @param null|string $delimiter  delimiter character (default=',')
     * @param null|string $enclosure  enclosure character (default='"')
     * @param null|string $escapeChar escape character (default='"')
     *
     * @return string
     */
    public static function strputcsv(
        array $data,
        ?string $delimiter = null,
        ?string $enclosure = null,
        ?string $escapeChar = null
    ): string {
        if (false === ($file_handle = \fopen('php://temp', 'wb+'))) {
            return '';
        }
        if (false === \fputcsv($file_handle, $data, $delimiter ?? ',', $enclosure ?? '"', $escapeChar ?? '"')) {
            \fclose($file_handle);

            return '';
        }
        \rewind($file_handle);
        if (false === ($return_csv = \stream_get_contents($file_handle))) {
            \fclose($file_handle);

            return '';
        }
        \fclose($file_handle);

        return \rtrim($return_csv, "\n");
    }

    /**
     * Appending two simpleXML elements.
     *
     * @param \SimpleXMLElement $receiver simpleXML element
     * @param \SimpleXMLElement $sender   simpleXML element
     */
    public static function simpleXmlAppend(\SimpleXMLElement $receiver, \SimpleXMLElement $sender): void
    {
        $to_dom = \dom_import_simplexml($receiver);
        $from_dom = \dom_import_simplexml($sender);

        if (false !== $to_dom && false !== $from_dom) {
            $to_dom->appendChild($to_dom->ownerDocument->importNode($from_dom, true));
        }
    }

    /**
     * Beautifies simpleXML element with indentation.
     *
     * @param \SimpleXMLElement $sxe simpleXML element
     *
     * @return string
     */
    public static function simpleXmlBeautify(\SimpleXMLElement $sxe): string
    {
        $document = new \DOMDocument('1.0', 'utf-8');
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;

        $dom_elem = \dom_import_simplexml($sxe);

        if (false !== $dom_elem) {
            $document->appendChild($document->importNode($dom_elem, true));
        }

        return $document->saveXML();
    }
}
