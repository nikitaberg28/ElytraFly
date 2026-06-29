<div align="center">

# ElytraFly

[![API](https://img.shields.io/badge/API-5.0.0+-blue?style=flat-square)](https://github.com/pmmp/PocketMine-MP)
[![PMMP](https://img.shields.io/badge/PocketMine--MP-5.0.0+-green?style=flat-square)](https://github.com/pmmp/PocketMine-MP)
[![NetherGames](https://img.shields.io/badge/NetherGames-5.0.0+-orange?style=flat-square)](https://github.com/NetherGamesMC)
[![ElytraFly](https://poggit.pmmp.io/shield.state/ElytraFly)](https://poggit.pmmp.io/p/ElytraFly)

[English](#english) / [Русский](#русский)

</div>

<a name="english"></a>
## English

### What it does

Single plugin that handles everything elytra-related. No more juggling 3–4 separate plugins for the item, entity, fireworks boost, and flight physics. ElytraFly auto-detects your server kernel at runtime and loads only what's needed — this is a revolution among vanilla mechanics in PMMP.

### Configuration

```yaml
language: en # english or russian, default is en. Set ru for russian.
elytra-durability: 432 # elytra durability
elytra-breakable: true # can elytra break at all? true - yes / false - no
elytra-in-creative: true # show elytra in creative mode? true - yes / false - no
cancel-fall-damage: true # disable fall damage while gliding? true - yes / false - no. This is a somewhat broken feature — proper BDS physics are hard to port to PHP. If you manage to do it, pull requests are welcome on GitHub.
```

### API

Took care of plugin developers too, both small ones and those using this in production. Add ElytraFly as a dependency in your `plugin.yml`:

```yaml
softdepend:
  - ElytraFly
```

Guard every call with a null-check first:

```php
if ($this->getServer()->getPluginManager()->getPlugin('ElytraFly') === null) {
    return;
}
```

Item checks:

```php
use berg\ElytraFly\ElytraFlyAPI;

// Get an elytra instance
$elytra = ElytraFlyAPI::getElytra();

// Get a fireworks instance — null on NetherGames
$fireworks = ElytraFlyAPI::getFireworks();

// Give elytra to a player
ElytraFlyAPI::giveElytra($player);
ElytraFlyAPI::giveElytra($player, count: 3);

// Give fireworks — returns false on NetherGames
$success = ElytraFlyAPI::giveFireworks($player);
```

Armor slot and instance checks:

```php
// Equip elytra
ElytraFlyAPI::equipElytra($player);

// Remove elytra if equipped — false if slot was empty
$removed = ElytraFlyAPI::unequipElytra($player);

// Check if elytra is in the chestplate slot
$equipped = ElytraFlyAPI::hasElytraEquipped($player);

// Check if elytra exists anywhere in inventory
$has = ElytraFlyAPI::hasElytraInInventory($player);
```

Player flight state checks:

```php
// Is the player currently gliding?
$gliding = ElytraFlyAPI::isGliding($player);

// Can the player glide? (elytra equipped + not broken)
$canGlide = ElytraFlyAPI::canGlide($player);

// Remaining durability — null if no elytra equipped
$durability = ElytraFlyAPI::getElytraDurability($player);
```

Debug strings — originally kept these for personal use, then decided to leave them in forever. Might come in handy:

```php
// "PocketMine-MP" or "NetherGames"
$kernel = ElytraFlyAPI::getKernel();

ElytraFlyAPI::isPMMP();        // bool
ElytraFlyAPI::isNetherGames(); // bool
```

<a name="русский"></a>
## Русский

### Подробнее

Один плагин закрывает всё что связано с элитрами. Больше не надо держать 3–4 отдельных плагина для предмета, сущности, буста фейерверками и физики полёта. ElytraFly определяет ядро в рантайме и грузит только то, чего не хватает, это революция среди ванильных механик в пммп

### Конфигурация

```yaml
language: en # русский или английский, по дэфолту en английский, поставь ru - чтобы стать Zа наших.
elytra-durability: 432 # прочность элитр
elytra-breakable: true # есть ли вообще разрушимость у элитр? true - да / false - нет
elytra-in-creative: true # будут ли элитры в творческом режиме? true - да / false - нет
cancel-fall-damage: true # отключать ли урон от падения на элитрах? true - да / false - нет. Это очень сломанная функция, так как у меня не получается перенести физику из BDS в PHP, если у вас получится перенести, то делайте pull-request на гитхабе
```

---

### API

Также позаботился о маленьких разработчиках, и те что будут использовать плагин именно в продакшене. Добавь ElytraFly как зависимость в `plugin.yml`:

```yaml
softdepend:
  - ElytraFly
```

перед каждым вызовом проверяй что плагин загружен:

```php
if ($this->getServer()->getPluginManager()->getPlugin('ElytraFly') === null) {
    return;
}
```

далее у нас проверка предмета

```php
use berg\ElytraFly\ElytraFlyAPI;

// Получить экземпляр элитры
$elytra = ElytraFlyAPI::getElytra();

// Получить фейерверк — null на NetherGames
$fireworks = ElytraFlyAPI::getFireworks();

// Выдать элитру игроку
ElytraFlyAPI::giveElytra($player);
ElytraFlyAPI::giveElytra($player, count: 3);

// Выдать фейерверк — вернёт false на NetherGames
$success = ElytraFlyAPI::giveFireworks($player);
```

теперь проверка слотов и экземляров

```php
// Надеть элитру
ElytraFlyAPI::equipElytra($player);

// Снять элитру если надета — false если слот был пуст
$removed = ElytraFlyAPI::unequipElytra($player);

// Надета ли элитра в слоте нагрудника
$equipped = ElytraFlyAPI::hasElytraEquipped($player);

// Есть ли элитра где-то в инвентаре
$has = ElytraFlyAPI::hasElytraInInventory($player);
```

тут расписал как работает проверка полета игрока

```php
// Летит ли игрок прямо сейчас
$gliding = ElytraFlyAPI::isGliding($player);

// Может ли игрок полететь (элитра надета + не сломана)
$canGlide = ElytraFlyAPI::canGlide($player);

// Текущая прочность — null если элитра не надета
$durability = ElytraFlyAPI::getElytraDurability($player);
```

прочие дебаг строки, которые изначально вынес для себя, а потом решил оставить в плагине навсегда, возможно понадобится:

```php
// "PocketMine-MP" или "NetherGames-MC"
$kernel = ElytraFlyAPI::getKernel();

ElytraFlyAPI::isPMMP();        // bool
ElytraFlyAPI::isNetherGames(); // bool
```

Закидывай ссылкой в нейросеть, или скачай readme.md и закинь внутрь нейронки, она быстро изучит api и напишет плагин используя стабильный api моего плагина.

p.s: сам плагин писался без ИИ, readme.md написан тоже без ИИ кроме переработки орфографии и перевода на английский, так что есть смысл использовать именно этот плагин(такое редкость щас)

ну делал как обычно пацик с России, пользуйтесь на здоровье, лады?

почекайте еще вот тут: [https://nikitaberg.ru](https://nikitaberg.ru) и закиньте старку на репозиторий pls.
