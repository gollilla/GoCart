<?php

declare(strict_types=1);

namespace soradore\gocart\entity;

use pocketmine\Server;
use pocketmine\entity\Entity;
use pocketmine\entity\Vehicle;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\network\mcpe\protocol\{
	ActorEventPacket, SetActorLinkPacket, AnimatePacket, AddActorPacket
};
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\math\Vector3;

class Minecart extends Vehicle{
    public const NETWORK_ID = self::MINECART;

    public $width = 1.0;
    public $height = 1.0;
    public $gravity = 0.08;
    public $drag = 0.1;

    public $rider = null;


    public function getName() : string{
        return "Minecart";
    }


    public function getDrops() : array{
        $drops = [
            ItemFactory::get(Item::MINECART, 0, 1)
        ];

        return $drops;
    }

    public function kill() : void{
        parent::kill();

        if($this->lastDamageCause instanceof EntityDamageByEntityEvent){
			$damager = $this->lastDamageCause->getDamager();
			if($damager instanceof Player and $damager->isCreative()){
				return;
			}
		}
		foreach($this->getDrops() as $item){
			$this->getLevel()->dropItem($this, $item);
        }
        $this->despawnFromAll();
    }


    /*public function attack(EntityDamageEvent $source) : void{
		parent::attack($source);
		if(!$source->isCancelled()){
			$pk = new ActorEventPacket();
			$pk->entityRuntimeId = $this->id;
			$pk->event = ActorEventPacket::HURT_ANIMATION;
			Server::getInstance()->broadcastPacket($this->getViewers(), $pk);
		}
	}*/


    public function setLink(Entity $rider){
        if($this->rider === null){
			$rider->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_RIDING, true);
			$rider->getDataPropertyManager()->setVector3(Entity::DATA_RIDER_SEAT_POSITION, new Vector3(0, 1, 0));
			$pk = new SetActorLinkPacket();
			$pk->link = new EntityLink($this->getId(), $rider->getId(), EntityLink::TYPE_RIDER);
            Server::getInstance()->broadcastPacket($this->getViewers(), $pk);
			$this->rider = $rider;
			return true;
		}else{
            $rider->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_RIDING, false);
			$rider->getDataPropertyManager()->setVector3(Entity::DATA_RIDER_SEAT_POSITION, new Vector3(0, 0, 0));
			$pk = new SetActorLinkPacket();
			$pk->link = new EntityLink($this->getId(), $rider->getId(), EntityLink::TYPE_REMOVE);
            Server::getInstance()->broadcastPacket($this->getViewers(), $pk);
            $this->rider = null;
            
			return true;
        }
		return false;
    }

    public function onUpdate(int $currentTick) : bool{
        if($this->closed) return false;
        if($this->rider !== null){
            $this->yaw = $this->rider->yaw + 90;
            $x = 1 * sin(-deg2rad($this->rider->yaw));
            $z = 1 * cos(-deg2rad($this->rider->yaw));
            $this->move($x, 0, $z);
            
            parent::onUpdate($currentTick);
            return true;
        }
        return false;
    }
}