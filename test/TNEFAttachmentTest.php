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
   public function testDecodeAuto($tnefFile, $listFile, $nestedListFile) {
      $buffer = file_get_contents($tnefFile);
      $attachment = new TNEFAttachment(false, true);

      if ($listFile === null) {
         $this->expectExceptionMessage('Checksums do not match');
      }
      $attachment->decodeTnef($buffer);

      $list = $this->readList($listFile);
      $decodedFiles = array_map(function($file) {return [$file->getName(), md5($file->getContent())];}, $attachment->getFiles());

      $this->assertEquals($list, $decodedFiles);

      if ($nestedListFile === null) {
         return;
      }

      $list = $this->readList($nestedListFile);
      $decodedFiles = array_map(function($file) {return [$file->getName(), md5($file->getContent())];}, $attachment->getFilesNested());

      $this->assertEquals($list, $decodedFiles);
   }

   public static function tnefFileProvider() {
      $tnefFiles = glob(dirname(__FILE__) . "/testfiles/*.tnef");
      $result = [];
      foreach ($tnefFiles as $tnefFile) {
         $pathinfo = pathinfo($tnefFile);
         $listFile = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.list';
         $nestedListFile = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.nested.list';

         $result[] = [
            $tnefFile,
            file_exists($listFile) ? $listFile : null,
            file_exists($nestedListFile) ? $nestedListFile : null,
         ];
      }

      return $result;
   }

   private function readList(string $filename): array
   {
      $arr = [];
      $handle = fopen($filename, 'r');
      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
         $arr[] = $data;
      }
      fclose($handle);

      return $arr;
   }
}
