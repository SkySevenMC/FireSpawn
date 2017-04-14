<?php
namespace firespawn;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase implements Listener{
	public $Main;
	
	public function onEnable(){
	
	@mkdir($this->getDataFolder());	
    $this->saveDefaultConfig();
	$this->getLogger()->info(TF::GREEN."FireSpawn loaded by SkySeven!");
	$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onDisable(){
	
    }
	
	public function onJoin(PlayerLoginEvent $event){
		
		if(file_exists($this->getDataFolder()."hub.yml")){
			$config = new Config($this->getDataFolder()."hub.yml",Config::YAML);
		
			if($config->get("force-hub") == true){
				$event->getPlayer()->teleport(new Position($config->get("lobbyX"), $config->get("lobbyY"), $config->get("lobbyZ"), $this->getServer()->getLevelByName($config->get("Level"))));
			}
		}
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		
		$cmd = strtolower($command);
		
		if($sender Instanceof Player){
			$player = $sender->getPlayer();
			$level = $player->getLevel()->getName();
			
			if($cmd == "hub" or $cmd == "lobby"){
				if(file_exists($this->getDataFolder()."hub.yml")){
					$config = new Config($this->getDataFolder()."hub.yml",Config::YAML);
					$player->teleport(new Position($config->get("lobbyX"), $config->get("lobbyY"), $config->get("lobbyZ"), $this->getServer()->getLevelByName($config->get("Level"))));
					$sender->sendMessage("§l§e» §r§eReturning to the ".$cmd.".");
				}else{
					$sender->sendMessage("§cThe lobby was not created");
				}
			}
			if($cmd == "spawn"){
				if(file_exists($this->getDataFolder().$level.".yml")) {
					$config = new Config($this->getDataFolder().$level.".yml",Config::YAML);
					$player->teleport(new Position($config->get("spawnX"), $config->get("spawnY"), $config->get("spawnZ"), $this->getServer()->getLevelByName($config->get("Level"))));
					$sender->sendMessage("§l§e» §r§eReturning to the spawn");
				}else{
					$sender->sendMessage("§cThe spawn was not created !");
				}
			}
			if($cmd == "sethub" && $sender->isOp()){
				$config = new Config($this->getDataFolder()."hub.yml",Config::YAML);

				$config->set("lobbyX", $sender->getFloorX());
				$config->set("lobbyY", $sender->getFloorY());
				$config->set("lobbyZ", $sender->getFloorZ());
				$config->set("Level", $level);
				$config->set("force-hub", true);
				$config->save();
				
				$sender->sendMessage("§aHub set!");
			}
			
			if($cmd == "setspawn" && $sender->isOp()){
				$config = new Config($this->getDataFolder().$level.".yml",Config::YAML);
				
				$config->set("spawnX", $sender->getFloorX());
				$config->set("spawnY", $sender->getFloorY());
				$config->set("spawnZ", $sender->getFloorZ());
				$config->set("Level", $level);
				$config->save();
				
				$sender->sendMessage("§aspawn set!");
			}
		}
	}
}