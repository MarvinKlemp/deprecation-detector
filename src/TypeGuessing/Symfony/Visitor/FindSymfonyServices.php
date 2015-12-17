<?php

namespace SensioLabs\DeprecationDetector\TypeGuessing\Symfony\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\Visitor\DeprecationVisitorInterface;

class FindSymfonyServices extends NodeVisitorAbstract implements DeprecationVisitorInterface
{
    /**
     * @var PhpFileInfo
     */
    protected $phpFileInfo;

    /**
     * @param PhpFileInfo $phpFileInfo
     *
     * @return $this
     */
    public function setPhpFileInfo(PhpFileInfo $phpFileInfo)
    {
        $this->phpFileInfo = $phpFileInfo;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Expr\MethodCall) {
            if ('Symfony\Component\DependencyInjection\ContainerInterface' === $node->var->getAttribute('guessedType')) {
                if ($node->name === 'get') {
                    if ($node->args[0]->value instanceof Node\Scalar\String_) {
                        $serviceId = $node->args[0]->value->value;
                        // add deprecation usage
                    }
                }
            }
        }
    }

    /**
     * @param $type
     *
     * @return bool
     */
    protected function isController($type)
    {
        return (substr($type, -10) === 'Controller');
    }
}