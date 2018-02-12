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
				if(file_exists($this->getDataFolder()."hub.yml")){
					
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
				$c = new Config($this->getDataFolder()."hub.yml",Config::YAML);

				$c->set("lobbyX", $sender->getFloorX());
				$c->set("lobbyY", $sender->getFloorY()+1);
				$c->set("lobbyZ", $sender->getFloorZ());
				$c->set("level", $level);
				$c->set("yaw", $sender->getYaw());
				$c->set("pitch", $sender->getPitch());
				$c->set("force-hub", true);
				$c->save();
				
				$sender->sendMessage("§aHub set!");
			}
			
			if($cmd == "setspawn" && $sender->isOp()){
				$c = new Config($this->getDataFolder()."Spawns/".$level.".yml",Config::YAML);
				
				$c->set("spawnX", $sender->getFloorX());
				$c->set("spawnY", $sender->getFloorY()+1);
				$c->set("spawnZ", $sender->getFloorZ());
				$c->set("level", $level);
				$c->set("yaw", $sender->getYaw());
				$c->set("pitch", $sender->getPitch());
				$c->save();
				
				$sender->sendMessage("§aspawn set!");
			}
		}
		return true;
	}
	
	public function onMove(PlayerMoveEvent $event){
		
		$player = $event->getplayer();
		$level = $player->getLevel();
		
		if($level == $this->getServer()->getDefaultLevel()){

			if($player->getFloorY() < 0){
				$this->teleportToHub($player);
			}
		}
	}
	
	public function teleportToHub($player){
		
		$c = new Config($this->getDataFolder()."hub.yml",Config::YAML);
		
		$player->teleport(new Position($c->get("lobbyX"), $c->get("lobbyY"), $c->get("lobbyZ"), $this->getServer()->getLevelByName($c->get("level"))), $c->get("yaw"), $c->get("pitch"));
		
	}
	
	public function teleportToSpawn($player){
		
		$level = $player->getLevel()->getName();
		$c = new Config($this->getDataFolder()."Spawns/".$level.".yml",Config::YAML);
		
		$player->teleport(new Position($c->get("spawnX"), $c->get("spawnY"), $c->get("spawnZ"), $this->getServer()->getLevelByName($c->get("level"))), $c->get("yaw"), $c->get("pitch"));
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
				if(file_exists($this->pg->getDataFolder()."hub.yml")){
					$c = new Config($this->pg->getDataFolder()."hub.yml",Config::YAML);
		
					if($c->get("force-hub") == true){
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
