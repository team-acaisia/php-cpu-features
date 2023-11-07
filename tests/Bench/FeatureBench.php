<?php

declare(strict_types=1);

namespace Acaisia\CpuFeatures\Tests\Bench;

use Acaisia\CpuFeatures\FeatureSet;
use Acaisia\CpuFeatures\Tests\AbstractTestCase;

class FeatureBench
{
    public function benchFromString()
    {
        $featureset = FeatureSet::createFromString(AbstractTestCase::EXAMPLE_KERNEL, AbstractTestCase::EXAMPLE_STRING);
    }
}