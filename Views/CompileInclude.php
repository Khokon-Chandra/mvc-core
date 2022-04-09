<?php

namespace khokonc\mvc\Views;

trait CompileInclude
{



    private function setMatches($content)
    {
        preg_match_all(self::PATTERN,$content,$matches);
        $this->matches = $matches[0];
    }


    public function renderInclude($content,$params)
    {
        $this->setMatches($content);
        foreach($this->matches as $match){

            $view = explode('"',$match)[1];
            $path = $this->getViewDirectory($view);
            $replacement = $this->getViewContent($path,$params);
            $content = str_replace($match,$replacement,$content);
        }
        return $content;

    }



}