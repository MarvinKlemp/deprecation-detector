<?php

namespace SensioLabs\DeprecationDetector\Parser;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use SensioLabs\DeprecationDetector\AstMap\AstMapFile;
use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\Visitor\StaticAnalysisVisitorInterface;
use SensioLabs\DeprecationDetector\Visitor\ViolationVisitorInterface;

class UsageParser extends Parser implements ParserInterface
{
    /**
     * @var NodeTraverser
     */
    protected $baseTraverser;

    /**
     * @var NodeTraverser
     */
    protected $staticTraverser;

    /**
     * @var NodeTraverser
     */
    protected $violationTraverser;

    /**
     * @var ViolationVisitorInterface[]
     */
    protected $violationVisitors;

    /**
     * @param StaticAnalysisVisitorInterface[] $staticAnalysisVisitors
     * @param ViolationVisitorInterface[]      $violationVisitors
     * @param NodeTraverser                    $baseTraverser
     * @param NodeTraverser                    $staticTraverser
     * @param NodeTraverser                    $violationTraverser
     */
    public function __construct(
        array $staticAnalysisVisitors,
        array $violationVisitors,
        NodeTraverser $baseTraverser,
        NodeTraverser $staticTraverser,
        NodeTraverser $violationTraverser
    ) {
        parent::__construct(new Lexer());
        $this->baseTraverser = $baseTraverser;
        $this->staticTraverser = $staticTraverser;
        foreach ($staticAnalysisVisitors as $visitor) {
            $this->staticTraverser->addVisitor($visitor);
        }

        $this->violationTraverser = $violationTraverser;
        $this->violationVisitors = $violationVisitors;
        foreach ($violationVisitors as $visitor) {
            $this->violationTraverser->addVisitor($visitor);
        }
    }

    /**
     * @param PhpFileInfo $phpFileInfo
     * @param AstMapFile  $astMapFile
     *
     * @return PhpFileInfo
     */
    public function parseFile(PhpFileInfo $phpFileInfo, AstMapFile $astMapFile)
    {
        $code = $this->baseTraverser->traverse($astMapFile->code());
        $code = $this->staticTraverser->traverse($code);
        $astMapFile->updateCode($code);

        foreach ($this->violationVisitors as $visitor) {
            $visitor->setPhpFileInfo($phpFileInfo);
        }

        $this->violationTraverser->traverse($code);

        return $phpFileInfo;
    }
}
