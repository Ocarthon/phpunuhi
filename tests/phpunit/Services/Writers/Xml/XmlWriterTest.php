<?php

namespace PHPUnuhi\Tests\Services\Writers\Xml;

use PHPUnit\Framework\TestCase;
use PHPUnuhi\Services\Writers\Xml\XmlWriter;
use PHPUnuhi\Tests\Utils\Traits\StringCleanerTrait;

class XmlWriterTest extends TestCase
{
    use StringCleanerTrait;


    /**
     * @var string
     */
    protected $testXmlFilePath;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->testXmlFilePath = __DIR__ . '/testfile.xml';
    }


    /**
     * @return void
     */
    protected function tearDown(): void
    {
        if (file_exists($this->testXmlFilePath)) {
            unlink($this->testXmlFilePath);
        }
    }

    /**
     * @return void
     */
    public function testSaveXml(): void
    {
        $xmlWriter = new XmlWriter();
        $xmlContent = '<?xml version="1.0" encoding="UTF-8"?><root><element>Test Content</element></root>';

        $xmlWriter->saveXml($this->testXmlFilePath, $xmlContent);

        $actual = (string)file_get_contents($this->testXmlFilePath);

        $expected = $this->buildComparableString($xmlContent);
        $actual = $this->buildComparableString($actual);

        $this->assertFileExists($this->testXmlFilePath);
        $this->assertEquals($expected, $actual);
    }
}
