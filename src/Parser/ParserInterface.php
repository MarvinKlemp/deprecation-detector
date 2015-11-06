<?php

namespace SensioLabs\DeprecationDetector\Parser;

use SensioLabs\DeprecationDetector\AstMap\AstMapFile;
use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use PhpParser\Node;

interface ParserInterface
{
    /**
     * @param PhpFileInfo $phpFileInfo
     * @param AstMapFile  $astMapFile
     *
     * @return PhpFileInfo
     */
    public function parseFile(PhpFileInfo $phpFileInfo, AstMapFile $astMapFile);
}
