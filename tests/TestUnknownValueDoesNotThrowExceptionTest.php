<?php

declare(strict_types=1);

namespace Acaisia\CpuFeatures\Tests;

use Acaisia\CpuFeatures\FeatureSet;
use Acaisia\CpuFeatures\Kernel;

final class TestUnknownValueDoesNotThrowExceptionTest extends AbstractTestCase
{

    public function testForwardCompatibility(): void
    {
        FeatureSet::createFromString(Kernel::v6_2, 'ibpb_exit_to_user');
    }

    public function testForwardCompatibilityOtherMethod(): void
    {
        FeatureSet::createFromStringArray(Kernel::v6_2, ['ibpb_exit_to_user']);
    }

    public function testExceptionOnNewSettings(): void
    {
        $this->expectException(\ValueError::class);
        FeatureSet::createFromString(Kernel::v6_2, 'ibpb_exit_to_user', true);
    }
}