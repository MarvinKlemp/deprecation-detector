<?php

namespace SensioLabs\DeprecationDetector\Parser;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\Visitor\DeprecationVisitorInterface;
use \PhpParser\Node;

class DeprecationParser implements ParserInterface
{
    /**
     * @var DeprecationVisitorInterface[]
     */
    protected $deprecationVisitors = array();

    /**
     * @param DeprecationVisitorInterface[] $visitors
     */
    public function __construct(array $visitors, NodeTraverser $baseTraverser)
    {
        $this->traverser = $baseTraverser;

        array_map(array($this, 'addDeprecationVisitor'), $visitors);
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
     * @param Node[]      $code
     *
     * @return PhpFileInfo
     */
    public function parseFile(PhpFileInfo $phpFileInfo, $code)
    {
        foreach ($this->deprecationVisitors as $visitor) {
            $visitor->setPhpFileInfo($phpFileInfo);
        }

        /** @TODO possible risks because traverse may change the AST */
        $this->traverser->traverse($code);

        return $phpFileInfo;
    }
}
