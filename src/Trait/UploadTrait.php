<?php

namespace UploadBundle\Trait;

#[\Attribute]
trait UploadTrait
{
    /**
     * Minification du JSON
     *
     * @param $json
     *
     * @return mixed
     */
    protected function jsonMinify($json)
    {
        $tokenizer = "/\"|(\/\*)|(\*\/)|(\/\/)|\n|\r/";
        $inString = false;
        $multilineComment = false;
        $singleLineComment = false;
        $from = 0;
        $lastIndex = 0;
        $rightCase = '';

        while (preg_match($tokenizer, $json, $tmp, PREG_OFFSET_CAPTURE, $lastIndex)) {
            $tmp = $tmp[0];
            $lastIndex = $tmp[1] + strlen($tmp[0]);
            $leftCase = substr($json, 0, $lastIndex - strlen($tmp[0]));
            $rightCase = substr($json, $lastIndex);
            if (!$multilineComment && !$singleLineComment) {
                $tmp2 = substr($leftCase, $from);
                if (!$inString) {
                    $tmp2 = preg_replace("/(\n|\r|\s)*/", "", $tmp2);
                }
                $newStr[] = $tmp2;
            }
            $from = $lastIndex;

            if ($tmp[0] == "\"" && !$multilineComment && !$singleLineComment) {
                preg_match("/(\\\\)*$/", $leftCase, $tmp2);
                // start of string with ", or unescaped " character found to end string
                if (!$inString || !$tmp2 || (strlen($tmp2[0]) % 2) == 0) {
                    $inString = !$inString;
                }
                $from--; // include " character in next catch
                $rightCase = substr($json, $from);
            } elseif ($tmp[0] == "/*" && !$inString && !$multilineComment && !$singleLineComment) {
                $multilineComment = true;
            } elseif ($tmp[0] == "*/" && !$inString && $multilineComment && !$singleLineComment) {
                $multilineComment = false;
            } elseif ($tmp[0] == "//" && !$inString && !$multilineComment && !$singleLineComment) {
                $singleLineComment = true;
            } elseif (($tmp[0] == "\n" || $tmp[0] == "\r") && !$inString && !$multilineComment && $singleLineComment) {
                $singleLineComment = false;
            } elseif (!$multilineComment && !$singleLineComment && !(preg_match("/\n|\r|\s/", $tmp[0]))) {
                $newStr[] = $tmp[0];
            }
        }

        $newStr[] = preg_replace('/\s+/', '', $rightCase);

        return implode("", $newStr);
    }
}
