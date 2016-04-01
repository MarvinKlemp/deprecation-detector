<?php

namespace SensioLabs\DeprecationDetector\Tests\Visitor\Usage;

use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\FileInfo\Usage\TypeHintUsage;
use SensioLabs\DeprecationDetector\Visitor\Usage\FindArguments;
use Symfony\Component\Finder\SplFileInfo;

class FindArgumentsTest extends FindTestCase
{
    public function testMethodsOfClasses()
    {
        $source = <<<'EOC'
<?php
namespace Foo;

class Bar
{
    public static function method(Foo $a) { }

    public static function method(Foo $a, \Foo $b) { }

    public static function method() {}
}
EOC;
        $splFileInfo = $this->prophesize(SplFileInfo::class);
        $this->parsePhpFileFromStringAndTraverseWithVisitor(
            $file = PhpFileInfo::create($splFileInfo->reveal()),
            $source,
            new FindArguments()
        );

        $usages = array_map(
            function (TypeHintUsage $usage) {
                return $usage->name().'::'.$usage->getLineNumber();
            },
            $file->typeHintUsages()
        );

        $this->assertCount(3, $usages);
        $this->assertContains('Foo\Foo::6', $usages);
        $this->assertContains('Foo\Foo::8', $usages);
        $this->assertContains('Foo::8', $usages);
    }

    public function testLambdas()
    {
        $source = <<<'EOC'
<?php
namespace Foo;

$x = function(\A $a) {};
$x = function(\A $a, \B $b) {};
$x = function(A $a) {};

EOC;
        $splFileInfo = $this->prophesize(SplFileInfo::class);
        $this->parsePhpFileFromStringAndTraverseWithVisitor(
            $file = PhpFileInfo::create($splFileInfo->reveal()),
            $source,
            new FindArguments()
        );

        $usages = array_map(
            function (TypeHintUsage $usage) {
                return $usage->name().'::'.$usage->getLineNumber();
            },
            $file->typeHintUsages()
        );

        $this->assertCount(4, $usages);
        $this->assertContains('A::4', $usages);
        $this->assertContains('A::5', $usages);
        $this->assertContains('B::5', $usages);
        $this->assertContains('Foo\A::6', $usages);
    }

    public function testLambdaInClass()
    {
        $source = <<<'EOC'
<?php
namespace Foo;

class Bar
{
    public static function method() {
        function(\A $a, A $a) {};
    }
}
EOC;
        $splFileInfo = $this->prophesize(SplFileInfo::class);
        $this->parsePhpFileFromStringAndTraverseWithVisitor(
            $file = PhpFileInfo::create($splFileInfo->reveal()),
            $source,
            new FindArguments()
        );

        $usages = array_map(
            function (TypeHintUsage $usage) {
                return $usage->name().'::'.$usage->getLineNumber();
            },
            $file->typeHintUsages()
        );

        $this->assertCount(2, $usages);
        $this->assertContains('A::7', $usages);
        $this->assertContains('Foo\A::7', $usages);
    }

}
