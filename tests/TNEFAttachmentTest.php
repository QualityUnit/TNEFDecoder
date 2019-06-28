<?php

use PHPUnit\Framework\TestCase;
use TNEFDecoder\TNEFAttachment;


class TNEFAttachmentTest extends TestCase {

    /**
     * Test decoding winmail.dat from email attachment encoded in base64
     */
    public function testDecode() {
        $buffer = base64_decode(file_get_contents(dirname(__FILE__) . "/testfiles/base64_attachment.txt"));
        file_put_contents(dirname(__FILE__) . "/testfiles/winmail.dat", $buffer);

        $attachment = new TNEFAttachment();
        $attachment->decodeTnef($buffer);
        $files = $attachment->getFiles();

        $this->assertEquals(4, count($files));
        $this->assertEquals("image001.jpg", $files[0]->getName());
        $this->assertEquals("image003.jpg", $files[1]->getName());
        $this->assertEquals("image004.jpg", $files[2]->getName());
        $this->assertEquals("image005.jpg", $files[3]->getName());

        foreach ($files as $file) {
            $this->assertEquals("image/jpeg", $file->getName());
        }

    }

    /**
     * Test decoding winmail.dat file from filesystem
     */
    public function testDecode2() {
        $buffer = file_get_contents(dirname(__FILE__) . "/testfiles/winmail2.dat");

        $attachment = new TNEFAttachment($buffer);
        $attachment->decodeTnef($buffer);
        $files = $attachment->getFiles();

        $this->assertEquals(3, count($files));
        $this->assertEquals("EmbeddedRTF.rtf", $files[0]->getName());
        $this->assertEquals("zappa_av1.jpg", $files[1]->getName());
        $this->assertEquals("bookmark.htm", $files[2]->getName());
    }
}
