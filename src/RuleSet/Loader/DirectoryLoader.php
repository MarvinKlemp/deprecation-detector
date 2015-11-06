<?php

namespace SensioLabs\DeprecationDetector\RuleSet\Loader;

use SensioLabs\DeprecationDetector\AstMap\AstMapRuleSetTraverser;
use SensioLabs\DeprecationDetector\RuleSet\Cache;

/**
 * Class DirectoryLoader.
 *
 * @author Christopher Hertel <christopher.hertel@sensiolabs.de>
 */
class DirectoryLoader implements LoaderInterface
{
    /**
     * @var AstMapRuleSetTraverser
     */
    protected $traverser;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @param AstMapRuleSetTraverser $traverser
     * @param Cache              $cache
     */
    public function __construct(AstMapRuleSetTraverser $traverser, Cache $cache)
    {
        $this->traverser = $traverser;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function loadRuleSet($path)
    {
        $key = $this->generateDirectoryKey($path);

        if ($this->cache->has($key)) {
            return $this->cache->getCachedRuleSet($key);
        }

        $ruleSet = $this->traverser->traverse($path);
        $this->cache->cacheRuleSet($key, $ruleSet);

        return $ruleSet;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function generateDirectoryKey($path)
    {
        return str_replace('/', '_', $path);
    }
}
