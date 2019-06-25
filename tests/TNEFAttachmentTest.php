<?php


use PHPUnit\Framework\TestCase;
use TNEFDecoder\TNEFAttachment;


class TNEFAttachmentTest extends TestCase {

    public function testDecode() {
        $buffer = base64_decode(file_get_contents(dirname(__FILE__) . "/testfiles/base64_attachment.txt"));
        file_put_contents(dirname(__FILE__) . "/testfiles/winmail.dat", $buffer);

        $attachment = new TNEFAttachment($buffer);
        $attachment->decodeTnef($buffer);
        $files = $attachment->getFilesNested();

        $this->assertEquals(4, count($files));
        $this->assertEquals("image001.jpg", $files[0]->getName());
        $this->assertEquals("image003.jpg", $files[1]->getName());
        $this->assertEquals("image004.jpg", $files[2]->getName());
        $this->assertEquals("image005.jpg", $files[3]->getName());

    }
}
