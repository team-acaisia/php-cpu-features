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

    private function __construct()
    {
    }

    /**
     * @param Kernel $kernel
     * @param string $features Formatted in the form of "fpu vme de msr" etc.
     * @return self
     */
    public static function createFromString(Kernel $kernel, string $features): self
    {
        $self = new self();
        $self->kernel = $kernel;

        foreach (explode(' ', $features) as $ftr) {
            $self->features[] = Feature::from($ftr);
        }

        return $self;
    }

    /**
     * @param Kernel $kernel
     * @param array $features Formatted in the form of ["fpu", "vme", (...)]
     * @return self
     */
    public static function createFromStringArray(Kernel $kernel, array $features): self
    {
        $self = new self();
        $self->kernel = $kernel;
        foreach ($features as $ftr) {
            $self->features[] = Feature::from($ftr);
        }

        return $self;
    }

    /**
     * @return string Formatted in the form of "fpu vme de msr" etc.
     */
    public function toLinuxString(): string
    {
        return implode(' ', $this->toLinuxStringArray());
    }

    /**
     * @return string Formatted in the form of ["fpu", "vme", (...)] etc.
     */
    public function toLinuxStringArray(): array
    {
        return array_map(fn (Feature $feature) => $feature->value, $this->features);
    }

    /**
     * @return string Formatted in the form of [Feature::X86_FEATURE_FPU] etc.
     */
    public function toArray(): array
    {
        return $this->features;
    }

    public function hasFeature(Feature $feature): bool
    {
        return in_array($feature, $this->features);
    }

    public function doesNotHaveFeature(Feature $feature): bool
    {
        return !$this->hasFeature($feature);
    }

    public function getKernel(): Kernel
    {
        return $this->kernel;
    }
}