<?php

namespace PHPUnuhi\Tests\Bundles\Translator;

use Exception;
use PHPUnit\Framework\TestCase;
use PHPUnuhi\Bundles\Translator\TranslatorFactory;
use PHPUnuhi\Exceptions\ConfigurationException;
use PHPUnuhi\Models\Command\CommandOption;
use PHPUnuhi\Tests\Utils\Fakes\FakeTranslator;

class TranslatorFactoryTest extends TestCase
{

    /**
     * @return void
     */
    protected function setUp(): void
    {
        TranslatorFactory::getInstance()->resetStorages();
    }

    /**
     * @throws ConfigurationException
     * @return void
     */
    public function testGetCustomTranslator(): void
    {
        $custom = new FakeTranslator();
        TranslatorFactory::getInstance()->registerTranslator($custom);

        $translator = TranslatorFactory::getInstance()->fromService('fake', []);

        $this->assertSame($custom, $translator);
    }

    /**
     * @throws ConfigurationException
     * @return void
     */
    public function testUnknownServiceThrowsException(): void
    {
        $this->expectException(Exception::class);

        TranslatorFactory::getInstance()->fromService('unknown', []);
    }

    /**
     * @throws ConfigurationException
     * @return void
     */
    public function testNoServiceNameLeadsToException(): void
    {
        $this->expectException(Exception::class);

        TranslatorFactory::getInstance()->fromService('', []);
    }

    /**
     * @throws ConfigurationException
     * @return void
     */
    public function testDoubleRegistrationThrowsException(): void
    {
        $this->expectException(Exception::class);

        $custom = new FakeTranslator();

        TranslatorFactory::getInstance()->registerTranslator($custom);
        TranslatorFactory::getInstance()->registerTranslator($custom);
    }

    /**
     * @return void
     */
    public function testAllOptions(): void
    {
        $options = TranslatorFactory::getInstance()->getAllOptions();

        $expected = [
            new CommandOption('google-key', true),
            new CommandOption('openai-key', true),
            new CommandOption('openai-model', true),
            new CommandOption('deepl-key', true),
            new CommandOption('deepl-formal', false),
        ];

        $this->assertEquals($expected, $options);
    }
}
