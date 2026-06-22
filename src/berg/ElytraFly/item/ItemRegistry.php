<?php

declare(strict_types=1);

namespace berg\ElytraFly\item;

use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\StringToItemParser;
use pocketmine\world\format\io\GlobalItemDataHandlers;

final class ItemRegistry
{
    private static ?Elytra    $elytra    = null;
    private static ?Fireworks $fireworks = null;

    public static function init(
        \pocketmine\plugin\PluginBase $plugin,
        int  $elytraDura = 432,
        bool $inCreative  = true,
        bool $ngMode      = false
    ): void {
        self::$elytra    = new Elytra(new ItemIdentifier(ItemTypeIds::newId()), $elytraDura);
        self::$fireworks = $ngMode ? null : new Fireworks(new ItemIdentifier(ItemTypeIds::newId()), 'Firework Rocket');

        self::registerAll($inCreative, $ngMode);

        $server = $plugin->getServer();
        $server->getAsyncPool()->addWorkerStartHook(
            static function (int $worker) use ($server, $ngMode): void {
                $server->getAsyncPool()->submitTaskToWorker(
                    new class($ngMode) extends \pocketmine\scheduler\AsyncTask {
                        public function __construct(private bool $ngMode) {}
                        public function onRun(): void
                        {
                            ItemRegistry::registerAll(false, $this->ngMode);
                        }
                    },
                    $worker
                );
            }
        );
    }

    public static function registerAll(bool $withCreative = false, bool $ngMode = false): void
    {
        $deserializer = GlobalItemDataHandlers::getDeserializer();
        $serializer   = GlobalItemDataHandlers::getSerializer();
        $parser       = StringToItemParser::getInstance();

        if (self::$elytra !== null) {
            $elytra = self::$elytra;
            $deserializer->map(ItemTypeNames::ELYTRA, static fn() => clone $elytra);
            $serializer->map($elytra, static fn() => new SavedItemData(ItemTypeNames::ELYTRA));
            $parser->override('elytra', static fn() => clone $elytra);

            if ($withCreative) {
                CreativeInventory::getInstance()->add(clone $elytra);
            }
        }

        // Фейерверки регистрируем только на PMMP — на NG они встроены в ядро
        if (!$ngMode && self::$fireworks !== null) {
            $fw = self::$fireworks;
            $deserializer->map(ItemTypeNames::FIREWORK_ROCKET, static fn() => clone $fw);
            $serializer->map($fw, static fn() => new SavedItemData(ItemTypeNames::FIREWORK_ROCKET));
            $parser->override('firework_rocket', static fn() => clone $fw);
        }
    }

    public static function getElytra(): ?Elytra
    {
        return self::$elytra;
    }

    public static function getFireworks(): ?Fireworks
    {
        return self::$fireworks;
    }
}
