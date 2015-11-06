<?php

namespace SensioLabs\DeprecationDetector\AstMap;

use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\Parser\DeprecationParser;
use SensioLabs\DeprecationDetector\RuleSet\RuleSet;

class AstMapRuleSetTraverser
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

            $this->deprecationParser->parseFile($phpFileInfo, $astMapFile);

            if ($phpFileInfo->hasDeprecations()) {
                $ruleSet->merge($phpFileInfo);
            }
        }

        return $ruleSet;
    }
}