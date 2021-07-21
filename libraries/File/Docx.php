
<?php
if(!defined("ATU_File_TEMP")){
    define("ATU_File_TEMP",function_exists("get_tempdir")?get_tempdir("tmp"):sys_get_temp_dir());
}

class ATU_Docx
{
    const SEPARATOR_TAB = "\t";
    private $debug = false;
    private $file;
    private $rels_xml;
    private $doc_xml;
    private $doc_media = [];
    private $last = 'none';
//    private $encoding = 'ISO-8859-1';
    private $encoding = 'UTF-8';
    private $tmpDir = ATU_File_TEMP;
    /**
     * object zipArchive
     *
     * @var string
     * @access private
     */
    private $docx;

    /**
     * object domDocument from document.xml
     *
     * @var string
     * @access private
     */
    private $domDocument;

    /**
     * xml from document.xml
     *
     * @var string
     * @access private
     */
    private $_document;

    /**
     * xml from numbering.xml
     *
     * @var string
     * @access private
     */
    private $_numbering;

    /**
     *  xml from footnote
     *
     * @var string
     * @access private
     */
    private $_footnote;

    /**
     *  xml from endnote
     *
     * @var string
     * @access private
     */
    public $_endnote;

    /**
     * array of all the endnotes of the document
     *
     * @var string
     * @access private
     */
    public $endnotes;

    /**
     * array of all the footnotes of the document
     *
     * @var string
     * @access private
     */
    private $footnotes;

    /**
     * array of all the relations of the document
     *
     * @var string
     * @access private
     */
    private $relations;

    /**
     * array of characters to insert like a list
     *
     * @var string
     * @access private
     */
    private $numberingList;

    /**
     * the text content that will be exported
     *
     * @var string
     * @access private
     */
    private $textOuput;


    
    private $table2text;
    private $list2text;
    private $paragraph2text;
    private $footnote2text;
    private $endnote2text;
    private $chart2text;

    /**
     * Construct
     *
     * @param $boolTransforms array of boolean values of which elements should be transformed or not
     * @access public
     */

    public function __construct($boolTransforms = array())
    {
        //table,list, paragraph, footnote, endnote, chart
        $this->table2text = $this->issetArrValue($boolTransforms,'table',true);
        $this->list2text = $this->issetArrValue($boolTransforms,'list',true);
        $this->paragraph2text = $this->issetArrValue($boolTransforms,'paragraph',true);
        $this->footnote2text = $this->issetArrValue($boolTransforms,'footnote',true);
        $this->endnote2text = $this->issetArrValue($boolTransforms,'endnote',true);
        $this->chart2text = $this->issetArrValue($boolTransforms,'chart',true);

        $this->debug = $this->issetArrValue($boolTransforms,'debug',false);
        $this->encoding = $this->issetArrValue($boolTransforms,'encoding',false);

        $this->textOuput = '';
        $this->docx = null;
        $this->_numbering = '';
        $this->numberingList = array();
        $this->endnotes = array();
        $this->footnotes = array();
        $this->relations = array();

        //$this->tmpDir = dirname(__FILE__);
    }
    private function issetArrValue($arr,$k,$v){
        return isset($arr[$k])?$arr[$k]:$v;
        
    }
    /**
     *
     * Extract the content of a word document and create a text file if the name is given
     *
     * @access public
     * @param string $filename of the word document.
     *
     * @return string
     */

    public function extract($filename = '')
    {
        if (empty($this->_document)) {
            //xml content from document.xml is not got
            exit('There is no content');
        }

        $this->domDocument = new DomDocument();
        $this->domDocument->loadXML($this->_document);
        //get the body node to check the content from all his children
        $bodyNode = $this->domDocument->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'body');
        //We get the body node. it is known that there is only one body tag
        $bodyNode = $bodyNode->item(0);
        foreach ($bodyNode->childNodes as $child) {
            //the children can be a table, a paragraph or a section. We only implement the 2 first option said.
            if ($this->table2text && $child->tagName == 'w:tbl') {
                //this node is a table and  the content is split with tabs if the variable table2text from the class is true
                $this->textOuput .= $this->table($child) . $this->separator();
            } else {
                //this node is a paragraph
                $this->textOuput .= $this->printWP($child) . ($this->paragraph2text ? $this->separator() : '');
            }
        }
        if (!empty($filename)) {
            $this->writeFile($filename, $this->textOuput);
        } else {
            return $this->textOuput;
        }
    }
    public function load($filename){
        $this->setDocx($filename);
    }
    /**
     * Setter
     *
     * @access public
     * @param $filename
     */
    public function setDocx($filename)
    {
        $this->docx = new ZipArchive();
        $ret = $this->docx->open($filename);
        if ($ret === true) {
            $this->_document = $this->docx->getFromName('word/document.xml');
        } else {
            exit('failed');
        }
    }

    /**
     * extract the content to an array from endnote.xml
     *
     * @access private
     */
    public function loadEndNote()
    {
        if (empty($this->endnotes)) {
            if (empty($this->_endnote)) {
                $this->_endnote = $this->docx->getFromName('word/endnotes.xml');
            }
            if (!empty($this->_endnote)) {
                $domDocument = new DomDocument();
                $domDocument->loadXML($this->_endnote);
                $endnotes = $domDocument->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'endnote');
                foreach ($endnotes as $endnote) {
                    $xml = $endnote->ownerDocument->saveXML($endnote);
                    $this->endnotes[$endnote->getAttribute('w:id')] = trim(strip_tags($xml));
                }
            }
        }
    }

    /**
     * Extract the content to an array from footnote.xml
     *
     * @access private
     */
    private function loadFootNote()
    {
        if (empty($this->footnotes)) {
            if (empty($this->_footnote)) {
                $this->_footnote = $this->docx->getFromName('word/footnotes.xml');
            }
            if (!empty($this->_footnote)) {
                $domDocument = new DomDocument();
                $domDocument->loadXML($this->_footnote);
                $footnotes = $domDocument->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'footnote');
                foreach ($footnotes as $footnote) {
                    $xml = $footnote->ownerDocument->saveXML($footnote);
                    $this->footnotes[$footnote->getAttribute('w:id')] = trim(strip_tags($xml));
                }
            }
        }
    }

    /**
     * Extract the styles of the list to an array
     *
     * @access private
     */
    private function listNumbering()
    {
        $ids = array();
        $nums = array();
        //get the xml code from the zip archive
        $this->_numbering = $this->docx->getFromName('word/numbering.xml');
        if (!empty($this->_numbering)) {
            //we use the domdocument to iterate the children of the numbering tag
            $domDocument = new DomDocument();
            $domDocument->loadXML($this->_numbering);
            $numberings = $domDocument->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'numbering');
            //there is only one numbering tag in the numbering.xml
            $numberings = $numberings->item(0);
            foreach ($numberings->childNodes as $child) {
                $flag = true;//boolean variable to know if the node is the first style of the list
                foreach ($child->childNodes as $son) {
                    if ($child->tagName == 'w:abstractNum' && $son->tagName == 'w:lvl') {
                        foreach ($son->childNodes as $daughter) {
                            if ($daughter->tagName == 'w:numFmt' && $flag) {
                                $nums[$child->getAttribute('w:abstractNumId')] = $daughter->getAttribute('w:val');//set the key with internal index for the listand the value it is the type of bullet
                                $flag = false;
                            }
                        }
                    } elseif ($child->tagName == 'w:num' && $son->tagName == 'w:abstractNumId') {
                        $ids[$son->getAttribute('w:val')] = $child->getAttribute('w:numId');//$ids is the index of the list
                    }
                }
            }
            //once we know what kind of list there is in the documents, is prepared the bullet that the library will use
            foreach ($ids as $ind => $id) {
                if ($nums[$ind] == 'decimal') {
                    //if the type is decimal it means that the bullet will be numbers
                    $this->numberingList[$id][0] = range(1, 10);
                    $this->numberingList[$id][1] = range(1, 10);
                    $this->numberingList[$id][2] = range(1, 10);
                    $this->numberingList[$id][3] = range(1, 10);
                } else {
                    //otherwise is *, and other characters
                    $this->numberingList[$id][0] = array('*', '*', '*', '*', '*', '*', '*', '*', '*', '*', '*', '*', '*', '*', '*', '*', '*');
                    $this->numberingList[$id][1] = array(chr(175), chr(175), chr(175), chr(175), chr(175), chr(175), chr(175), chr(175), chr(175), chr(175), chr(175), chr(175));
                    $this->numberingList[$id][2] = array(chr(237), chr(237), chr(237), chr(237), chr(237), chr(237), chr(237), chr(237), chr(237), chr(237), chr(237), chr(237));
                    $this->numberingList[$id][3] = array(chr(248), chr(248), chr(248), chr(248), chr(248), chr(248), chr(248), chr(248), chr(248), chr(248), chr(248));
                }
            }
        }
    }

    /**
     * Extract the content of a w:p tag
     *
     * @access private
     * @param $node object
     * @return string
     */
    private function printWP($node)
    {
        $ilvl = $numId = -1;
        if ($this->list2text) {//transform the list in ooxml to formatted list with tabs and bullets
            if (empty($this->numberingList)) {//check if numbering.xml is extracted from the zip archive
                $this->listNumbering();
            }
            //use the xpath to get expecific children from a node
            $xpath = new DOMXPath($this->domDocument);
            $query = 'w:pPr/w:numPr';
            $xmlLists = $xpath->query($query, $node);
            $xmlLists = $xmlLists->item(0);

            //if ($xmlLists->tagName == 'w:numPr') {
            //    if ($xmlLists->hasChildNodes()) {
            //        foreach ($xmlLists->childNodes as $child) {
            //            if ($child->tagName == 'w:ilvl') {
            //                $ilvl = $child->getAttribute('w:val');
            //            }elseif ($child->tagName == 'w:numId') {
            //                $numId = $child->getAttribute('w:val');
            //            }
            //        }
            //    }
            //}
            //if (($ilvl != -1) && ($numId != -1)) {
            //    //if is founded the style index of the list in the document and the kind of list
            //    $ret = '';
            //    for($i=-1; $i < $ilvl; $i++) {
            //        if(self::DEBUG) {
            //            $ret .= self::SEPARATOR_TAB_DEBUG;
            //        }
            //        else {
            //            $ret .= self::SEPARATOR_TAB;
            //        }
            //    }
            //    $ret .= array_shift($this->numberingList[$numId][$ilvl]) . ' ' . $this->toText($node);  //print the bullet
            //} else {
            $ret = $this->toText($node);
        //}
        } else {
            //if dont want to formatted lists, we strip from html tags
            $ret = $this->toText($node);
        }


        //get the data from the charts
        if ($this->chart2text) {
            $query = 'w:r/w:drawing/wp:inline';
            $xmlChart = $xpath->query($query, $node);
            //get the relation id from the document, to get the name of the xml chart file from the relations to extract the xml code.
            foreach ($xmlChart as $chart) {
                foreach ($chart->childNodes as $child) {
                    foreach ($child->childNodes as $child2) {
                        foreach ($child2->childNodes as $child3) {
                            $rid = $child3->getAttribute('r:id');
                        }
                    }
                }
            }
            //if (!empty($rid)) {
            //    if (empty($this->relations)) {
            //        $this->loadRelations();
            //    }
            //    //get the name of the chart xml file from the relations docuemnt
            //    $dataChart = new getDataFromXmlChart($this->docx->getFromName('word/' . $this->relations[$rid]['file']));
            //    if (in_array($this->chart2text, array(2, 'table'))) {
            //        $ret .= $this->printChartDataTable($dataChart);//formatted print of the chart data
            //    } else {
            //        $ret .= $this->printChartDataArray($dataChart);//formatted print of the chart data
            //    }
            //}
        }
        //extract the expecific endnote to insert with the text content
        if ($this->endnote2text) {
            if (empty($this->endnotes)) {
                $this->loadEndNote();
            }
            $query = 'w:r/w:endnoteReference';
            $xmlEndNote = $xpath->query($query, $node);
            foreach ($xmlEndNote as $note) {
                $ret .= '[' . $this->endnotes[$note->getAttribute('w:id')] . '] ';
            }
        }
        //extract the expecific footnote to insert with the text content
        if ($this->footnote2text) {
            if (empty($this->footnotes)) {
                $this->loadFootNote();
            }
            $query = 'w:r/w:footnoteReference';
            $xmlFootNote = $xpath->query($query, $node);
            foreach ($xmlFootNote as $note) {
                $ret .= '[' . $this->footnotes[$note->getAttribute('w:id')] . '] ';
            }
        }
        if ((($ilvl != -1) && ($numId != -1)) || (1)) {
            $ret .= $this->separator();
        }

        return $ret;
    }

    /**
     * return a text end of line
     *
     * @access private
     */
    private function separator()
    {
        return "\r\n";
    }

    /**
     *
     * Extract the content of a table node from the document.xml and return a text content
     *
     * @access private
     * @param $node object
     *
     * @return string
     */
    private function table($node)
    {
        $output = '';
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                //start a new line of the table
                if ($child->tagName == 'w:tr') {
                    foreach ($child->childNodes as $cell) {
                        //start a new cell
                        if ($cell->tagName == 'w:tc') {
                            if ($cell->hasChildNodes()) {
                                //
                                foreach ($cell->childNodes as $p) {
                                    $output .= $this->printWP($p);
                                }
                                $output .= self::SEPARATOR_TAB;
                            }
                        }
                    }
                }
                $output .= $this->separator();
            }
        }
        return $output;
    }


    /**
     *
     * 转换成text
     *
     * @access private
     * @param $node object
     *
     * @return string
     */
    private function toText($node)
    {
        $xml = $node->ownerDocument->saveXML($node);
        return trim(strip_tags($xml));
    }
    private function toHtml($node)
    {
        $xml = $node->ownerDocument->saveXML($node);
        return trim(strip_tags($xml));
    }





    /**
     * Sets the tmp directory where images will be stored
     *
     * @param string $tmp The location
     * @return void
     */
    private function setTmpDir($tmp)
    {
        $this->tmpDir = $tmp;
    }

    /**
     * READS The Document and Relationships into separated XML files
     *
     * @param var $object The class variable to set as DOMDocument
     * @param var $xml The xml file
     * @param string $encoding The encoding to be used
     * @return void
     */
    private function setXmlParts(&$object, $xml, $encoding)
    {
        $object = new DOMDocument();
        $object->encoding = $encoding;
        $object->preserveWhiteSpace = false;
        $object->formatOutput = true;
        $object->loadXML($xml);
        $object->saveXML();
    }

    /**
     * READS The Document and Relationships into separated XML files
     *
     * @param String $filename The filename
     * @return void
     */
    private function readZipPart($filename)
    {
        $zip = new ZipArchive();
        $_xml = 'word/document.xml';
        $_xml_rels = 'word/_rels/document.xml.rels';

        if (true === $zip->open($filename)) {
            if (($index = $zip->locateName($_xml)) !== false) {
                $xml = $zip->getFromIndex($index);
            }
            //Get the relationships
            if (($index = $zip->locateName($_xml_rels)) !== false) {
                $xml_rels = $zip->getFromIndex($index);
            }
            // load all images if they exist
            for ($i=0; $i<$zip->numFiles;$i++) {
                $zip_element = $zip->statIndex($i);
                if (preg_match("([^\s]+(\.(?i)(jpg|jpeg|png|gif|bmp))$)", $zip_element['name'])) {
                    $this->doc_media[$zip_element['name']] = $zip_element['name'];
                }
            }
            $zip->close();
        } else {
            die('non zip file');
        }

        $enc = mb_detect_encoding($xml);
        $this->setXmlParts($this->doc_xml, $xml, $enc);
        $this->setXmlParts($this->rels_xml, $xml_rels, $enc);

        if ($this->debug) {
            echo "<textarea style='width:100%; height: 200px;'>";
            echo $this->doc_xml->saveXML();
            echo "</textarea>";
            echo "<textarea style='width:100%; height: 200px;'>";
            echo $this->rels_xml->saveXML();
            echo "</textarea>";
        }
    }

    /**
     * CHECKS THE FONT FORMATTING OF A GIVEN ELEMENT
     * Currently checks and formats: bold, italic, underline, background color and font family
     *
     * @param XML $xml The XML node
     * @return String HTML formatted code
     */
    private function checkFormating(&$xml)
    {
        $node = trim($xml->readOuterXML());
        $t = '';
        // add <br> tags
        if (strstr($node, '<w:br ')) {
            $t = '<br>';
        }
        // look for formatting tags
        $f = "<span style='";
        $reader = new XMLReader();
        $reader->XML($node);
        $img = null;

        while ($reader->read()) {
            if ($reader->name == "w:b") {
                $f .= "font-weight: bold,";
            }
            if ($reader->name == "w:i") {
                $f .= "text-decoration: underline,";
            }
            if ($reader->name == "w:color") {
                $f .="color: #".$reader->getAttribute("w:val").",";
            }
            if ($reader->name == "w:rFont") {
                $f .="font-family: #".$reader->getAttribute("w:ascii").",";
            }
            if ($reader->name == "w:shd" && $reader->getAttribute("w:val") != "clear" && $reader->getAttribute("w:fill") != "000000") {
                $f .="background-color: #".$reader->getAttribute("w:fill").",";
            }
            if ($reader->name == 'w:drawing' && !empty($reader->readInnerXml())) {
                $r = $this->checkImageFormating($reader);
                $img = $r !== null ? "<image src='".$r."' />" : null;
            }
        }

        $f = rtrim($f, ',');
        $f .= "'>";
        $t .= ($img !== null ? $img : htmlentities($xml->expand()->textContent));

        return $f.$t."</span>";
    }

    /**
     * CHECKS THE ELEMENT FOR UL ELEMENTS
     * Currently under development
     *
     * @param XML $xml The XML node
     * @return String HTML formatted code
     */
    private function getListFormating(&$xml)
    {
        $node = trim($xml->readOuterXML());

        $reader = new XMLReader();
        $reader->XML($node);
        $ret=[];
        $close = "";
        while ($reader->read()) {
            if ($reader->name == "w:numPr" && $reader->nodeType == XMLReader::ELEMENT) {
            }
            if ($reader->name == "w:numId" && $reader->hasAttributes) {
                switch ($reader->getAttribute("w:val")) {
                    case 1:
                        $ret['open'] = "<ol><li>";
                        $ret['close'] = "</li></ol>";
                        break;
                    case 2:
                        $ret['open'] = "<ul><li>";
                        $ret['close'] = "</li></ul>";
                        break;
                }
            }
        }
        return $ret;
    }

    /**
     * CHECKS IF THERE IS AN IMAGE PRESENT
     * Currently under development
     *
     * @param XML $xml The XML node
     * @return String The location of the image
     */
    private function checkImageFormating(&$xml)
    {
        $content = trim($xml->readInnerXml());

        if (!empty($content)) {
          
            $notfound = true;
            $reader = new XMLReader();
            $reader->XML($content);

            while ($reader->read() && $notfound) {
                if ($reader->name == "a:blip") {
                    $relId = $reader->getAttribute("r:embed");
                    $notfound = false;
                }
            }

            // image id found, get the image location
            if (!$notfound && $relId) {
                $reader = new XMLReader();
                $reader->XML($this->rels_xml->saveXML());

                while ($reader->read()) {
                    if ($reader->nodeType == XMLREADER::ELEMENT && $reader->name=='Relationship') {
                        if ($reader->getAttribute("Id") == $relId) {
                            $link = "word/".$reader->getAttribute('Target');
                            break;
                        }
                    }
                }

                $zip = new ZipArchive();
                $im = null;
                if (true === $zip->open($this->file)) {
                    $im = $this->createImage($zip->getFromName($link), $relId, $link);
                }
                $zip->close();
                return $im;
            }
        }

        return null;
    }

    /**
     * Creates an image in the filesystem
     *
     * @param objetc $image The image object
     * @param string $relId The image relationship Id
     * @param string $name The image name
     * @return Array With HTML open and closing tag definition
     */
    private function createImage($image, $relId, $name)
    {
        $arr = explode('.', $name);
        $l = count($arr);
        $ext = strtolower($arr[$l-1]);
        return 'data:image/bmp/jpg/png/gif;base64,' . base64_encode($image);
        $im = imagecreatefromstring($image);
        $fname = $this->tmpDir.$relId.'.'.$ext;
        //echo $fname;exit;
        switch ($ext) {
            case 'png':
                imagepng($im, $fname);
                break;
            case 'bmp':
                imagebmp($im, $fname);
                break;
            case 'gif':
                imagegif($im, $fname);
                break;
            case 'jpeg':
            case 'jpg':
                imagejpeg($im, $fname);
                break;
            default:
                return null;
        }
        
        return $fname
;
    }

    /**
     * CHECKS IF ELEMENT IS AN HYPERLINK
     *
     * @param XML $xml The XML node
     * @return Array With HTML open and closing tag definition
     */
    private function getHyperlink(&$xml)
    {
        $ret = array('open'=>'<ul>','close'=>'</ul>');
        $link ='';
        if ($xml->hasAttributes) {
            $attribute = "";
            while ($xml->moveToNextAttribute()) {
                if ($xml->name == "r:id") {
                    $attribute = $xml->value;
                }
            }

            if ($attribute != "") {
                $reader = new XMLReader();
                $reader->XML($this->rels_xml->saveXML());

                while ($reader->read()) {
                    if ($reader->nodeType == XMLREADER::ELEMENT && $reader->name=='Relationship') {
                        if ($reader->getAttribute("Id") == $attribute) {
                            $link = $reader->getAttribute('Target');
                            break;
                        }
                    }
                }
            }
        }

        if ($link != "") {
            $ret['open'] = "<a href='".$link."' target='_blank'>";
            $ret['close'] = "</a>";
        }

        return $ret;
    }
    /**
     * PROCESS TABLE CONTENT
     *
     * @param XML $xml The XML node
     * @return THe HTML code of the table
     */
    private function checkTableFormating(&$xml)
    {
        $table = "<table><tbody>";

        while ($xml->read()) {
            if ($xml->nodeType == XMLREADER::ELEMENT && $xml->name === 'w:tr') { //table row
                $tc = $ts = "";


                $tr = new XMLReader;
                $tr->xml(trim($xml->readOuterXML()));

                while ($tr->read()) {
                    if ($tr->nodeType == XMLREADER::ELEMENT && $tr->name === 'w:tcPr') { //table element properties
                        $ts = $this->processTableStyle(trim($tr->readOuterXML()));
                    }
                    if ($tr->nodeType == XMLREADER::ELEMENT && $tr->name === 'w:tc') { //table column
                        $tc .= $this->processTableRow(trim($tr->readOuterXML()));
                    }
                }
                $table .= '<tr style="'.$ts.'">'.$tc.'</tr>';
            }
        }

        $table .= "</tbody></table>";
        return $table;
    }

    /**
     * PROCESS THE TABLE ROW STYLE
     *
     * @param string $content The XML node content
     * @return THe HTML code of the table
     */
    private function processTableStyle($content)
    {
        /*border-collapse:collapse;
        border-bottom:4px dashed #0000FF;
        border-top:6px double #FF0000;
        border-left:5px solid #00FF00;
        border-right:5px solid #666666;*/

        $tc = new XMLReader;
        $tc->xml($content);
        $style = "border-collapse:collapse;";

        while ($tc->read()) {
            if ($tc->name === "w:tcBorders") {
                $tc2 = new SimpleXMLElement($tc->readOuterXML());

                foreach ($tc2->children('w', true) as $ch) {
                    if (in_array($ch->getName(), ['left','top','botom','right'])) {
                        $line = $this->convertLine($ch['val']);
                        $style .= " border-".$ch->getName().":".$ch['sz']."px $line #".$ch['color'].";";
                    }
                }

                $tc->next();
            }
        }
        return $style;
    }
    private function convertLine($in)
    {
        if (in_array($in, ['dotted'])) {
            return "dashed";
        }

        if (in_array($in, ['dotDash','dotdotDash','dotted','dashDotStroked','dashed','dashSmallGap'])) {
            return "dashed";
        }

        if (in_array($in, ['double','triple','threeDEmboss','threeDEngrave','thick'])) {
            return "double";
        }

        if (in_array($in, ['nil','none'])) {
            return "none";
        }

        return "solid";
    }

    /**
     * PROCESS THE TABLE ROW
     *
     * @param string $content The XML node content
     * @return THe HTML code of the table
     */
    private function processTableRow($content)
    {
        $tc = new XMLReader;
        $tc->xml($content);
        $ct = "";

        while ($tc->read()) {
            if ($tc->name === "w:r") {
                $ct .= "<td>".$this->checkFormating($tc)."</td>";
                $tc->next();
            }
        }
        return $ct;
    }
    /**
     * READS THE GIVEN DOCX FILE INTO HTML FORMAT
     *
     * @param String $filename The DOCX file name
     * @return String With HTML code
     */
    public function readDocument($filename)
    {
        $this->file = $filename;
        $this->readZipPart($filename);
        $reader = new XMLReader();
        $reader->XML($this->doc_xml->saveXML());

        $text = '';
        $list_format=[];

    
        // loop through docx xml dom
        while ($reader->read()) {
            // look for new paragraphs
            $paragraph = new XMLReader;
            $p = $reader->readOuterXML();
           
            if ($reader->nodeType == XMLREADER::ELEMENT && $reader->name === 'w:p') {
                // set up new instance of XMLReader for parsing paragraph independantly
                $paragraph->xml($p);
             
             
                $formatting['header'] = 0;
                preg_match('/<w:pStyle w:val=".*?([1-6])"/', $p, $matches);
           
                if (isset($matches[1])) {
                    switch ($matches[1]) {
                        case '1': $formatting['header'] = 1; break;
                        case '2': $formatting['header'] = 2; break;
                        case '3': $formatting['header'] = 3; break;
                        case '4': $formatting['header'] = 4; break;
                        case '5': $formatting['header'] = 5; break;
                        case '6': $formatting['header'] = 6; break;
                        default: $formatting['header'] = 0; break;
                    }
                }
                // open h-tag or paragraph
                $text .= ($formatting['header'] > 0) ? '<h'.$formatting['header'].'>' : '<p>';

                // loop through paragraph dom
                while ($paragraph->read()) {
                    // look for elements
                    if ($paragraph->nodeType == XMLREADER::ELEMENT && $paragraph->name === 'w:r') {
                        if ($list_format == "") {
                            $text .= $this->checkFormating($paragraph);
                        } else {
                            if (isset($list_format['open'])) {
                                $text .= $list_format['open'];
                            }
                            $text .= $this->checkFormating($paragraph);
                            if (isset($list_format['close'])) {
                                $text .= $list_format['close'];
                            }
                        }
                        $list_format ="";
                        $paragraph->next();
                    } elseif ($paragraph->nodeType == XMLREADER::ELEMENT && $paragraph->name === 'w:pPr') { //lists
                        $list_format = $this->getListFormating($paragraph);
                        $paragraph->next();
                    } elseif ($paragraph->nodeType == XMLREADER::ELEMENT && $paragraph->name === 'w:drawing') { //images
                        $text .= $this->checkImageFormating($paragraph);
                        $paragraph->next();
                    } elseif ($paragraph->nodeType == XMLREADER::ELEMENT && $paragraph->name === 'w:hyperlink') {
                        $hyperlink = $this->getHyperlink($paragraph);
                        $text .= $hyperlink['open'];
                        $text .= $this->checkFormating($paragraph);
                        $text .= $hyperlink['close'];
                        $paragraph->next();
                    }
                }
                $text .= ($formatting['header'] > 0) ? '</h'.$formatting['header'].'>' : '</p>';
            } elseif ($reader->nodeType == XMLREADER::ELEMENT && $reader->name === 'w:tbl') { //tables
                $paragraph->xml($p);
                $text .= $this->checkTableFormating($paragraph);
                $reader->next();
            }
        }
        $reader->close();
        if ($this->debug) {
            echo "<div style='width:100%; height: 200px;'>";
            echo mb_convert_encoding($text, $this->encoding);
            echo "</div>";
        }
        return mb_convert_encoding($text, $this->encoding);
    }
}