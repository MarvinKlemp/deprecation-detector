<?php

namespace SensioLabs\DeprecationDetector\AstMap;

use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\Parser\UsageParser;
use \ArrayIterator;

class AstMapUsageTraverser
{
    /**
     * @var AstMapGenerator
     */
    private $astMapGenerator;

    private $usageParser;

    /**
     * @param AstMapGenerator $astMapGenerator
     * @param UsageParser $usageParser
     */
    public function __construct(AstMapGenerator $astMapGenerator, UsageParser $usageParser)
    {
        $this->astMapGenerator = $astMapGenerator;
        $this->usageParser = $usageParser;
    }

    /**
     * @param string $path
     * @return ArrayIterator
     */
    public function traverse($path)
    {
        $astMap = $this->astMapGenerator->generateAstMap($path);
        $files = new ArrayIterator();

        /** @var AstMapFile $astMapFile */
        foreach ($astMap->getAsts() as $astMapFile) {
            $phpFileInfo = PhpFileInfo::create($astMapFile->file());
            $this->usageParser->parseFile($phpFileInfo, $astMapFile);

            $files->append($phpFileInfo);
        }

        return $files;
    }
}