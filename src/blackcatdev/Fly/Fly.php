<?php
 #  _   _       _   _           _____             
 # | \ | |     | | (_)         |  __ \            
 # |  \| | __ _| |_ ___   _____| |  | | _____   __
 # | . ` |/ _` | __| \ \ / / _ \ |  | |/ _ \ \ / /
 # | |\  | (_| | |_| |\ V /  __/ |__| |  __/\ V / 
 # |_| \_|\__,_|\__|_| \_/ \___|_____/ \___| \_/  
 #
 # Больше плагинов в https://vk.com/native_dev
 # По вопросам native.dev@mail.ru

declare(strict_types=1);

namespace blackcatdev\Fly;

use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\utils\TextFormat;

class Fly extends PluginBase implements Listener{


	public function onEnable() : void{
		$this->getLogger()->info("§2Плагин §b[Fly] §2Запущен! §1https://vk.com/native_dev");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
	}

	public function onDisable() : void{
	    $this->getLogger()->info("§cПлагин §b[Fly] §cВыключен §1https://vk.com/native_dev");
	}

	private function multiWorldCheck(Entity $entity) : bool{
		if(!$entity instanceof Player) return false;
		if($this->getConfig()->get("multi-world") === "on"){
			if(!in_array($entity->getLevel()->getName(), $this->getConfig()->get("worlds"))){
				$entity->sendMessage($this->getConfig()->get("prefix") . $this->getConfig()->get("world-off"));
				if(!$entity->isCreative()){
					$entity->setFlying(false);
					$entity->setAllowFlight(false);
				}
				return false;
			}
		}elseif($this->getConfig()->get("multi-world") === "off") return true;
		return true;
	}

	public function onJoin(PlayerJoinEvent $event) : void{
		$player = $event->getPlayer();
		if($this->getConfig()->get("onJoin-FlyReset") === true){
			if($player->isCreative()) return;
			$player->setAllowFlight(false);
			$player->sendMessage($this->getConfig()->get("fly-off"));
		}
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $str, array $args) : bool{
		if($cmd->getName() === "fly"){
			if(!$sender instanceof Player){
				$sender->sendMessage($this->getConfig()->get("prefix") . $this->getConfig()->get("noconsole"));
				return false;
			}
			if(!$sender->hasPermission("fly.cmd")){
				$sender->sendMessage($this->getConfig()->get("prefix") . TextFormat::RED . "У вас нет прав на использование этой команды");
				return false;
			}

			if(empty($args[0])){
				if(!$sender->isCreative()){
					if($this->multiWorldCheck($sender) === false) return false;
					$sender->sendMessage($sender->getAllowFlight() === false ? $this->getConfig()->get("fly-on") : $this->getConfig()->get("fly-off"));
					$sender->setAllowFlight($sender->getAllowFlight() === false ? true : false);
					$sender->setFlying($sender->isFlying() === false ? true : false);

				}else{

					$sender->sendMessage($this->getConfig()->get("prefix") . TextFormat::RED . $this->getConfig()->get("increative"));
					return false;

				}

				return false;
			}

			if(!$sender->hasPermission("fly.other")){
				$sender->sendMessage($this->getConfig()->get("prefix") . TextFormat::RED . "У вас нет прав на использование этой команды");
				return false;
			}

			if($this->getServer()->getPlayer($args[0])){
				$player = $this->getServer()->getPlayer($args[0]);

				if(!$player->isCreative()){

					if($this->multiWorldCheck($player) === false) return false;

					$player->sendMessage($player->getAllowFlight() === false ? $this->getConfig()->get("fly-on") : $this->getConfig()->get("fly-off"));
					$sender->sendMessage($player->getAllowFlight() === false ? $this->getConfig()->get("prefix") . $this->getConfig()->get("plron") . $player->getName() : $this->getConfig()->get("prefix") . $this->getConfig()->get("plroff") . $player->getName());
					$player->setAllowFlight($player->getAllowFlight() === false ? true : false);
					$player->setFlying($player->isFlying() === false ? true : false);

				}else{

					$sender->sendMessage($this->getConfig()->get("prefix") . TextFormat::RED . $player->getName() . $this->getConfig()->get("plrcreative"));
					return false;
				}
			}
		}
		
		return true;
	}

	public function onDamage(EntityDamageEvent $event) : void{
		$entity = $event->getEntity();
		if($this->getConfig()->get("onPvP-FlyReset") === true){
			if($event instanceof EntityDamageByEntityEvent){
				if($entity instanceof Player){
					$damager = $event->getDamager();
					if(!$damager instanceof Player) return;
					if($damager->isCreative()) return;
					if($damager->getAllowFlight() === true){
						$damager->sendMessage($this->getConfig()->get("prefix") . $this->getConfig()->get("fly-off"));
						$damager->setAllowFlight(false);
						$damager->setFlying(false);
					}
				}
			}
		}
	}
}