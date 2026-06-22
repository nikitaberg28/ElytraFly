<?php

declare(strict_types=1);

namespace berg\ElytraFly;

use berg\ElytraFly\entity\EntityRegistry;
use berg\ElytraFly\event\EventHandler;
use berg\ElytraFly\item\ItemRegistry;
use berg\ElytraFly\kernel\KernelDetector;
use berg\ElytraFly\kernel\Lang;
use pocketmine\plugin\PluginBase;

class ElytraFly extends PluginBase
{
    private static self $instance;

    private const DEFAULT_CONFIG = "language: en # ru - русский @ russian / en - английский @ english\nelytra-durability: 432 # прочность элитр\nelytra-breakable: true # есть ли вообще разрушимость у элитр? true - да / false - нет\nelytra-in-creative: true # будут ли элитры в творческом режиме? true - да / false - нет\ncancel-fall-damage: true # отключать ли урон от падения на элитрах? true - да / false - нет. Это очень сломанная функция, так как у меня не получается перенести физику из BDS в PHP, если у вас получится перенести, то делайте pull-request на гитхабе";

    public function onEnable(): void
    {
        self::$instance = $this;

        $this->initConfig();
        $cfg = $this->getConfig();

        Lang::setLang((string) $cfg->get('language', 'en'));

        if (KernelDetector::isNetherGamesFork()) {
            $this->getLogger()->info(Lang::get('kernel.detected.ng'));
            ItemRegistry::init(
                plugin:     $this,
                elytraDura: (int)  $cfg->get('elytra-durability', 432),
                inCreative: (bool) $cfg->get('elytra-in-creative', true),
                ngMode:     true
            );
            $this->registerEvents(cancelFallDamage: (bool) $cfg->get('cancel-fall-damage', true));

        } elseif (KernelDetector::isPMMP()) {
            $this->getLogger()->info(Lang::get('kernel.detected.pmmp'));
            ItemRegistry::init(
                plugin:     $this,
                elytraDura: (int)  $cfg->get('elytra-durability', 432),
                inCreative: (bool) $cfg->get('elytra-in-creative', true),
                ngMode:     false
            );
            EntityRegistry::init($this);
            $this->registerEvents(cancelFallDamage: (bool) $cfg->get('cancel-fall-damage', true));

        } else {
            $this->getLogger()->warning(Lang::get('kernel.unknown'));
            ItemRegistry::init($this);
            EntityRegistry::init($this);
            $this->registerEvents();
        }
    }

    private function initConfig(): void
    {
        $dataFolder = $this->getDataFolder();
        if (!is_dir($dataFolder)) {
            mkdir($dataFolder, 0755, true);
        }

        $cfgPath = $dataFolder . 'config.yml';
        if (!file_exists($cfgPath) || filesize($cfgPath) < 10) {
            file_put_contents($cfgPath, self::DEFAULT_CONFIG);
        }

        $this->reloadConfig();
    }

    public function onDisable(): void {}

    private function registerEvents(bool $cancelFallDamage = true): void
    {
        $this->getServer()->getPluginManager()->registerEvents(
            new EventHandler($this, $cancelFallDamage),
            $this
        );
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }
}