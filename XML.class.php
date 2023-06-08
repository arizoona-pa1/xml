<?php
class XML
{
    private $path = "XML/";
    public readonly string $file;
    function __construct(string $file)
    {
        $this->file = $this->path . $file;
    }
    function AutoCreateTag($parents, $append, $xml)
    {
        foreach ($append as $parent => $value) {
            if (gettype($value) == 'array') {
                if (str_replace('@', '', $parent) == 'attributes') {
                    foreach ($value as $key => $key_value) {
                        $attr_user_id[] = new DOMAttr($key, $key_value);
                    }
                } else {
                    $parent = $xml->createElement($parent);
                    $this->AutoCreateTag($parent, $value, $xml);
                }
            } else {
                $parent = $xml->createElement($parent, $value);
            }
            if (gettype($parent) == "string") {
                if (str_replace('@', '', $parent) == 'attributes') {
                    foreach ($attr_user_id as $attr) {
                        $parents->setAttributeNode($attr);
                    }
                } else {
                    die("failed");
                }
            } else {
                $parents->appendChild($parent);
            }
        }
    }
    function appendXML(string $root, $append, $unique = null)
    {
        $xml = new DOMDocument();
        $xml->formatOutput = true;
        if (file_exists($this->file)) {
            $xml->preserveWhiteSpace = false;
            $xml->load($this->file) or die('file has problem');
            $root = $xml->getElementsByTagName($root)->item(0);

            if (isset($unique['tag'])) {
                $searchNodes = $xml->getElementsByTagName($unique['tag']);

                foreach ($searchNodes as $searchNode) {
                    foreach ($unique['attr'] as $x => $x_value) {
                        $valueAttr = $searchNode->getAttribute($x);
                        if ($valueAttr == $x_value) {
                            $nodeToRemove = $searchNode;
                        } else {
                            $nodeToRemove = false;
                            break;
                        }
                    }
                    if ($nodeToRemove) {
                        $root->removeChild($nodeToRemove);
                    }
                }
            }
        } else {
            $xml->encoding = 'UTF-8';
            $xml->xmlVersion = '1.0';
            $root = $xml->createElement($root);
            $xml->appendChild($root);
        }

        $this->AutoCreateTag($root, $append, $xml);
        if ($xml->save($this->file)) {
            // echo "$this->file has been successfully Appended";
        } else {
            // echo "failed";
        }
    }
}
// $xmldata = simplexml_load_file("XML/employee.xml") or die("we cannot find this url");
//  get attribute 
// echo "<pre>";
// print_r( $xmldata->employee[0]["name"]);
// echo $xmldata->employee[0]["name"];
// echo "</pre>";

?>