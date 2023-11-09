<?php

declare(strict_types=1);

namespace Acaisia\CpuFeatures;

use Acaisia\CpuFeatures\Exception\UnknownBinaryStringException;

/**
 * This class contains a set of features - can be casted to and from a Linux (cpuinfo) compatible string, or words.
 *
 * There's basic methods to check if a feature is available, and some simple serialisation features into various
 * formats.
 */
class FeatureSet {

    private const BIN_VERSION_1 = 0b10101010;

    private Kernel $kernel;
    private array $features = [];

    private function __construct()
    {
    }

    public static function createEmpty(Kernel $kernel): self
    {
        $self = new self();
        $self->kernel = $kernel;
        $self->features = [];
        return $self;
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
        return array_filter(
            array_map(fn (Feature $feature) => $feature->getCpuinfoString(), $this->features), // Build array
            fn (?string $entry) => $entry !== null // Filter out null
        );
    }

    /**
     * @return Feature[]
     */
    public function toArray(): array
    {
        return $this->features;
    }

    /**
     * Creates a binary string version for storage or serialization. Please note that order of items is not preserved!
     */
    public function toBinaryString(): mixed
    {
        $countTotal = count(Feature::cases());

        // Setup an empty array. 8 bits per byte - so 8 feature flags per byte.
        $bytes = new \SplFixedArray(intdiv($countTotal, 8) + 2);
        $bytes = array_fill(0, count($bytes)+1, 0);

        // We set the first byte to our "version" (in case we want other packing in the future)
        $bytes[0] = self::BIN_VERSION_1;

        // The second byte we set to the known Kernel version
        $bytes[1] = $this->kernel->getByteVersion();

        // Pack them all
        $i = -1;
        foreach (Feature::cases() as $feature) {
            $i++;
            if ($this->doesNotHaveFeature($feature)) {
                continue;
            }
            $bit = $i % (8);
            $byte = intdiv($i, 8) + 2;
            $bytes[$byte] |= 1 << $bit;
        }

        return pack('C*', ...$bytes); // Return a string (array of bytes / char[])
    }

    /**
     * Creates a version from the binary string. Please note that order of items is not preserved!
     */
    public static function fromBinaryString(string $string): self
    {
        $array = unpack('C*', $string); // Note: unpack always starts at index 1, not 0.
        if ($array === false) {
            throw new UnknownBinaryStringException('Could not unpack binary string');
        }

        // Since unpacked strings end up at element 1 of the array, not 0, we have to shift them all down and delete the last.
        $newArray = [];
        foreach ($array as $key => $value) {
            $newArray[$key-1] = $value;
        }
        $array = $newArray;

        // First check if the version is OK
        if ($array[0] !== self::BIN_VERSION_1) {
            throw new UnknownBinaryStringException('Binary version is not correct ('.$array[0].' vs '.self::BIN_VERSION_1.')');
        }

        // Now fetch the kernel version and create a set
        $set = FeatureSet::createEmpty(Kernel::fromByte($array[1]));

        // Unpack the features
        $i = -1;
        $arraySize = count($array);
        foreach (Feature::cases() as $feature) {
            $i++;
            $bit = $i % (8);
            $byte = intdiv($i, 8) + 2;
            if ($byte < $arraySize && $array[$byte] & (1 << $bit)) {
                $set->addFeature($feature);
            }
        }

        return $set;
    }

    /**
     * Private because in general it should never be used
     */
    private function addFeature(Feature $feature): void
    {
        $this->features[] = $feature;
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