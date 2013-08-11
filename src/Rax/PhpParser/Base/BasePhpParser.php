<?php

namespace Rax\PhpParser\Base;

use Rax\PhpParser\PhpParsed;

/**
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
class BasePhpParser
{
    /**
     * @param string $content
     *
     * @return PhpParsed
     */
    public function parse($content)
    {
        $parsed = new PhpParsed();

        $tokens = token_get_all($content);

        foreach ($tokens as $i => $token) {
            switch ($token[0]) {
                case T_NAMESPACE:
                    $fqn = array();
                    while ($tokens[++$i] !== ';') {
                        if ($tokens[$i][0] === T_STRING) {
                            $fqn[] = $tokens[$i][1];
                        }
                    }
                    break;
                case T_CLASS:
                    if ($tokens[$i + 2][0] === T_STRING) {
                        $parsed->setClass($tokens[$i + 2][1]);
                    }
                    break;
            }
        }

        if (!empty($fqn)) {
            $fqn[] = $parsed->getClass();
            $parsed->setFqn(implode('\\', $fqn));
        }

        return $parsed;
    }
}
