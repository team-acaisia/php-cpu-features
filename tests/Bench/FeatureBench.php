<?php

declare(strict_types=1);

namespace Acaisia\CpuFeatures\Tests\Bench;

use Acaisia\CpuFeatures\Feature;
use Acaisia\CpuFeatures\FeatureSet;
use Acaisia\CpuFeatures\Kernel;
use Acaisia\CpuFeatures\Tests\AbstractTestCase;
use PhpBench\Benchmark\Metadata\Annotations\ParamProviders;

class FeatureBench
{
    public function benchFromString()
    {
        $featureset = FeatureSet::createFromString(AbstractTestCase::EXAMPLE_KERNEL, AbstractTestCase::EXAMPLE_STRING);
    }

    /**
     * @ParamProviders("provideArray")
     */
    public function benchFromArray(array $data)
    {
        $featureset = FeatureSet::createFromStringArray(AbstractTestCase::EXAMPLE_KERNEL, $data);
    }

    /**
     * @ParamProviders("provideFeatureset")
     */
    public function benchToString(array $fs)
    {
        $a = $fs[0]->toLinuxString();
    }

    /**
     * @ParamProviders("provideFeatureset")
     */
    public function benchToArray(array $fs)
    {
        $a = $fs[0]->toArray();
    }

    /**
     * @ParamProviders("provideFeatureset")
     */
    public function benchToLinuxStringArray(array $fs)
    {
        $a = $fs[0]->toLinuxStringArray();
    }

    /**
     * @ParamProviders("provideSomeFeatures")
     */
    public function benchHasFeature(array $data)
    {
        $fs = $data[0];
        $f = $data[1];
        $test = $fs->hasFeature($f);
        $test2 = $fs->doesNotHaveFeature($f);
    }

    /**
     * @ParamProviders("provideSomeFeatures")
     */
    public function benchGetBit(array $data)
    {
        /** @var Feature $feature */
        $feature = $data[1];
        $feature->getBit(Kernel::v6_0);
        $feature->getWord(Kernel::v6_0);
    }

    /**
     * @ParamProviders("provideSomeFeaturesets")
     */
    public function benchBinaryPackingUnpacking(array $data)
    {
        /** @var FeatureSet $featureset */
        $featureset = $data[0];
        $fs2 = FeatureSet::fromBinaryString($featureset->toBinaryString());
    }

    public function provideArray(): \Generator
    {
        yield explode(' ', AbstractTestCase::EXAMPLE_STRING);
    }

    public function provideSomeFeaturesets(): \Generator
    {
        yield [FeatureSet::createFromString(AbstractTestCase::EXAMPLE_KERNEL, AbstractTestCase::EXAMPLE_STRING)];
        yield [FeatureSet::createEmpty(Kernel::v6_6)];

        // Random set
        $arr = [];
        foreach (Feature::cases() as $feature) {
            if (random_int(0, 1)) {
                $arr[] = $feature->value;
            }
        }
        yield [featureSet::createFromStringArray(Kernel::v6_4, $arr)];
    }

    public function provideFeatureset(): \Generator
    {
        yield [FeatureSet::createFromString(AbstractTestCase::EXAMPLE_KERNEL, AbstractTestCase::EXAMPLE_STRING)];
    }

    public function provideSomeFeatures(): \Generator
    {
        $fset = FeatureSet::createFromString(AbstractTestCase::EXAMPLE_KERNEL, AbstractTestCase::EXAMPLE_STRING);

        yield [$fset, Feature::X86_3DNOW];
        yield [$fset, Feature::X86_ARCH_CAPABILITIES];
        yield [$fset, Feature::X86_AVX512_BF16];
        yield [$fset, Feature::X86_SSSE3];
        yield [$fset, Feature::X86_AVX512_4FMAPS];
    }
}