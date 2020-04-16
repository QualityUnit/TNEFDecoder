<?php

use PHPUnit\Framework\TestCase;
use TNEFDecoder\TNEFAttachment;
use TNEFDecoder\TNEFFileBase;


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
            $this->assertEquals("image/jpeg", $file->getType());
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

   /**
    * Test decoding winmail.dat file from filesystem
    */
   public function testDecode3() {
      $buffer = file_get_contents(dirname(__FILE__) . "/testfiles/two-files.tnef");

      $attachment = new TNEFAttachment($buffer);
      $attachment->decodeTnef($buffer);
      $files = $attachment->getFiles();

      $this->assertEquals(2, count($files));
      $this->assertEquals("AUTHORS", $files[0]->getName());
      $this->assertEquals("README", $files[1]->getName());
   }

   public function testDecodeHtml() {
       $files =  file_get_contents(dirname(__FILE__) . "/testfiles/unicode-mapi-attr.tnef");
       $attachment = new TNEFAttachment();
       $attachment->decodeTnef($buffer);
       $html = $attachment->parseHtml();
       $this->assertRegExp('/html/', $html->getContent());
   }

   public function testDecodeAuto() {
      $files =  scandir ( dirname(__FILE__) . "/testfiles/");
      foreach ($files as $file) {
         $ext = explode('.', $file);
         if (count($ext) == 2 && $ext[1] == "tnef") {
            $buffer = file_get_contents(dirname(__FILE__) . "/testfiles/" . $file);
            $list = file(dirname(__FILE__) . "/testfiles/" . $ext[0] . '.list', FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES);
            $attachment = new TNEFAttachment();
            $attachment->decodeTnef($buffer);
            $this->assertEquals(count($list), count($attachment->files));
            $decodedFiles = array_map(function(TNEFFileBase$file) {return $file->getName();}, $attachment->files);

            $withoutRtf = array_filter($list, array($this, "endsWithRtf"));
            $withoutRtfdecoded = array_filter($decodedFiles, array($this, "endsWithRtf"));

            $rtfs = array_diff($withoutRtf, $list);
            $rtfsDecoded = array_diff($withoutRtfdecoded, $decodedFiles);

            $this->assertEquals(count($rtfs), count($rtfsDecoded));

            sort($withoutRtfdecoded);
            sort($withoutRtf);
            $this->assertEquals($withoutRtf, $withoutRtfdecoded);
         }
      }
   }

   private function endsWithRtf($value) {
      try{
         return explode('.', $value)[1] != 'rtf';
      } catch (Throwable $e) {
         return false;
      }

   }
}
