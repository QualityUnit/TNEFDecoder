<?php

namespace TNEFDecoder;

class TNEFHtml
{
    const START = '<html';
    const END = '</html>';

    private $html;

    public function setTnefBuffer($buffer)
    {
        $this->parseHtml($buffer, self::START, self::END);
    }

    public function getContent()
    {
        return $this->html;
    }

    protected function parseHtml($buffer, $start, $end)
    {
        $str = ' ' . $buffer;
        $ini = strpos($str, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($str, $end, $ini) - $ini;
        $this->html = $start . substr($str, $ini, $len) . $end;
    }
}