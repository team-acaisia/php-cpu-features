<?php

declare(strict_types=1);

namespace Acaisia\CpuFeatures\Tests;

use Acaisia\CpuFeatures\FeatureSet;

final class FeatureSetTest extends AbstractTestCase
{

    public function testFromString() {
        $featureSet = FeatureSet::createFromString(AbstractTestCase::EXAMPLE_KERNEL, AbstractTestCase::EXAMPLE_STRING);

        $this->assertSame(AbstractTestCase::EXAMPLE_STRING, $featureSet->toLinuxString());
        $this->assertSame(AbstractTestCase::EXAMPLE_KERNEL, $featureSet->getKernel());
    }
}