<?php namespace TNEFDecoder;

/**
  * SquirrelMail TNEF Decoder Plugin
  *
  * Copyright (c) 2010- Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2003  Bernd Wiegmann <bernd@wib-software.de>
  * Copyright (c) 2002  Graham Norburys <gnorbury@bondcar.com>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage tnef_decoder
  *
  */

class TNEFFileRTF extends TNEFFileBase
{
   var $size;
   var $debug;

   function __construct($debug, $buffer)
   {
      parent::__construct($debug);
      $this->type = "application/rtf";
      $this->name = "EmbeddedRTF.rtf";
      $this->debug = $debug;

      $this->decode_crtf($buffer);
   }

   function getSize()
   {
      return $this->size;
   }

   function decode_crtf(&$buffer)
   {
      $size_compressed = tnef_geti32($buffer);
      $this->size = tnef_geti32($buffer);
      $magic = tnef_geti32($buffer);
      $crc32 = tnef_geti32($buffer);

      if ($this->debug)
         tnef_log("CRTF: size comp=$size_compressed, size=$this->size");

      switch ($magic)
      {
         case CRTF_COMPRESSED:
            $this->uncompress_rtf($buffer);
            break;

         case CRTF_UNCOMPRESSED:
            $this->content = $buffer;
            break;

         default:
            if ($this->debug)
               tnef_log("Unknown Compressed RTF Format");
            break;
      }
   }

   function uncompress_rtf(&$buffer)
   {
      $uncomp = array();
      $in = 0;
      $out = 0;
      $flags = 0;
      $flag_count = 0;

      $preload = "{\\rtf1\ansi\mac\deff0\deftab720{\fonttbl;}{\f0\fnil \froman \fswiss \fmodern \fscript \fdecor MS Sans SerifSymbolArialTimes New RomanCourier{\colortbl\\red0\green0\blue0\n\r\par \pard\plain\f0\fs20\b\i\u\\tab\\tx";
      $length_preload = strlen($preload);
      for ($cnt = 0; $cnt < $length_preload; $cnt++)
         $uncomp[$out++] = $preload{$cnt};

      while ($out < ($this->size + $length_preload))
      {
         if (($flag_count++ % 8) == 0)
            $flags = ord($buffer{$in++});
         else
            $flags = $flags >> 1;

         if (($flags & 1) != 0)
         {
            $offset = ord($buffer{$in++});
            $length = ord($buffer{$in++});
            $offset = ($offset << 4) | ($length >> 4);
            $length = ($length & 0xF) + 2;
            $offset = ((int)($out / 4096)) * 4096 + $offset;
            if ($offset >= $out)
               $offset -= 4096;
            $end = $offset + $length;
            while ($offset < $end)
               $uncomp[$out++] = $uncomp[$offset++];
         }
         else
            $uncomp[$out++] = $buffer{$in++};
      }
      $this->content = substr_replace(implode("", $uncomp), "", 0, $length_preload);
      $length=strlen($this->content);
      if ($this->debug)
         tnef_log("real=$length, est=$this->size out=$out");
   }

}



