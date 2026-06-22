<?php

declare(strict_types=1);

namespace berg\ElytraFly\kernel;

final class KernelDetector
{
    public const KERNEL_PMMP        = 'PocketMine-MP';
    public const KERNEL_NETHERGAMES = 'NetherGames';

    private static ?string $detected = null;

    public static function detect(): string
    {
        if (self::$detected !== null) {
            return self::$detected;
        }

        self::$detected = self::isNetherGames() ? self::KERNEL_NETHERGAMES : self::KERNEL_PMMP;
        return self::$detected;
    }

    private static function isNetherGames(): bool
    {
        if (defined('pocketmine\VersionInfo::BASE_VERSION')) {
            if (stripos(\pocketmine\VersionInfo::BASE_VERSION, 'NG') !== false) {
                return true;
            }
        }

        if (defined('pocketmine\VersionInfo::NAME')) {
            if (stripos(\pocketmine\VersionInfo::NAME, 'nethergames') !== false ||
                stripos(\pocketmine\VersionInfo::NAME, 'NG-') !== false) {
                return true;
            }
        }

        if (class_exists('pocketmine\network\mcpe\convert\MultiVersionProtocolConfig', false)) {
            return true;
        }

        if (class_exists('pocketmine\network\mcpe\handler\LoginPacketQueueHandler', false)) {
            return true;
        }

        try {
            $name = \pocketmine\Server::getInstance()->getName();
            if (stripos($name, 'nethergames') !== false || stripos($name, 'NG-') !== false) {
                return true;
            }
        } catch (\Throwable) {}

        return false;
    }

    public static function isPMMP(): bool
    {
        return self::detect() === self::KERNEL_PMMP;
    }

    public static function isNetherGamesFork(): bool
    {
        return self::detect() === self::KERNEL_NETHERGAMES;
    }
}
