<?php

namespace khokonc\mvc\Views;

class CompileInclude
{
    private const PATTERN = '~@include\("[a-zA-Z0-9._]*"\)~m';
    private const BASE_VIEW = BASE_URL."/views";
    private ?array $matches = null;

   public function __construct($content)
   {
        $this->setMatches($content);
        echo "<pre>";
        print_r($this->matches);

   }

   private function parseObject()
   {

   }

    private function setMatches($content)
    {
        preg_match_all(self::PATTERN,$content,$matches);
        $this->matches = $matches[0];
    }



}