<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class Util.
 */
class Util
{
    /**
     * @param array       $data
     * @param null|string $delimiter
     * @param null|string $enclosure
     * @param null|string $escapeChar
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
     * @param \SimpleXMLElement $to
     * @param \SimpleXMLElement $from
     */
    public static function simplexml_append(\SimpleXMLElement $to, \SimpleXMLElement $from): void
    {
        $to_dom = \dom_import_simplexml($to);
        $from_dom = \dom_import_simplexml($from);

        if (false !== $to_dom && false !== $from_dom) {
            $to_dom->appendChild($to_dom->ownerDocument->importNode($from_dom, true));
        }
    }

    /**
     * @param \SimpleXMLElement $sxe
     *
     * @return string
     */
    public static function simplexml_beauty_xml(\SimpleXMLElement $sxe): string
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
