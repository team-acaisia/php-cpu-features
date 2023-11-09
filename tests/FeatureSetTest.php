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
            $this->assertSame($key, $value->getCpuinfoString());
            $i++;
        }
    }


    public function testHiddenSingle() {
        $featureSet = FeatureSet::createFromString(Kernel::v6_6, Feature::X86_K8->value);
        $this->assertTrue(Feature::X86_K8->isHidden());

        $this->assertSame('k8', Feature::X86_K8->value);
        $this->assertSame(null, Feature::X86_K8->getCpuinfoString());

        $this->assertSame([Feature::X86_K8], $featureSet->toArray());
        $this->assertSame([], $featureSet->toLinuxStringArray());
        $this->assertSame('', $featureSet->toLinuxString());
    }

    public function testHiddenNotShown() {
        $featureSet = FeatureSet::createFromStringArray(
            Kernel::v6_6,
            array_map(fn (Feature $fn) => $fn->value, Feature::cases()) // Create from value string (so all are there)
        );

        $completeString = $featureSet->toLinuxString();
        $completeStringArray = $featureSet->toLinuxStringArray();

        foreach (Feature::cases() as $feature) {
            if ($feature->isHidden()) {
                // For this testcase we're just taking a guess that the first and last units are not hidden :)
                $this->assertStringNotContainsString(' ' . $feature->value . ' ', $completeString);
                $this->assertNotContains($feature->value, $completeStringArray);
            }

            // Test has feature and other
            $this->assertTrue($featureSet->hasFeature($feature));
            $this->assertFalse($featureSet->doesNotHaveFeature($feature));
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

    public function testBinaryStringAllFeatures()
    {
        // All features
        $featureSet = FeatureSet::createFromStringArray(
            Kernel::v6_6,
            array_map(fn (Feature $fn) => $fn->value, Feature::cases()) // Create from value string (so all are there)
        );
        $this->assertEquals($featureSet, FeatureSet::fromBinaryString($featureSet->toBinaryString()));
        $this->assertEquals($featureSet->toBinaryString(), FeatureSet::fromBinaryString($featureSet->toBinaryString())->toBinaryString());
    }

    public function testBinaryStringSomeFeatures()
    {
        // Some features
        $featureSet = FeatureSet::createFromString(AbstractTestCase::EXAMPLE_KERNEL, AbstractTestCase::EXAMPLE_STRING);

        // Since the order is not preserved, we can not use assertEquals directly
        $roundTripped = FeatureSet::fromBinaryString($featureSet->toBinaryString());
        foreach (Feature::cases() as $item) {
            $this->assertSame($featureSet->hasFeature($item), $roundTripped->hasFeature($item));
            $this->assertSame($featureSet->doesNotHaveFeature($item), $roundTripped->doesNotHaveFeature($item));
        }

        // Check if the string is the same
        $this->assertEquals($featureSet->toBinaryString(), FeatureSet::fromBinaryString($featureSet->toBinaryString())->toBinaryString());
    }

    public function testBinaryStringNoFeatures()
    {
        // No features
        $featureSet = FeatureSet::createEmpty(Kernel::v6_6);
        $this->assertEquals($featureSet, FeatureSet::fromBinaryString($featureSet->toBinaryString()));
        $this->assertEquals($featureSet->toBinaryString(), FeatureSet::fromBinaryString($featureSet->toBinaryString())->toBinaryString());
    }

    public function testBinaryStringRandomFeatures()
    {
        // Some features
        $arr = [];
        foreach (Feature::cases() as $feature) {
            if (random_int(0, 1)) {
                $arr[] = $feature->value;
            }
        }

        $featureSet = featureSet::createFromStringArray(Kernel::v6_4, $arr);

        $this->assertEquals($featureSet, FeatureSet::fromBinaryString($featureSet->toBinaryString()));
        $this->assertEquals($featureSet->toBinaryString(), FeatureSet::fromBinaryString($featureSet->toBinaryString())->toBinaryString());

        // Check if the string is the same
        $this->assertEquals($featureSet->toBinaryString(), FeatureSet::fromBinaryString($featureSet->toBinaryString())->toBinaryString());
    }
}