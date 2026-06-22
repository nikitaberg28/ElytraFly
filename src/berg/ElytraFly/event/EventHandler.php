<?php

declare(strict_types=1);

namespace berg\ElytraFly\event;

use berg\ElytraFly\entity\FireworksRocket;
use berg\ElytraFly\item\Elytra;
use berg\ElytraFly\item\Fireworks;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleGlideEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;

class EventHandler implements Listener
{
    public const MINIMUM_PITCH = -59;
    public const MAXIMUM_PITCH = 38;

    private PluginBase $plugin;
    private bool $cancelFallDamage;

    /** @var TaskHandler[] */
    private array $glidingTicker = [];

    public function __construct(PluginBase $plugin, bool $cancelFallDamage = true)
    {
        $this->plugin           = $plugin;
        $this->cancelFallDamage = $cancelFallDamage;
    }

    /** @priority MONITOR */
    public function onEntityDamage(EntityDamageEvent $event): void
    {
        if (!$this->cancelFallDamage) {
            return;
        }

        $entity = $event->getEntity();
        if (
            $entity instanceof \pocketmine\player\Player &&
            $entity->isGliding() &&
            $entity->getArmorInventory()->getChestplate() instanceof Elytra &&
            $event->getCause() === EntityDamageEvent::CAUSE_FALL
        ) {
            $event->cancel();
        }
    }

    /** @priority MONITOR */
    public function onPlayerMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$player->isGliding()) {
            return;
        }

        $pitch = $event->getTo()->pitch;
        if ($pitch >= self::MINIMUM_PITCH && $pitch <= self::MAXIMUM_PITCH) {
            $player->resetFallDistance();
        }
    }

    /** @priority MONITOR */
    public function onPlayerToggleGlide(PlayerToggleGlideEvent $event): void
    {
        $player  = $event->getPlayer();
        $rawUUID = $player->getUniqueId()->getBytes();

        if ($event->isGliding()) {
            $armorInventory = $player->getArmorInventory();
            $this->glidingTicker[$rawUUID] = $this->plugin->getScheduler()->scheduleRepeatingTask(
                new ClosureTask(static function () use ($armorInventory, $player): void {
                    $chestplate = $armorInventory->getChestplate();
                    if (
                        $player->hasFiniteResources() &&
                        $chestplate instanceof Elytra &&
                        $chestplate->applyDamage(1)
                    ) {
                        $armorInventory->setChestplate($chestplate);
                    }
                }),
                20
            );
        } else {
            ($this->glidingTicker[$rawUUID] ?? null)?->cancel();
            unset($this->glidingTicker[$rawUUID]);
        }
    }

    /** @priority MONITOR */
    public function onPlayerItemUse(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$player->isGliding()) {
            return;
        }

        $inventory = $player->getInventory();
        $item      = $inventory->getItemInHand();

        if (!$item instanceof Fireworks) {
            return;
        }

        $item->pop();

        $entity = new FireworksRocket($player->getLocation(), clone $item);
        $entity->getNetworkProperties()->setLong(EntityMetadataProperties::MINECART_HAS_DISPLAY, $player->getId());
        $entity->setOwningEntity($player);
        $entity->spawnToAll();

        $inventory->setItemInHand($item);
    }

    /** @priority MONITOR */
    public function onPlayerQuit(PlayerQuitEvent $event): void
    {
        $rawUUID = $event->getPlayer()->getUniqueId()->getBytes();
        ($this->glidingTicker[$rawUUID] ?? null)?->cancel();
        unset($this->glidingTicker[$rawUUID]);
    }
}
