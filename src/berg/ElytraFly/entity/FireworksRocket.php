<?php

declare(strict_types=1);

namespace berg\ElytraFly\entity;

use berg\ElytraFly\entity\animation\FireworkParticleAnimation;
use berg\ElytraFly\item\Fireworks;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;

class FireworksRocket extends Entity
{
    public const DATA_FIREWORK_ITEM = 16;

    protected int      $lifeTime  = 0;
    protected Fireworks $fireworks;

    public function __construct(Location $location, Fireworks $fireworks, ?int $lifeTime = null)
    {
        $this->fireworks = $fireworks;
        parent::__construct($location, $fireworks->getNamedTag());
        $this->setMotion(new Vector3(0.001, 0.05, 0.001));

        if ($fireworks->getNamedTag()->getCompoundTag('Fireworks') !== null) {
            $this->setLifeTime($lifeTime ?? $fireworks->getRandomizedFlightDuration());
        }

        $location->getWorld()->broadcastPacketToViewers(
            $this->location,
            LevelSoundEventPacket::nonActorSound(LevelSoundEvent::LAUNCH, $this->location->asVector3(), false)
        );
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::FIREWORKS_ROCKET;
    }

    public function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.25, 0.25);
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0.99;
    }

    protected function getInitialGravity(): float
    {
        return 0.05;
    }

    public function canSaveWithChunk(): bool
    {
        return false;
    }

    protected function tryChangeMovement(): void
    {
        $this->motion->x *= 1.15;
        $this->motion->y += 0.04;
        $this->motion->z *= 1.15;
    }

    public function setLifeTime(int $life): void
    {
        $this->lifeTime = $life;
    }

    public function getLifeTime(): int
    {
        return $this->lifeTime;
    }

    protected function doLifeTimeTick(): bool
    {
        if (--$this->lifeTime < 0 && !$this->isFlaggedForDespawn()) {
            $this->doExplosionAnimation();
            $this->playSounds();
            $this->flagForDespawn();
            return true;
        }
        return false;
    }

    protected function doExplosionAnimation(): void
    {
        $this->broadcastAnimation(new FireworkParticleAnimation($this), $this->getViewers());
    }

    public function playSounds(): void
    {
        $fireworksTag = $this->fireworks->getNamedTag()->getCompoundTag('Fireworks');
        if ($fireworksTag === null) {
            return;
        }

        $explosionsTag = $fireworksTag->getListTag('Explosions');
        if ($explosionsTag === null) {
            return;
        }

        foreach ($explosionsTag->getValue() as $info) {
            if (!$info instanceof CompoundTag) {
                continue;
            }

            $position = $this->location->asVector3();

            if ($info->getByte('FireworkType', 0) === Fireworks::TYPE_HUGE_SPHERE) {
                $this->getWorld()->broadcastPacketToViewers($this->location, LevelSoundEventPacket::nonActorSound(LevelSoundEvent::LARGE_BLAST, $position, false));
            } else {
                $this->getWorld()->broadcastPacketToViewers($this->location, LevelSoundEventPacket::nonActorSound(LevelSoundEvent::BLAST, $position, false));
            }

            if ($info->getByte('FireworkFlicker', 0) === 1) {
                $this->getWorld()->broadcastPacketToViewers($this->location, LevelSoundEventPacket::nonActorSound(LevelSoundEvent::TWINKLE, $position, false));
            }
        }
    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if ($this->closed) {
            return false;
        }

        $hasUpdate = parent::entityBaseTick($tickDiff);
        if ($this->doLifeTimeTick()) {
            $hasUpdate = true;
        }
        return $hasUpdate;
    }

    public function syncNetworkData(EntityMetadataCollection $properties): void
    {
        parent::syncNetworkData($properties);
        $properties->setCompoundTag(self::DATA_FIREWORK_ITEM, new CacheableNbt($this->fireworks->getNamedTag()));
    }

    public function saveNBT(): CompoundTag
    {
        $nbt = parent::saveNBT();
        $nbt->setTag('Item', $this->fireworks->nbtSerialize());
        return $nbt;
    }
}
