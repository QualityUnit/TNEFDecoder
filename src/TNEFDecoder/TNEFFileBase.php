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

class TNEFFileBase
{
   var $name;
   var $name_is_unicode = FALSE;
   var $code_page = '';
   var $message_code_page = ''; // parent message's code page (the whole TNEF file)
   var $type;
   var $content;
   var $created;
   var $modified;
   var $debug;
 
   function __construct($debug)
   {
      $this->name = 'Untitled';
      $this->type = 'application/octet-stream';
      $this->content = '';
      $this->debug = $debug;
   }

   function setMessageCodePage($code_page)
   {
      $this->message_code_page = $code_page;
   }

   function getCodePage()
   {
      if (empty($this->code_page))
         return $this->message_code_page;
      else
         return $this->code_page;
   }

   function getName()
   {
       if ($this->name_is_unicode) {
           return substr(mb_convert_encoding($this->name, "UTF-8" , "UTF-16LE"), 0, -1);
       }
       return $this->name;
   }

   function getType()
   {
       return $this->type;
   }

   function getSize()
   {
      return strlen($this->content);
   }

   function getCreated()
   {
      return $this->created;
   }

   function getModified()
   {
      return $this->modified;
   }

   function getContent()
   {
      return $this->content;
   }

}



