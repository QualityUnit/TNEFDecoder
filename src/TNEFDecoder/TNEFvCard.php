<? namespace TNEFDecoder;

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

class TnefvCard
{

   var $debug;
   var $name;
   var $name_is_unicode = FALSE;
   var $code_page = '';
   var $message_code_page = ''; // parent message's code page (the whole TNEF file)
   var $type;
   var $content;
   var $metafile;
   var $created;
   var $modified;
   var $surname;
   var $surname_is_unicode = FALSE;
   var $given_name;
   var $given_name_is_unicode = FALSE;
   var $middle_name;
   var $middle_name_is_unicode = FALSE;
   var $nickname;
   var $nickname_is_unicode = FALSE;
   var $company;
   var $company_is_unicode = FALSE;

   var $homepages;
   var $addresses;
   var $emails;
   var $telefones;

   function __construct($debug)
   {
      $this->debug = $debug;
      $this->name = "Untitled";
      $this->type = "text/x-vcard";
      $this->content = "";
      $this->telefones = array();
      $this->homepages = array();
      $this->emails = array();
      $this->addresses = array();
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
      return $this->name;
   }

   function getSurname()
   {
      return $this->surname;
   }

   function getGivenName()
   {
      return $this->given_name;
   }

   function getMiddleName()
   {
      return $this->middle_name;
   }

   function getNickname()
   {
      return $this->nickname;
   }

   function getCompany()
   {
      return $this->company;
   }

   function getAddresses()
   {
      return $this->addresses;
   }

   function getType()
   {
      return $this->type;
   }

   function getMetafile()
   {
      return $this->metafile;
   }

   function getSize()
   {
      return strlen($this->content);
   }

   function getContent()
   {
      return $this->content;
   }

   function getCreated()
   {
      return $this->created;
   }

   function getModified()
   {
      return $this->modified;
   }

   function getTelefones()
   {
      return $this->telefones;
   }

   function getHomepages()
   {
      return $this->homepages;
   }

   function getEmails()
   {
      return $this->emails;
   }

   function receiveTnefAttribute($attribute, $value, $length)
   {
      switch ($attribute)
      {

         // code page
         //
         case TNEF_AOEMCODEPAGE:
            $this->code_page = tnef_geti16($value);
            break;

      }
   }

   function receiveMapiAttribute($attr_type, $attr_name, $value, $length, $is_unicode=FALSE)
   {
      switch($attr_name)
      {
         case TNEF_MAPI_DISPLAY_NAME:
            $this->name = $value;

            if ($is_unicode) $this->name_is_unicode = TRUE;

            break;

         case TNEF_MAPI_SURNAME:
            $this->surname = $value;

            if ($is_unicode) $this->surname_is_unicode = TRUE;

            break;

         case TNEF_MAPI_GIVEN_NAME:
            $this->given_name = $value;

            if ($is_unicode) $this->given_name_is_unicode = TRUE;

            break;

         case TNEF_MAPI_MIDDLE_NAME:
            $this->middle_name = $value;

            if ($is_unicode) $this->middle_name_is_unicode = TRUE;

            break;

         case TNEF_MAPI_NICKNAME:
            $this->nickname = $value;

            if ($is_unicode) $this->nickname_is_unicode = TRUE;

            break;

         case TNEF_MAPI_COMPANY_NAME:
            $this->company = $value;

            if ($is_unicode) $this->company_is_unicode = TRUE;

            break;

         default:
            $rc = $this->evaluateTelefoneAttribute($attr_type, $attr_name, $value, $length);
            if (!$rc)
               $rc = $this->evaluateEmailAttribute($attr_type, $attr_name, $value, $length);
            if (!$rc)
               $rc = $this->evaluateAddressAttribute($attr_type, $attr_name, $value, $length);
            if (!$rc)
               $rc = $this->evaluateHomepageAttribute($attr_type, $attr_name, $value, $length);
            break;
      }
   }

   function evaluateTelefoneAttribute($attr_type, $attr_name, $value, $length)
   {
      global $telefone_mapping;
      $rc = 0;

      if ($length > 0)
      {
         if (array_key_exists($attr_name, $telefone_mapping))
         {
            $telefone_key = $telefone_mapping[$attr_name];
            $this->telefones[$telefone_key] = $value;
            $rc = 1;
            if ($this->debug)
               tnef_log("Setting telefone '$telefone_key' to value '$value'");
         }
      }
    
      return $rc;
   }

   function evaluateEmailAttribute($attr_type, $attr_name, $value, $length)
   {
      global $email_mapping;
      $rc = 0;

      if ($length > 0)
      {
         if (array_key_exists($attr_name, $email_mapping))
         {
            $email_key = $email_mapping[$attr_name];
            if (!array_key_exists($email_key[0], $this->emails))
               $this->emails[$email_key[0]] = array ( EMAIL_DISPLAY => "", EMAIL_TRANSPORT => "", EMAIL_EMAIL => "", EMAIL_EMAIL2 => "");
            $this->emails[$email_key[0]][$email_key[1]] = $value;
         }
      }

      return $rc;
   }

   function evaluateAddressAttribute($attr_type, $attr_name, $value, $length)
   {
      global $address_mapping;
      $rc = 0;

      if ($length > 0)
      {
         if (array_key_exists($attr_name, $address_mapping))
         {
            $address_key = $address_mapping[$attr_name];
            if (!array_key_exists($address_key[0], $this->addresses))
               $this->addresses[$address_key[0]] = array ( );
            $this->addresses[$address_key[0]][$address_key[1]] = $value;
         }
      }

      return $rc;
   }

   function evaluateHomepageAttribute($attr_type, $attr_name, $value, $length)
   {
      global $homepage_mapping;
      $rc = 0;

      if ($length > 0)
      {
         if (array_key_exists($attr_name, $homepage_mapping))
         {
            $homepage_key = $homepage_mapping[$attr_name];
            $this->homepages[$homepage_key] = $value;
            $rc = 1;
            if ($this->debug)
               tnef_log("Setting homepage '$homepage_key' to value '$value'");
         }
      }
    
      return $rc;
   }

}



