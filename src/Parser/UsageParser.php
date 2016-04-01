<?php

namespace SensioLabs\DeprecationDetector\Parser;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\Visitor\StaticAnalysisVisitorInterface;
use SensioLabs\DeprecationDetector\Visitor\ViolationVisitorInterface;

class UsageParser implements ParserInterface
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var NodeTraverser
     */
    protected $nameResolver;

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
     * @param Parser                            $parser
     * @param StaticAnalysisVisitorInterface[]  $staticAnalysisVisitors
     * @param ViolationVisitorInterface[]       $violationVisitors
     * @param NodeTraverser                     $baseTraverser
     * @param NodeTraverser                     $staticTraverser
     * @param NodeTraverser                     $violationTraverser
     */
    public function __construct(
        Parser $parser,
        array $staticAnalysisVisitors,
        array $violationVisitors,
        NodeTraverser $baseTraverser,
        NodeTraverser $staticTraverser,
        NodeTraverser $violationTraverser
    ) {
        $this->parser = $parser;
        $this->nameResolver = $baseTraverser;
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
     *
     * @return PhpFileInfo
     */
    public function parseFile(PhpFileInfo $phpFileInfo)
    {
        $nodes = $this->parser->parse($phpFileInfo->getContents());
        $nodes = $this->nameResolver->traverse($nodes);
        $nodes = $this->staticTraverser->traverse($nodes);

        foreach ($this->violationVisitors as $visitor) {
            $visitor->setPhpFileInfo($phpFileInfo);
        }

        $this->violationTraverser->traverse($nodes);

        return $phpFileInfo;
    }
}
