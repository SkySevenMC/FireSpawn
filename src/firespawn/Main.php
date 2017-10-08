<?php
namespace firespawn;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
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
	@mkdir($this->getDataFolder()."Hub");
	@mkdir($this->getDataFolder()."Spawns");	
    $this->saveDefaultConfig();
	$this->getLogger()->info(TF::GREEN."FireSpawn loaded by SkySeven!");
	$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onDisable(){
	
    }
	
	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new JoinTask($this, $player), 20);
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
		
		
		if($sender Instanceof Player){
			$player = $sender->getPlayer();
			$level = $player->getLevel()->getName();
			
			if($cmd == "hub" or $cmd == "lobby"){
				if(file_exists($this->getDataFolder()."Hub/hub.yml")){
					
					$this->teleportToHub($player);
					$sender->sendMessage("§l§e» §r§eReturning to the ".$cmd.".");
					
				}else{
					$sender->sendMessage("§cThe lobby was not created");
				}
			}
			if($cmd == "spawn"){
				if(file_exists($this->getDataFolder()."Spawns/".$level.".yml")) {
					$this->teleportToSpawn($player);
					$sender->sendMessage("§l§e» §r§eReturning to the spawn.");
					
				}else{
					$sender->sendMessage("§cThe spawn was not created !");
				}
			}
			if($cmd == "sethub" && $sender->isOp()){
				$config = new Config($this->getDataFolder()."Hub/hub.yml",Config::YAML);

				$config->set("lobbyX", $sender->getFloorX());
				$config->set("lobbyY", $sender->getFloorY());
				$config->set("lobbyZ", $sender->getFloorZ());
				$config->set("level", $level);
				$config->set("yaw", $sender->getYaw());
				$config->set("pitch", $sender->getPitch());
				$config->set("force-hub", true);
				$config->save();
				
				$sender->sendMessage("§aHub set!");
			}
			
			if($cmd == "setspawn" && $sender->isOp()){
				$config = new Config($this->getDataFolder()."Spawns/".$level.".yml",Config::YAML);
				
				$config->set("spawnX", $sender->getFloorX());
				$config->set("spawnY", $sender->getFloorY());
				$config->set("spawnZ", $sender->getFloorZ());
				$config->set("level", $level);
				$config->set("yaw", $sender->getYaw());
				$config->set("pitch", $sender->getPitch());
				$config->save();
				
				$sender->sendMessage("§aspawn set!");
			}
		}
		return true;
	}
	public function teleportToHub($player){
		
		$config = new Config($this->getDataFolder()."Hub/"."hub.yml",Config::YAML);
		
		$player->teleport(new Position($config->get("lobbyX"), $config->get("lobbyY"), $config->get("lobbyZ"), $this->getServer()->getLevelByName($config->get("level"))), $config->get("yaw"), $config->get("pitch"));
		
	}
	
	public function teleportToSpawn($player){
		
		$level = $player->getLevel()->getName();
		$config = new Config($this->getDataFolder()."Spawns/".$level.".yml",Config::YAML);
		
		$player->teleport(new Position($config->get("spawnX"), $config->get("spawnY"), $config->get("spawnZ"), $this->getServer()->getLevelByName($config->get("level"))), $config->get("yaw"), $config->get("pitch"));
	}
}
class JoinTask extends PluginTask {
	public $sec = 4;
	
	public function __construct($pg, $player){
		$this->pg = $pg;
		$this->player = $player;
		parent::__construct($pg, $player);
	}
	
	public function onRun(int $tick){
		$this->sec--;
		$player = $this->player;
		
		if($player->isOnline()){
			
			if($this->sec == 0){
				if(file_exists($this->pg->getDataFolder()."Hub/hub.yml")){
					$config = new Config($this->pg->getDataFolder()."Hub/hub.yml",Config::YAML);
		
					if($config->get("force-hub") == true){
						$this->pg->teleportToHub($player);
					}
				}
				$this->pg->getServer()->getScheduler()->cancelTask($this->getTaskId());	
			}
		}else{
			$this->pg->getServer()->getScheduler()->cancelTask($this->getTaskId());
		}
	}
}
