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

    public function testFromArrayString() {
        $arrayString = explode(" ", AbstractTestCase::EXAMPLE_STRING);

        $featureSet = FeatureSet::createFromStringArray(
            AbstractTestCase::EXAMPLE_KERNEL,
            $arrayString
        );

        $this->assertSame($arrayString, $featureSet->toLinuxStringArray());
        $this->assertSame(AbstractTestCase::EXAMPLE_STRING, $featureSet->toLinuxString());
        $this->assertSame(AbstractTestCase::EXAMPLE_KERNEL, $featureSet->getKernel());
    }
}