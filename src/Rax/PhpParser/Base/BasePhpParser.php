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
        $tokens = token_get_all($content);

        foreach ($tokens as $i => $token) {
            switch ($token[0]) {
                case T_NAMESPACE:
                    $namespace = array();
                    while ($tokens[++$i] !== ';') {
                        if ($tokens[$i][0] === T_STRING) {
                            $namespace[] = $tokens[$i][1];
                        }
                    }
                    break;
                case T_CLASS:
                    if ($tokens[$i + 2][0] === T_STRING) {
                        $className = $tokens[$i + 2][1];
                    }
                    break;
            }
        }

        $phpParsed = new PhpParsed();

        if (isset($namespace)) {
            $phpParsed->setNamespace(implode('\\', $namespace));
        }

        if (isset($className)) {
            $phpParsed->setClassName($className);
        }

        return $phpParsed;
    }
}
