<?php

declare(strict_types=1);

namespace Acaisia\CpuFeatures\Tests;

use Acaisia\CpuFeatures\Arch;
use Acaisia\CpuFeatures\Exception\UnknownInKernelException;
use Acaisia\CpuFeatures\Feature;
use Acaisia\CpuFeatures\FeatureSet;
use Acaisia\CpuFeatures\Kernel;

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

        $actualArray = $featureSet->toArray();
        $i = 0;
        foreach ($arrayString as $key) {
            /** @var Feature $value */
            $value = $actualArray[$i];
            $this->assertSame(Feature::from($key), $value);
            $this->assertSame(Arch::X86, $value->getArch());
            $i++;
        }
    }

    /**
     * @dataProvider provideAllFeatures
     */
    public function testAllCases(Feature $feature) {
        $this->assertSame(Arch::X86, $feature->getArch());
        $this->assertIsString($feature->getDescription());

        // We only have a few features that are not available in kernel v6_6, so manually exclude them;
        if ($feature == Feature::X86_RETPOLINE_AMD ||
            $feature == Feature::X86_INVPCID_SINGLE ||
            $feature == Feature::X86_MFENCE_RDTSC ||
            $feature == Feature::X86_K7
        ) {
            $this->expectException(UnknownInKernelException::class);
        }

        $this->assertIsInt($feature->getWord(Kernel::v6_6));
        $this->assertIsInt($feature->getBit(Kernel::v6_6));
    }

    public static function provideAllFeatures(): \Generator
    {
        foreach (Feature::cases() as $feature) {
            yield [$feature];
        }
    }
}