<?php

namespace Kunstmaan\UtilitiesBundle\Tests\Helper\Cipher;

use Kunstmaan\UtilitiesBundle\Helper\Cipher\UrlSafeCipher;

/**
 * UrlSafeCipherTest
 */
class UrlSafeCipherTest extends \PHPUnit_Framework_TestCase
{

    const SECRET = 'def00000290a4b250a1b24c41f3076b5e3955e1a51d8535a5dbcf209d17f1eb8d772349cbd12af5dc8f4b05d43ca900489c0fb5aa5c4c5190ccffb5663ae4831e3022fc6';
    const CONTENT = 'This is a random sentence which will be encrypted and then decrypted!';

    /**
     * @var UrlSafeCipher
     */
    protected $cipher;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @covers Kunstmaan\UtilitiesBundle\Helper\Cipher\UrlSafeCipher::__construct
     */
    protected function setUp()
    {
        $this->cipher = new UrlSafeCipher(self::SECRET);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Kunstmaan\UtilitiesBundle\Helper\Cipher\UrlSafeCipher::encrypt
     * @covers Kunstmaan\UtilitiesBundle\Helper\Cipher\UrlSafeCipher::decrypt
     */
    public function testEncryptDecrypt()
    {
        $encryptedValue = $this->cipher->encrypt(self::CONTENT);
        $this->assertNotEquals(self::CONTENT, $encryptedValue);
        $decryptedValue = $this->cipher->decrypt($encryptedValue);
        $this->assertEquals($decryptedValue, self::CONTENT);
    }

    /**
     * @covers Kunstmaan\UtilitiesBundle\Helper\Cipher\UrlSafeCipher::hex2bin
     */
    public function testHex2bin()
    {
        $hexValue = bin2hex(self::CONTENT);
        $this->assertNotEquals(self::CONTENT, $hexValue);
        $binValue = $this->cipher->hex2bin($hexValue);
        $this->assertEquals($binValue, self::CONTENT);
    }

}
