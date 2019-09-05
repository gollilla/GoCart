<?php

namespace soradore\gocart;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\entity\Entity;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\RiderJumpPacket;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\Color;
use pocketmine\math\Vector3;
use soradore\gocart\entity\Minecart;

class GoCart extends PluginBase implements Listener{


    public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->init();
	}

	public function init(){
		Entity::registerEntity(Minecart::class, false, ['Minecart', 'minecraft:minecart']);
	}

	public function onTouch(PlayerInteractEvent $ev){
		$player = $ev->getPlayer();
		$block = $ev->getBlock();
		$level = $player->getLevel();
		if($player->getInventory()->getItemInHand()->equals(Item::get(Item::MINECART, 0, 1), false, false)){
			if(!isset($this->carts[$player->getName()])){
				$level->loadChunk($block->x >> 4,$block->z >> 4);
			    $nbt = Entity::createBaseNbt($block->add(0,1,0));
			    $minecart = Entity::createEntity("Minecart", $level, $nbt);
			    $minecart->spawnToAll();
			    $minecart->setLink($player);
			    $this->carts[$player->getName()][] = $minecart;
			}
		}
	}


	public function onJump(PlayerJumpEvent $ev){
		$player = $ev->getPlayer();
		if(isset($this->carts[$player->getName()])){
			foreach($this->carts[$player->getName()] as $minecart){
			    $minecart->setLink($player);
			    $minecart->kill();
			}
			unset($this->carts[$player->getName()]);
		}
	}


	public function onMove(PlayerMoveEvent $ev){
		$player = $ev->getPlayer();
		if(isset($this->carts[$player->getName()]) && !$player->isOnGround()){
			foreach($this->carts[$player->getName()] as $minecart){
				//if($minecart->distance($player) > 1.5){
                    $minecart->setLink($player);
                    $minecart->kill();
                //}
			}
			unset($this->carts[$player->getName()]);
		}
	}

	/*public function onRe(DataPacketReceiveEvent $ev){*/
		/*$player = $ev->getPlayer();
		$level = $player->getLevel();
		foreach($level->getEntities() as $entity){
			if($entity instanceof Minecart){
				$entity->kill();
			}
		}*/
		//var_dump($ev->getPacket());
	//}

    
}


    

