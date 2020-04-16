<?php

namespace TNEFDecoder;

class TNEFHtml
{
    const START = '<html';
    const END = '</html>';

    private $html;

    public function setTnefBuffer($buffer)
    {
        $this->parseHtml($buffer);
    }

    public function getContent()
    {
        return $this->html;
    }

    protected function parseHtml($str)
    {
        $ini = strpos($str, self::START);
        if ($ini == 0) return '';
        $ini += strlen(self::START);
        $len = strpos($str, self::END, $ini) - $ini;
        $this->html = self::START . substr($str, $ini, $len) . self::END;
    }
}