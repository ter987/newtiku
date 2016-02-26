<?php
/**
 * This file is part of PHPWord - A pure PHP library for reading and writing
 * word processing documents.
 *
 * PHPWord is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPWord/contributors.
 *
 * @link        https://github.com/PHPOffice/PHPWord
 * @copyright   2010-2014 PHPWord contributors
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\PhpWord\Writer\Word2007\Part;

use PhpOffice\PhpWord\Writer\Word2007\Element\Container;

/**
 * Word2007 footer part writer: word/footerx.xml
 */
class Footer extends AbstractPart
{
    /**
     * Root element name
     *
     * @var string
     */
    protected $rootElement = 'w:ftr';

    /**
     * Footer/header element to be written
     *
     * @var \PhpOffice\PhpWord\Element\Footer
     */
    protected $element;

    /**
     * Write part
     *
     * @return string
     */
    public function write()
    {
        $xmlWriter = $this->getXmlWriter();
        $drawingSchema = 'http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing';

        $xmlWriter->startDocument('1.0', 'UTF-8', 'yes');
        $xmlWriter->startElement($this->rootElement);
        $xmlWriter->writeAttribute('xmlns:ve', 'http://schemas.openxmlformats.org/markup-compatibility/2006');
        $xmlWriter->writeAttribute('xmlns:o', 'urn:schemas-microsoft-com:office:office');
        $xmlWriter->writeAttribute('xmlns:r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $xmlWriter->writeAttribute('xmlns:m', 'http://schemas.openxmlformats.org/officeDocument/2006/math');
        $xmlWriter->writeAttribute('xmlns:v', 'urn:schemas-microsoft-com:vml');
        $xmlWriter->writeAttribute('xmlns:wp', $drawingSchema);
        $xmlWriter->writeAttribute('xmlns:w10', 'urn:schemas-microsoft-com:office:word');
        $xmlWriter->writeAttribute('xmlns:w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $xmlWriter->writeAttribute('xmlns:wne', 'http://schemas.microsoft.com/office/word/2006/wordml');

        $containerWriter = new Container($xmlWriter, $this->element);
        $containerWriter->write();

        $xmlWriter->endElement(); // $this->rootElement
//echo $xmlWriter->getData();exit;
		$data = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:ftr xmlns:ve="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml"><w:p><w:pPr/><w:r><w:pict><v:shape type="#_x0000_t0202" style="width:160.71428571429px; height:617.85714285714px; margin-left:546.42857142857px; margin-top:710.71428571429px; position:absolute; mso-position-horizontal:left; mso-position-vertical:top; mso-position-horizontal-relative:page; mso-position-vertical-relative:page;"><w10:wrap type="inline" anchorx="page" anchory="page"/><v:stroke/><v:textbox><w:txbxContent><w:p><w:pPr/><w:textFlow w:val="tbRlV" /><w:r><w:rPr><w:color w:val="AACC00"/><w:b/></w:rPr><w:t xml:space="preserve">I am bold</w:t></w:r></w:p></w:txbxContent></v:textbox></v:shape></w:pict></w:r></w:p></w:ftr>';
        return $xmlWriter->getData();
    }

    /**
     * Set element
     *
     * @param \PhpOffice\PhpWord\Element\Footer|\PhpOffice\PhpWord\Element\Header $element
     * @return self
     */
    public function setElement($element)
    {
        $this->element = $element;

        return $this;
    }
}
