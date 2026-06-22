<?php

declare(strict_types=1);

namespace berg\ElytraFly\item;

use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Armor;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\ItemIdentifier;

class Elytra extends Armor
{
    public function __construct(ItemIdentifier $identifier, int $maxDurability = 432)
    {
        parent::__construct(
            $identifier,
            'Elytra',
            new ArmorTypeInfo(0, $maxDurability, ArmorInventory::SLOT_CHEST)
        );
    }

    protected function onBroken(): void {}
}
