<?php

use PHPUnit\Framework\TestCase;
use TNEFDecoder\TNEFAttachment;
use TNEFDecoder\TNEFFileBase;


class TNEFAttachmentTest extends TestCase
{
    /**
     * Test decoding winmail.dat file from filesystem
     */
    public function testDecode3()
    {
        $buffer = file_get_contents(dirname(__FILE__) . "/testfiles/two-files.tnef");

        $attachment = new TNEFAttachment();
        $attachment->decodeTnef($buffer);
        $files = $attachment->getFiles();

        $this->assertEquals(2, count($files));
        $this->assertEquals("AUTHORS", $files[0]->getName());
        $this->assertEquals("README", $files[1]->getName());
    }

   /**
    * @dataProvider tnefFileProvider
    */
   public function testDecodeAuto($tnefFile, $listFile) {
      $buffer = file_get_contents($tnefFile);
      $attachment = new TNEFAttachment(false, true);

      if ($listFile === null) {
         $this->expectExceptionMessage('Checksums do not match');
      }
      $attachment->decodeTnef($buffer);

      $list = file($listFile, FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES);
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

   public function tnefFileProvider() {
      $tnefFiles = glob(dirname(__FILE__) . "/testfiles/*.tnef");
      $result = [];
      foreach ($tnefFiles as $tnefFile) {
         $pathinfo = pathinfo($tnefFile);
         $listFile = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.list';

         $result[] = [
            $tnefFile,
            file_exists($listFile) ? $listFile : null,
         ];
      }

      return $result;
   }

   private function endsWithRtf($value) {
      try {
         return explode('.', $value)[1] != 'rtf';
      } catch (Throwable $e) {
         return false;
      }

   }
}
