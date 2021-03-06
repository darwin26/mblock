<?php
/**
 * Author: Joachim Doerr
 * Date: 31.07.16
 * Time: 08:48
 */

class MBlockFormItemDecorator
{
    /**
     * @param MBlockItem $item
     * @return String
     * @author Joachim Doerr
     */
    static public function decorateFormItem(MBlockItem $item)
    {
        // set phpquery document
        $document = phpQuery::newDocumentHTML($item->getForm());

        // find inputs
        if ($matches = $document->find('input')) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                // label for and id change
                self::replaceForId($document, $match, $item);
                // replace attribute id
                self::replaceName($match, $item);
                // change checked or value by type
                switch ($match->getAttribute('type')) {
                    case 'checkbox':
                    case 'radio':
                        // replace checked
                        self::replaceChecked($match, $item);
                        break;
                    default:
                        // replace value by json key
                        self::replaceValue($match, $item);
                }
            }
        }

        // find textareas
        if ($matches = $document->find('textarea')) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                // label for and id change
                self::replaceForId($document, $match, $item);
                // replace attribute id
                self::replaceName($match, $item);
                // replace value by json key
                self::replaceValue($match, $item);
            }
        }

        // find selects
        if ($matches = $document->find('select')) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                // continue by media elements
                if (strpos($match->getAttribute('id'), 'REX_MEDIA') !== false
                    or strpos($match->getAttribute('id'), 'REX_LINK') !== false) {
                    continue;
                }
                // label for and id change
                self::replaceForId($document, $match, $item);
                // replace attribute id
                self::replaceName($match, $item);
                // replace value by json key
                if ($match->hasChildNodes()) {
                    /** @var DOMElement $child */
                    foreach ($match->childNodes as $child) {
                        switch ($child->nodeName) {
                            case 'optgroup':
                                foreach ($child->childNodes as $nodeChild)
                                    self::replaceOptionSelect($match, $nodeChild, $item);
                                break;
                            default:
                                self::replaceOptionSelect($match, $child, $item);
                                break;
                        }
                    }
                }
            }
        }

        // return the manipulated html output
        return $document->htmlOuter();
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function replaceName(DOMElement $dom, MBlockItem $item)
    {
        // replace attribute id
        preg_match('/\]\[\d+\]\[/', $dom->getAttribute('name'), $matches);
        if ($matches) $dom->setAttribute('name', str_replace($matches[0], '][' . $item->getId() . '][', $dom->getAttribute('name')));
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function replaceValue(DOMElement $dom, MBlockItem $item)
    {
        // get value key by name
        $matches = self::getName($dom);

        // found
        if ($matches) {
            // node name switch
            switch ($dom->nodeName) {
                default:
                case 'input':
                    if ($matches && array_key_exists($matches[1], $item->getResult())) $dom->setAttribute('value', $item->getResult()[$matches[1]]);
                    break;
                case 'textarea':
                    if ($matches && array_key_exists($matches[1], $item->getResult())) $dom->nodeValue = $item->getResult()[$matches[1]];
                    break;
            }
        }
    }

    /**
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function replaceChecked(DOMElement $dom, MBlockItem $item)
    {
        // get value key by name
        $matches = self::getName($dom);

        // found
        if ($matches) {
            // unset select
            if ($dom->getAttribute('checked')) {
                $dom->removeAttribute('checked');
            }
            // set select by value = result
            if ($matches && array_key_exists($matches[1], $item->getResult()) && $item->getResult()[$matches[1]] == $dom->getAttribute('value')) {
                $dom->setAttribute('checked', 'checked');
            }
        }
    }

    /**
     * @param DOMElement $select
     * @param DOMElement $option
     * @param MBlockItem $item
     * @author Joachim Doerr
     */
    protected static function replaceOptionSelect(DOMElement $select, DOMElement $option, MBlockItem $item)
    {
        // get value key by name
        $matches = self::getName($select);

        if ($matches) {
            // unset select
            if ($option->hasAttribute('selected')) {
                $option->removeAttribute('selected');
            }
            // set select by value = result
            if ($matches && array_key_exists($matches[1], $item->getResult()) && $item->getResult()[$matches[1]] == $option->getAttribute('value')) {
                $option->setAttribute('selected', 'selected');
            }
        }
    }

    /**
     * @param phpQueryObject $document
     * @param DOMElement $dom
     * @param MBlockItem $item
     * @return bool
     * @author Joachim Doerr
     */
    protected static function replaceForId(phpQueryObject $document, DOMElement $dom, MBlockItem $item)
    {
        // get input id
        $domId = $dom->getAttribute('id');

        if (strpos($domId, 'REX_MEDIA') !== false
            or strpos($dom->getAttribute('class'), 'redactorEditor') !== false
            or strpos($domId, 'REX_LINK') !== false) {
            return false;
        }

        $id = preg_replace('/(_\d+){2}/i', '_' . $item->getId(), $domId);
        $dom->setAttribute('id', $id);
        // find label with for
        $matches = $document->find('label');

        if ($matches) {
            /** @var DOMElement $match */
            foreach ($matches as $match) {
                $for = $match->getAttribute('for');
                if ($for == $domId) {
                    $match->setAttribute('for', $id);
                }
            }
        }
        return true;
    }

    /**
     * @param DOMElement $dom
     * @return mixed
     * @author Joachim Doerr
     */
    public static function getName(DOMElement $dom)
    {
        preg_match('/^.*?\[(\w+)\]$/i', $dom->getAttribute('name'), $matches);
        return $matches;
    }

//    static protected function get_html_from_node($node){
//        $html = '';
//        $children = $node->childNodes;
//
//        foreach ($children as $child) {
//            $tmp_doc = new DOMDocument();
//            $tmp_doc->appendChild($tmp_doc->importNode($child,true));
//            $html .= $tmp_doc->saveHTML();
//        }
//        return $html;
//    }

}