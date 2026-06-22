<?php

declare(strict_types=1);

namespace berg\ElytraFly\kernel;

final class Lang
{
    private static string $lang = 'en';

    private static array $messages = [
        'kernel.detected.pmmp' => [
            'en' => '§fKernel detected: §aPocketMine-MP§f. Registering elytra + fireworks + flight physics',
            'ru' => '§fАвто-обнаружение ядра: §aPocketMine-MP§f. Регистрирую элитры + фейерверки + физику полёта',
        ],
        'kernel.detected.ng' => [
            'en' => '§fKernel detected: §6NetherGames-MC§f. Registering flight physics only',
            'ru' => '§fАвто-обнаружение ядра: §6NetherGames-MC§f. Регистрирую физику полёта',
        ],
        'kernel.unknown' => [
            'en' => '§fUnknown kernel — falling back to §aPocketMine-MP mode',
            'ru' => '§fЯдро не распознано — используется режим §aPocketMine-MP',
        ],
        'config.lang.invalid' => [
            'en' => '§cInvalid language "{lang}" in config.yml, falling back to "en"',
            'ru' => '§cНеверный язык "{lang}" в config.yml, используется "en"',
        ],
    ];

    public static function setLang(string $lang): void
    {
        self::$lang = in_array($lang, ['en', 'ru'], true) ? $lang : 'en';
    }

    public static function get(string $key, array $replacements = []): string
    {
        $msg = self::$messages[$key][self::$lang]
            ?? self::$messages[$key]['en']
            ?? "§c[ElytraFly] Unknown key: $key";

        return empty($replacements) ? $msg : str_replace(array_keys($replacements), array_values($replacements), $msg);
    }
}
