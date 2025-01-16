<?php

namespace Lib;

class DOMParser extends DOMDocument
{

    public function __construct($version, $encoding)
    {
        parent::__construct($version, $encoding);
        libxml_use_internal_errors(true);
    }

    /**
     * @param $node
     * @return DOMElement
     */
    public function querySelector($node)
    {
        $pattern = null;
        $xpath   = new DOMXPath($this->ownerDocument);

        if (strpos($node, '#') === 0) {
            $pattern = '//*[@id="' . substr($node, 0, 1) . '"]';
        }

        if (strpos($node, '.') === 0) {
            $pattern = '//*[@class="' . substr($node, 0 , 1) . '"]';
        }

        if (strpos($node, 'input' === 0)) {
            $pattern = '//input[@"' . substr($node, 0, 6);
        }

        $xpath->query($pattern);
    }

    /**
     * @param  DOMNode $parentNode
     * @param  DOMNode $node
     * @return DOMNode
     * @throws DOMException
     */
    public function find($parentNode, $node)
    {
        if (!$node instanceof DOMNode) {
            throw new DOMException('Node is not an instance of DOMNode');
        }

        for ($i = 0; $i < $parentNode->childNodes->length; $i++) {

            if ($parentNode->childNodes->item($i) === $node) {
                return $parentNode->childNodes->item($i);
            }

        }

        throw new DOMException('Requested node cannot be found');
    }

}