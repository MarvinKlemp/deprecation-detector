<?php

namespace SensioLabs\DeprecationDetector\Parser;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\Visitor\DeprecationVisitorInterface;

class DeprecationParser implements ParserInterface
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var NodeTraverser
     */
    protected $traverser;

    /**
     * @var DeprecationVisitorInterface[]
     */
    protected $deprecationVisitors = [];

    /**
     * @param Parser                        $parser
     * @param DeprecationVisitorInterface[] $visitors
     * @param NodeTraverser                 $baseTraverser
     */
    public function __construct(Parser $parser, array $visitors, NodeTraverser $baseTraverser)
    {
        $this->parser = $parser;
        $this->traverser = $baseTraverser;
        array_map([$this, 'addDeprecationVisitor'], $visitors);
    }

    /**
     * @param DeprecationVisitorInterface $visitor
     */
    public function addDeprecationVisitor(DeprecationVisitorInterface $visitor)
    {
        $this->deprecationVisitors[] = $visitor;
        $this->traverser->addVisitor($visitor);
    }

    /**
     * @param PhpFileInfo $phpFileInfo
     *
     * @return PhpFileInfo
     */
    public function parseFile(PhpFileInfo $phpFileInfo)
    {
        foreach ($this->deprecationVisitors as $visitor) {
            $visitor->setPhpFileInfo($phpFileInfo);
        }

        $this->traverser->traverse($this->parser->parse($phpFileInfo->getContents()));

        return $phpFileInfo;
    }
}
