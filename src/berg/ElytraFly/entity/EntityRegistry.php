<?php

declare(strict_types=1);

namespace berg\ElytraFly\entity;

use berg\ElytraFly\item\Fireworks;
use berg\ElytraFly\item\ItemRegistry;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\World;

final class EntityRegistry
{
    public static function init(\pocketmine\plugin\PluginBase $plugin): void
    {
        try {
            EntityFactory::getInstance()->register(
                FireworksRocket::class,
                static function (World $world, CompoundTag $nbt): FireworksRocket {
                    $itemTag = $nbt->getCompoundTag('Item');
                    if ($itemTag !== null) {
                        $item = Item::nbtDeserialize($itemTag);
                        if ($item instanceof Fireworks) {
                            return new FireworksRocket(EntityDataHelper::parseLocation($nbt, $world), $item);
                        }
                    }
                    return new FireworksRocket(
                        EntityDataHelper::parseLocation($nbt, $world),
                        new Fireworks(
                            ItemRegistry::getFireworks()?->getIdentifier() ?? new ItemIdentifier(ItemTypeIds::newId()),
                            'Firework Rocket'
                        )
                    );
                },
                ['FireworksRocket', 'FireworkRocket', EntityIds::FIREWORKS_ROCKET]
            );
        } catch (\RuntimeException $e) {
            $plugin->getLogger()->debug('FireworksRocket already registered: ' . $e->getMessage());
        }
    }
}
