<?php

declare(strict_types=1);

namespace Acaisia\CpuFeatures;

/**
 * This class contains a set of features - can be casted to and from a Linux (cpuinfo) compatible string, or words.
 *
 * There's basic methods to check if a feature is available, and some simple serialisation features into various
 * formats.
 */
class FeatureSet {

    private Kernel $kernel;
    private array $features = [];

    private function __construct() {
    }

    /**
     * @param Kernel $kernel
     * @param string $features Formatted in the form of "fpu vme de msr" etc.
     * @return self
     */
    public static function createFromString(Kernel $kernel, string $features): self
    {

    }

    /**
     * @param Kernel $kernel
     * @param array $features Formatted in the form of ["fpu", "vme", (...)]
     * @return self
     */
    public static function createFromStringArray(Kernel $kernel, array $features): self
    {

    }

    /**
     * @return string Formatted in the form of "fpu vme de msr" etc.
     */
    public function toLinuxString(): string {

    }

    /**
     * @return string Formatted in the form of ["fpu", "vme", (...)] etc.
     */
    public function toLinuxStringArray(): array {

    }

    /**
     * @return string Formatted in the form of [Feature::X86_FEATURE_FPU] etc.
     */
    public function toArray(): array {

    }
}