<?php
class XML
{
    public readonly string $file;

    function __construct(string $file)
    {
        $this->file = $file;
    }

    private function createAttributes(DOMElement $element, array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $element->setAttribute($key, $value);
        }
    }

    private function autoCreateTag(DOMElement $parent, array $children, DOMDocument $xml): void
    {
        foreach ($children as $tag => $content) {
            if (is_array($content)) {
                if ($tag === '@attributes') {
                    $this->createAttributes($parent, $content);
                } else {
                    // Check if the tag is meant to be repeated
                    if (array_keys($content) === range(0, count($content) - 1)) {
                        // Handle repeated tags
                        foreach ($content as $childContent) {
                            $childElement = $xml->createElement($tag);
                            $this->autoCreateTag($childElement, $childContent, $xml);
                            $parent->appendChild($childElement);
                        }
                    } else {
                        // Handle non-repeated tags
                        $childElement = $xml->createElement($tag);
                        $this->autoCreateTag($childElement, $content, $xml);
                        $parent->appendChild($childElement);
                    }
                }
            } else {
                $childElement = $xml->createElement($tag, $content);
                $parent->appendChild($childElement);
            }
        }
    }

    public function appendXML(string $rootTag, array $content, array $unique = null): void
    {
        $xml = new DOMDocument();
        $xml->formatOutput = true;
        $xml->preserveWhiteSpace = false;

        if (file_exists($this->file) && $xml->load($this->file)) {
            $root = $xml->getElementsByTagName($rootTag)->item(0);
        } else {
            $xml->encoding = 'UTF-8';
            $xml->xmlVersion = '1.0';
            $root = $xml->createElement($rootTag);
            $xml->appendChild($root);
        }

        if ($unique !== null) {
            $this->removeUniqueNodes($xml, $root, $unique);
        }

        $this->autoCreateTag($root, $content, $xml);

        if (!$xml->save($this->file)) {
            die("Failed to save XML file.");
        }
    }

    private function removeUniqueNodes(DOMDocument $xml, DOMElement $root, array $unique): void
    {
        $searchNodes = $xml->getElementsByTagName($unique['tag']);
        foreach ($searchNodes as $node) {
            $match = true;
            foreach ($unique['attr'] as $attr => $value) {
                if ($node->getAttribute($attr) !== $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                $root->removeChild($node);
            }
        }
    }
}
