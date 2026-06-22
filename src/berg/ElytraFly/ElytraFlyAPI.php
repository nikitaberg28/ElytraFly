<?php

declare(strict_types=1);

namespace berg\ElytraFly;

use berg\ElytraFly\item\Elytra;
use berg\ElytraFly\item\Fireworks;
use berg\ElytraFly\item\ItemRegistry;
use berg\ElytraFly\kernel\KernelDetector;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

/**
 *
 * Позволяет другим плагинам взаимодействовать с ElytraFly
 *
 * Пример использования:
 *
 *   // Проверить что ElytraFly загружен
 *   if ($server->getPluginManager()->getPlugin('ElytraFly') === null) return;
 *
 *   // Получить элитру
 *   $elytra = ElytraFlyAPI::getElytra();
 *
 *   // Выдать элитру игроку
 *   ElytraFlyAPI::giveElytra($player);
 *
 *   // Проверить летит ли игрок
 *   if (ElytraFlyAPI::isGliding($player)) { ... }
 *
 *   // Надеть элитру на игрока
 *   ElytraFlyAPI::equipElytra($player);
 */
final class ElytraFlyAPI
{
    private function __construct() {}

    /**
     * Возвращает экземпляр предмета Elytra (клон, готовый к выдаче).
     * На PMMP — наш кастомный класс Elytra.
     * На NG — встроенная элитра из ядра (pocketmine\item\VanillaItems::ELYTRA()).
     */
    public static function getElytra(): Item
    {
        if (KernelDetector::isPMMP()) {
            $elytra = ItemRegistry::getElytra();
            if ($elytra !== null) {
                return clone $elytra;
            }
        }

        // На NG или если ItemRegistry не инициализирован — берём из ванильного реестра
        return VanillaItems::ELYTRA();
    }

    /**
     * Возвращает экземпляр предмета Fireworks (клон, готовый к выдаче).
     * На NG возвращает null — фейерверки встроены в ядро, получай их сам через VanillaItems.
     */
    public static function getFireworks(): ?Item
    {
        $fw = ItemRegistry::getFireworks();
        return $fw !== null ? clone $fw : null;
    }

    /**
     * Выдаёт элитру игроку в инвентарь.
     */
    public static function giveElytra(Player $player, int $count = 1): void
    {
        $elytra = self::getElytra();
        $elytra->setCount($count);
        $player->getInventory()->addItem($elytra);
    }

    /**
     * Выдаёт фейерверк игроку в инвентарь.
     * Возвращает false если фейерверки недоступны (NG режим).
     */
    public static function giveFireworks(Player $player, int $count = 1): bool
    {
        $fw = self::getFireworks();
        if ($fw === null) {
            return false;
        }
        $fw->setCount($count);
        $player->getInventory()->addItem($fw);
        return true;
    }

    /**
     * Надевает элитру игроку в слот нагрудника.
     * Заменяет существующий нагрудник.
     */
    public static function equipElytra(Player $player): void
    {
        $player->getArmorInventory()->setChestplate(self::getElytra());
    }

    /**
     * Снимает элитру с игрока (очищает слот нагрудника если там элитра).
     */
    public static function unequipElytra(Player $player): bool
    {
        $armor = $player->getArmorInventory();
        if (self::hasElytraEquipped($player)) {
            $armor->setChestplate(VanillaItems::AIR());
            return true;
        }
        return false;
    }

    /**
     * Проверяет наличие элитры в слоте нагрудника.
     */
    public static function hasElytraEquipped(Player $player): bool
    {
        $chestplate = $player->getArmorInventory()->getChestplate();

        // На PMMP — проверяем наш класс
        if ($chestplate instanceof Elytra) {
            return true;
        }

        // На NG — сравниваем по typeId через VanillaItems
        try {
            return $chestplate->getTypeId() === VanillaItems::ELYTRA()->getTypeId();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Проверяет есть ли у игрока элитра хотя бы в одном слоте инвентаря.
     */
    public static function hasElytraInInventory(Player $player): bool
    {
        foreach ($player->getInventory()->getContents() as $item) {
            if ($item instanceof Elytra) return true;
            try {
                if ($item->getTypeId() === VanillaItems::ELYTRA()->getTypeId()) return true;
            } catch (\Throwable) {}
        }
        return false;
    }

    /**
     * Проверяет летит ли игрок на элитре прямо сейчас.
     */
    public static function isGliding(Player $player): bool
    {
        return $player->isGliding();
    }

    /**
     * Проверяет может ли игрок полететь (элитра надета и не сломана).
     */
    public static function canGlide(Player $player): bool
    {
        if (!self::hasElytraEquipped($player)) {
            return false;
        }

        $chestplate = $player->getArmorInventory()->getChestplate();

        // Если это наш класс — проверяем durability
        if ($chestplate instanceof Elytra) {
            return $chestplate->getMeta() < $chestplate->getMaxDurability();
        }

        return true;
    }

    /**
     * Возвращает текущую прочность элитры игрока.
     * null если элитра не надета.
     */
    public static function getElytraDurability(Player $player): ?int
    {
        if (!self::hasElytraEquipped($player)) {
            return null;
        }

        $chestplate = $player->getArmorInventory()->getChestplate();
        return $chestplate->getMaxDurability() - $chestplate->getMeta();
    }

    /**
     * Возвращает на каком ядре работает плагин.
     * Возможные значения: "PocketMine-MP", "NetherGames"
     */
    public static function getKernel(): string
    {
        return KernelDetector::detect();
    }

    /**
     * Возвращает true если ядро — NetherGames форк.
     */
    public static function isNetherGames(): bool
    {
        return KernelDetector::isNetherGamesFork();
    }

    /**
     * Возвращает true если ядро — оригинальный PocketMine-MP.
     */
    public static function isPMMP(): bool
    {
        return KernelDetector::isPMMP();
    }
}