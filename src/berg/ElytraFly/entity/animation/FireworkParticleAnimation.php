<?php

declare(strict_types=1);

namespace berg\ElytraFly\entity\animation;

use berg\ElytraFly\entity\FireworksRocket;
use pocketmine\entity\animation\Animation;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\types\ActorEvent;

class FireworkParticleAnimation implements Animation
{
    private FireworksRocket $firework;

    public function __construct(FireworksRocket $firework)
    {
        $this->firework = $firework;
    }

    public function encode(): array
    {
        static $needsFirePosition = null;
        if ($needsFirePosition === null) {
            $ref = new \ReflectionMethod(ActorEventPacket::class, 'create');
            $needsFirePosition = $ref->getNumberOfParameters() >= 4;
        }

        if ($needsFirePosition) {
            return [ActorEventPacket::create($this->firework->getId(), ActorEvent::FIREWORK_PARTICLES, 0, null)];
        }

        return [ActorEventPacket::create($this->firework->getId(), ActorEvent::FIREWORK_PARTICLES, 0)];
    }
}
