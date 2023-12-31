<?php

declare(strict_types=1);

namespace Acaisia\CpuFeatures;

use Acaisia\CpuFeatures\Exception\UnknownInKernelException;
use Brick\Math\BigNumber;

/**
 * A set of CPU features and their word + offset + description per kernel version.
 *
 * Please note that when editing, or even a new generation, for backwards compatibility reasons, the order may NOT
 * be changed. Every flag has its own bit "offset" in its order. TL;DR: Append-only list.
 */
enum Feature: string {
    case TEMPLATE_CASES = "TEMPLATE";

    /**
     * This returns the arch of this feature
     * @return Arch
     */
    public function getArch(): Arch
    {
        return Arch::X86;
    }

    /**
     * This returns the string version of the enum, as used in `/proc/cpuinfo`
     * @return null|string
     */
    public function getCpuinfoString(): ?string
    {
        if ($this->isHidden()) {
            return null;
        }
        return $this->value;
    }

    /**
     * This returns the word number used for this feature in this kernel
     *
     * @param Kernel $kernel
     * @return int
     */
    public function getWord(Kernel $kernel): int
    {
        return $this->returnFromMap(self::MAP_WORD, $kernel);
    }

    /**
     * This returns the bit number used for this feature in the given kernel
     *
     * @param Kernel $kernel
     * @return int
     */
    public function getBit(Kernel $kernel): int
    {
        return $this->returnFromMap(self::MAP_BIT, $kernel);
    }

    private function returnFromMap(array $map, Kernel $kernel): int
    {
        if (!array_key_exists($kernel->value, $map[$this->value])) {
            throw new UnknownInKernelException('The feature ' . $this->value . ' is not known in kernel ' . $kernel->value);
        }
        return $map[$this->value][$kernel->value];
    }

    public function isHidden(): bool { return false; }

    public function getDescription(): string { return ""; }

    private const MAP_BIT = [Kernel::v5_0->value => [self::TEMPLATE_CASES->value => 1]];

    private const MAP_WORD = [Kernel::v5_0->value => [self::TEMPLATE_CASES->value => 1]];
}