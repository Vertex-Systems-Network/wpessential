<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Admin\Core;

use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Admin\Core\ModuleCatalog;
use WPEssential\Platform\Admin\Core\OptionContractReader;

final class OptionContractReaderTest extends TestCase
{
    public function testEveryCatalogContractNormalizesWithMatchingOptionCount(): void
    {
        $reader = new OptionContractReader(dirname(__DIR__, 5));

        foreach (ModuleCatalog::all() as $module) {
            $contract = $reader->read($module['contract']);
            self::assertIsArray($contract, $module['contract']);
            self::assertNotSame([], $contract['groups'], $module['contract']);

            $total = 0;
            foreach ($contract['groups'] as $group) {
                self::assertNotSame('', $group['label']);
                foreach ($group['options'] as $option) {
                    self::assertContains($option['tier'], OptionContractReader::TIERS);
                    ++$total;
                }
            }

            self::assertSame($contract['coverage']['atomic_options'] ?? null, $total, $module['contract']);
        }
    }

    public function testRejectsPathTraversalAndUnknownContracts(): void
    {
        $reader = new OptionContractReader(dirname(__DIR__, 5));

        self::assertNull($reader->read('../composer'));
        self::assertNull($reader->read('does-not-exist'));
    }
}
