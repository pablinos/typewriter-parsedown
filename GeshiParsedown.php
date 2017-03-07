<?php

require_once 'Parsedown.php';

class GeshiParsedown extends Parsedown
{
    protected function blockCode($Line, $Block = null)
    {
        if (isset($Block) and ! isset($Block['type']) and ! isset($Block['interrupted']))
        {
            return;
        }

        if ($Line['indent'] >= 4)
        {
            $text = substr($Line['body'], 4);

            $Block = array(
                'element' => array(
                    'name' => 'pre',
                    'text' => $text,
                    'attributes' => array (
                        'escaped' => 'true'
                    )
                ),
            );

            return $Block;
        }
    }

    protected function blockCodeContinue($Line, $Block)
    {
        if ($Line['indent'] >= 4)
        {
            if (isset($Block['interrupted']))
            {
                $Block['element']['text'] .= "\n";

                unset($Block['interrupted']);
            }

            $Block['element']['text'] .= "\n";

            $text = substr($Line['body'], 4);

            $Block['element']['text'] .= $text;

            return $Block;
        }
    }

    protected function blockCodeComplete($Block)
    {
        $text = $Block['element']['text'];

        $text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');

        $Block['element']['text'] = $text;

        return $Block;
    }


    protected function blockFencedCode($Line)
    {
        if (preg_match('/^['.$Line['text'][0].']{3,}[ ]*([\w-]+)?[ ]*$/', $Line['text'], $matches))
        {
            $Element = array(
                'name' => 'pre',
                'text' =>  '',
                'attributes' => array (
                    'escaped' => 'true'
                )
            );

            if (isset($matches[1]))
            {
                $Element['attributes']['lang'] = $matches[1];
            }

            $Block = array(
                'char'=>$Line['text'][0],
                'element' => $Element,
            );

            return $Block;
        }
    }

    protected function blockFencedCodeContinue($Line, $Block)
    {
        if (isset($Block['complete']))
        {
            return;
        }

        if (isset($Block['interrupted']))
        {
            $Block['element']['text'] .= "\n";

            unset($Block['interrupted']);
        }

        if (preg_match('/^'.$Block['char'].'{3,}[ ]*$/', $Line['text']))
        {
            $Block['element']['text'] = substr($Block['element']['text'], 1);

            $Block['complete'] = true;

            return $Block;
        }

        $Block['element']['text'] .= "\n".$Line['body'];;

        return $Block;
    }

    protected function blockFencedCodeComplete($Block)
    {
        $text = $Block['element']['text'];

        $text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');

        $Block['element']['text'] = $text;

        return $Block;
    }
}
