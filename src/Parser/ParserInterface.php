<?php

namespace SensioLabs\DeprecationDetector\Parser;

use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use PhpParser\Node;

interface ParserInterface
{
    /**
     * @param PhpFileInfo $phpFileInfo
     * @param Node[]      $code
     *
     * @return PhpFileInfo
     */
    public function parseFile(PhpFileInfo $phpFileInfo, $code);
}
