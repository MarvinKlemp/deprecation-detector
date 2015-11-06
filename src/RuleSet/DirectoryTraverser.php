<?php

namespace SensioLabs\DeprecationDetector\RuleSet;

use SensioLabs\DeprecationDetector\AstMap\AstMapFile;
use SensioLabs\DeprecationDetector\AstMap\AstMapGenerator;
use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\Parser\DeprecationParser;

class DirectoryTraverser
{
    /**
     * @var AstMapGenerator
     */
    private $astMapGenerator;

    /**
     * @var DeprecationParser
     */
    private $deprecationParser;

    /**
     * @param AstMapGenerator $astMapGenerator
     * @param DeprecationParser $deprecationParser
     */
    public function __construct(AstMapGenerator $astMapGenerator, DeprecationParser $deprecationParser)
    {
        $this->astMapGenerator = $astMapGenerator;
        $this->deprecationParser = $deprecationParser;
    }

    /**
     * @param string  $path
     * @param RuleSet $ruleSet
     *
     * @return RuleSet
     */
    public function traverse($path, RuleSet $ruleSet = null)
    {
        $astMap = $this->astMapGenerator->generateAstMap($path);

        if (!$ruleSet instanceof RuleSet) {
            $ruleSet = new RuleSet();
        }

        /** @var AstMapFile $astMapFile */
        foreach ($astMap->getAsts() as $astMapFile) {
            $phpFileInfo = PhpFileInfo::create($astMapFile->file());

            $this->deprecationParser->parseFile($phpFileInfo, $astMapFile->code());

            if ($phpFileInfo->hasDeprecations()) {
                $ruleSet->merge($phpFileInfo);
            }
        }

        return $ruleSet;
    }
}
