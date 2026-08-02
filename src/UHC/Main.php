<?php
namespace UHC;

use pocketmine\event\inventory\FurnaceSmeltEvent;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\tile\Chest as TileChest;
use pocketmine\tile\Tile;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;
use pocketmine\scheduler\CallbackTask;
use pocketmine\scheduler\Task;
use pocketmine\level\Position;
use pocketmine\item\Item;
use pocketmine\event\inventory\InventoryEvent;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\entity\Effect;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\block\Block;

class Main extends PluginBase implements Listener{
	public function onEnable(){
    $this->getServer()->loadLevel("UHC");
	$this->getServer()->getPluginManager()->registerEvents($this,$this);
	$this->getLogger()->info(TextFormat::YELLOW . "Author: SilverSky
	QQ: 2195834005 The Plugin is now Enabeled");
	@mkdir($this->getDataFolder(),0755);
       $config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
       $slot = new Config($this->getDataFolder() . "/slot.yml", Config::YAML);
       $config->set("Start",0);
       $slot->set("Player",0);
       $config->save();
           $slot->save();
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this,"gameTask"]),20);
	}
	public function onDisable(){
	    $this->getLogger()->info("The Plugin is Disable");
		$this->reload($this->getServer()->getLevelByName("UHC"));
       $config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
       $slot = new Config($this->getDataFolder() . "/slot.yml", Config::YAML);
       $config->set("Start",0);
       $slot->set("Player",0);
       $config->save();
           $slot->save();
	}

	public function onDeath(PlayerDeathEvent $event){
	$player = $event->getPlayer();
        if($player->getLevel()->getFolderName() == "UHC"){
        foreach([0, 1] as $num){
            $player->getLevel()->setBlock(new Vector3($player->getX() + $num, $player->getY(), $player->getZ()), Block::get(Block::CHEST));
        }
            $fx = $player->getX();
            $sx = $fx + 1;
            $y = $player->getY();
            $z = $player->getZ();
           $fnbt = new CompoundTag("", [
            new ListTag("Items", []),
            new StringTag("id", Tile::CHEST),
            new IntTag("x", $fx),
            new IntTag("y", $y),
            new IntTag("z", $z)
        ]);
        $fnbt->Items->setTagType(NBT::TAG_Compound);
        $ftile = Tile::createTile("Chest", $player->getLevel()->getChunk($fx >> 4, $z >> 4), $fnbt);
            $snbt = new CompoundTag("", [
            new ListTag("Items", []),
            new StringTag("id", Tile::CHEST),
            new IntTag("x", $sx),
            new IntTag("y", $y),
            new IntTag("z", $z)
        ]);
        $snbt->Items->setTagType(NBT::TAG_Compound);
        $stile = Tile::createTile("Chest", $player->getLevel()->getChunk($sx >> 4, $z >> 4), $snbt);
            $ftile->pairWith($stile);
                foreach($player->getDrops() as $drop){
                    $ftile->getInventory()->addItem($drop);
                    $event->setDrops([]);
            }
        }
        if($event instanceof EntityDamageByEntityEvent){
	$cause = $event->getPlayer()->getLastDamageCause();
	$killer = $cause->getDamager();
	$namep = $player->getName();
	$namek = $killer->getName();
	if($player->getLevel()->getFolderName() == "UHC"){
		$i = 0;
		foreach($player->getLevel()->getPlayers() as $players){
			if($players->getGamemode() == 0){
				$i++;
			}
		}
		foreach($players->getLevel()->getPlayers() as $players){
            $players->sendMessage(TextFormat::DARK_GRAY . "[" . TextFormat::RED . "UHC" . TextFormat::DARK_GRAY . "] " . TextFormat::GOLD . "$namep was killed by $namek [$i/24]");
        }
	if($cause instanceof EntityDamageByEntityEvent and $killer instanceof Player){
		$killer->addEffect(Effect::getEffect(10)->setDuration(20*5)->setAmplifier(3)->setVisible(true));
	            }
	        }
	    }
    }
		public function onCommand(CommandSender $sender, Command $command, $Label, array $args){
			$config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
            $slot = new Config($this->getDataFolder() . "/slot.yml", Config::YAML);
                  if($sender instanceof Player){
			switch($command->getName()){
                case "uhcreload":
                    if($sender->isOp()){
                    $config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
                    $slot = new Config($this->getDataFolder() . "/slot.yml", Config::YAML);
					$config->set("Start", 0);
                    $slot->set("Player", 0);
                    $config->save();
                    $slot->save();
                    }
                case "tpm":
                    $sx = intval($sender->getX());
                    $sy = intval($sender->getY());
                    $sz = intval($sender->getZ());
                    $mpos = new Vector3($sx, $sy, $sz);
                    $sender->teleport($mpos);
                    return NULL;
                case "uhcmake":
                    if($sender->isOp()){
                        if(file_exists($this->getServer()->getDataPath() . "/worlds/" . "UHC")){
                            $this->getServer()->loadLevel("UHC");
                            $this->zipper($sender, "UHC");
                        }
                    }
					return NULL;
			case "uhcjoin":
                    $this->getServer()->loadLevel("UHC");
					$players = $this->getServer()->getLevelByName("UHC")->getPlayers();
                    if($sender->getLevel()->getFolderName() != "UHC"){
					$i = 0;
					foreach($players as $xh){
						if($xh->getGamemode() == 2){
							$i++;
						}
					}
					$cp = $i+1;
			if($cp < 24 and $config->get("Start") == 0){
			$level = $this->getServer()->getLevelByName("UHC");
                foreach($level->getPlayers() as $players){
			$players->sendMessage(TextFormat::DARK_GRAY . "[" . TextFormat::RED . "UHC" . TextFormat::DARK_GRAY . "] " . TextFormat::YELLOW . $sender->getName() . " Join the game [$cp/24]");
                }
                $sender->sendMessage(TextFormat::DARK_GRAY . "[" . TextFormat::RED . "UHC" . TextFormat::DARK_GRAY . "] " . TextFormat::YELLOW . $sender->getName() . " Join the game [$cp/24]");
			$sender->teleport($level->getSafeSpawn());
			$sender->setMaxHealth(40);
			$sender->setHealth(40);
			$sender->setGamemode(2);
			$sender->addEffect(Effect::getEffect(11)->setDuration(20*114514)->setAmplifier(11)->setVisible(false));
			}else{
               $sender->sendMessage(TextFormat::DARK_GRAY . "[" . TextFormat::RED . "UHC" . TextFormat::DARK_GRAY . "] " . TextFormat::DARK_RED . "游戏已开始或者已满人");
            }
                        }else{
                        $sender->sendMessage(TextFormat::DARK_GRAY . "[" . TextFormat::RED . "UHC" . TextFormat::DARK_GRAY . "] " . TextFormat::RED . "你已经在游戏中了!");
                    }
			return NULL;
			case "uhcquit":
			if($sender->getLevel()->getName() == "UHC"){
			$sender->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
			$sender->setMaxHealth(20);
			$sender->setHealth(20);
			$sender->setGamemode(2);
		    $sender->removeAllEffects();
			}else{
			$sender->sendMessage('You can use it in "UHC" world');
			}
            }
			return NULL;
			}else{
			$sender->sendMessage("控制台你玩个寄吧");
			}
      }

    public function zipper($player, $name){
		$path = realpath($player->getServer()->getDataPath() . 'worlds/' . $name);
		$zip = new \ZipArchive;
		@mkdir($this->getDataFolder(). 'arenas/', 0755);
		$zip->open($this->getDataFolder() . 'arenas/' . $name . '.zip', $zip::CREATE | $zip::OVERWRITE);
		$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::LEAVES_ONLY);
		foreach($files as $datos){
			if(!$datos->isDir()){
				$relativePath = $name . '/' . substr($datos, strlen($path) + 1);
				$zip->addFile($datos, $relativePath);
			}
		}
		$zip->close();
		$player->getServer()->loadLevel($name);
		unset($zip, $path, $files);
	}
    public function reload($level){
        $name = $level->getFolderName();
        if($this->getServer()->isLevelLoaded($name)){
            $this->getServer()->unloadLevel($this->getServer()->getLevelByName("UHC"));
        }
        $zip = new \ZipArchive;
        $zip->open($this->getDataFolder() . 'arenas/' . $name . '.zip');
        $zip->extractTo($this->getServer()->getDataPath() . 'worlds');
        $zip->close();
        unset($zip);
            $this->getServer()->loadLevel($name);
        return true;
    }
    
    public function onBlockBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$level = $player->getLevel()->getFolderName();
		if($level == "UHC"){
				if($event->getBlock()->getId() == 18){
					$rand = mt_rand(1,10);
					if($rand == 1){
						$drops = array(Item::get(Item::APPLE,0,1));
						$event->setDrops($drops);
					}
                    if($rand == 10){
                        $drops = array(Item::get(Item::DYE,15,1));
                        $event->setDrops($drops);
                    }
				}
            if($event->getBlock()->getId() == 31){
                $rd = mt_rand(1,10);
                if($rd == 6){
                    $drops = array(Item::get(Item::STRING,0,1));
                    $event->setDrops($drops);
                }
            }
            if($event->getBlock()->getId() == 173){
                $coalblock = mt_rand(1,10);
                if($coalblock == 1){
                    $drops = array(Item::get(Item::OBSIDIAN,0,1));
                    $event->setDrops($drops);
}else{
                    $drops = array(Item::get(Item::AIR,0,1));
                    $event->setDrops($drops);
                }
            }
            if($event->getBlock()->getId() == 16){
                $coal = mt_rand(1,20);
                    if($coal == 20 or $coal == 2){
                    $drops = array(Item::get(Item::ARROW,0,1));
                    $event->setDrops($drops);
                }
                if($coal == 1){
                    $drops = array(Item::get(Item::BOOK,0,1));
                    $event->setDrops($drops);
                }
                    }
            if($event->getBlock()->getId() == 1){
                $eb = mt_rand(1,300);
                    if($eb == 1 or $eb == 3 or $eb ==  4){
                        $drops = array(Item::get(Item::ENCHANTING_BOTTLE,0,1));
                        $event->setDrops($drops);
                    }
            }
            if($event->getBlock()->getId() == 14){
                $drops = array(Item::get(Item::GOLD_INGOT,0,1));
                $event->setDrops($drops);
            }
            if($event->getBlock()->getId() == 15){
                $drops = array(Item::get(Item::IRON_INGOT,0,1));
                $event->setDrops($drops);
            }
			}
    }
    public function gameTask(){
        $level = $this->getServer()->getLevelByName("UHC");
        static $mtp = 120;
        static $safe = 1;
        if($mtp > 0){
            $mtp--;
            if($mtp == 0){
            foreach($this->getServer()->getLevelByName("UHC")->getPlayers() as $tip){
                $tip->sendMessage(TextFormat::DARK_GRAY . "[" . TextFormat::RED . "UHC" . TextFormat::DARK_GRAY . "]" . TextFormat::AQUA . " 如果卡方块了使用/tpm逃脱");
            }
                $mtp = 120;
        }
        }
                    static $seconds = 180;
                    static $gt = 2400;
                    static $hp = 1;
                    $config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
                    $slot = new Config($this->getDataFolder() . "/slot.yml", Config::YAML);
        $Taskover = 0;
        foreach($level->getPlayers() as $taskPlayers){
            if($taskPlayers->getGamemode() == 2){
                $Taskover++;
            }
        }
        if($Taskover >= 2){
            $slot = new Config($this->getDataFolder() . "/slot.yml", Config::YAML);
            $slot->set("Player",1);
            $slot->save();
        if($seconds > 0 and $config->get("Start") == 0){
            $seconds--;
        foreach($this->getServer()->getLevelByName("UHC")->getPlayers() as $players){
            $players->sendPopup(TextFormat::DARK_GRAY . "[" . TextFormat::RED . "UHC" . TextFormat::DARK_GRAY . "]" . TextFormat::GOLD . " 游戏还有 $seconds 秒开始");
            }
        }
        }
        if($seconds == 0){
            $config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
            $config->set("Start", 1);
                $config->save();
            foreach($this->getServer()->getLevelByName("UHC")->getPlayers() as $players){
                $players->sendMessage(TextFormat::DARK_GRAY . "[" . TextFormat::RED . "UHC" . TextFormat::DARK_GRAY . "]". TextFormat::GOLD . " 游戏开始!时间限制40分钟");
                if($players->getGamemode() == 2){
					$players->removeAllEffects();
                                $x = mt_rand(-128,127);
                                $z = mt_rand(-128,127);
                $y = 125;
                $pos = new Vector3($x,$y,$z);
                $players->setGamemode(0);
                $players->teleport($pos);
                $players->addEffect(Effect::getEffect(11)->setDuration(20*120)->setAmplifier(11)->setVisible(false));
                $players->addEffect(Effect::getEffect(3)->setDuration(20*1800)->setAmplifier(1)->setVisible(false));
                $players->addEffect(Effect::getEffect(16)->setDuration(20*1800)->setAmplifier(1)->setVisible(false));
                    $players->getInventory()->setItem(0,Item::get(Item::IRON_SWORD, 0, 1));
                    $players->getInventory()->setItem(1,Item::get(Item::IRON_PICKAXE, 0, 1));
                    $players->getInventory()->setItem(2,Item::get(Item::IRON_AXE, 0, 1));
                    $players->getInventory()->setItem(3,Item::get(Item::IRON_SHOVEL, 0, 1));
                    $players->getInventory()->setItem(4,Item::get(Item::IRON_HOE, 0, 1));
                    $players->getInventory()->setItem(7,Item::get(Item::GOLDEN_APPLE, 0, 2));
					$players->getInventory()->setItem(8,Item::get(Item::STEAK, 0, 16));
                }
            }
            $seconds--;
        }
            if($seconds < 0){
                $gt--;
                if($gt < 2400){
                    $None = "                                                             ";
                    $sps = 0;
                    if($hp > 0){
                        $hp--;
                    }
                        if($hp <= 0){
                    foreach($level->getPlayers() as $players){
                        if($players->getGamemode() == 0){
                            $b1 = $players->getInventory()->getHelmet()->getID();
                            $b2 = $players->getInventory()->getChestplate()->getID();
                            $b3 = $players->getInventory()->getLeggings()->getID();
                            $b4 = $players->getInventory()->getBoots()->getID();
                            if($b1 == 314){
                                $players->addEffect(Effect::getEffect(10)->setDuration(20*2)->setAmplifier(0)->setVisible(true));
                            }
                            if($b4 == 313){
                                $players->addEffect(Effect::getEffect(1)->setDuration(20*5)->setAmplifier(0)->setVisible(true));
                            }
                        }
                    }
                            $hp = 4;
                        }
                    foreach($level->getPlayers() as $players){
			if($players->getGamemode() == 0){
                $sps++;
            }
                }
                    foreach($level->getPlayers() as $players){
                        $playerx = intval($players->getX());
                        $playery = intval($players->getY());
                        $playerz = intval($players->getZ());
                $players->sendPopup($None . TextFormat::RED . "UHC" . TextFormat::GOLD . " 极限生存" . "\n" . $None . TextFormat::GREEN . "存活" . TextFormat::WHITE . " $sps" . "\n" . $None . TextFormat::GREEN . "剩余时间" . TextFormat::WHITE . " $gt" . " 秒" . "\n" . $None . TextFormat::AQUA . "坐标" . TextFormat::WHITE . ": X<$playerx> Y<$playery> Z<$playerz>");
                    }
                $i = 0;
				$level = $this->getServer()->getLevelByName("UHC");
		foreach($level->getPlayers() as $players){
			if($players->getGamemode() == 0){
				$i++;
			}
		}
                }
		$cp = $i;
                if($cp > 1){
					if($gt == 1200){
					foreach($this->getServer()->getLevelByName("UHC")->getPlayers() as $players){
					$players->sendMessage(TextFormat::DARK_GRAY . "[" . TextFormat::RED . "UHC" . TextFormat::DARK_GRAY . "]" . TextFormat::GOLD . " 时间还有20分钟,死斗在10分钟时开始");
					}
					}
					if($gt == 900){
					foreach($this->getServer()->getLevelByName("UHC")->getPlayers() as $players){
					$players->sendMessage(TextFormat::DARK_GRAY . "[" . TextFormat::RED . "UHC" . TextFormat::DARK_GRAY . "]" . TextFormat::GOLD . " 时间还有15分钟,死斗在10分钟时开始");
					}
					}
					if($gt <= 600){
						if($gt == 600){
					foreach($this->getServer()->getLevelByName("UHC")->getPlayers() as $players){
					$players->sendMessage(TextFormat::DARK_GRAY . "[" . TextFormat::RED . "UHC" . TextFormat::DARK_GRAY . "]" . TextFormat::GOLD . " 死斗开始");
					}
					foreach($this->getServer()->getLevelByName("UHC")->getPlayers() as $players){
					if($players->getGamemode() == 0){
                        $players->addEffect(Effect::getEffect(11)->setDuration(20*5)->setAmplifier(11)->setVisible(false));
						$xd = mt_rand(-32,32);
						$zd = mt_rand(-32,32);
						$yd = 125;
						$npos = new Vector3($xd,$yd,$zd);
						$players->teleport($npos);
                }
                    }
                        }
                        foreach($level->getPlayers() as $players){
                                                $px = intval($players->getX());
                            					$py = intval($players->getY());
                                                $pz = intval($players->getZ());
                        if($px >= -32 and $px <= 32 and $pz >= -32 and $pz <= 32){
		            $players->sendTip(TextFormat::DARK_GRAY . "[" . TextFormat::RED . "UHC" . TextFormat::DARK_GRAY . "]" . TextFormat::GREEN . " 你很安全!");
					}else{
                            $safe--;
                            if($safe <= 0){
						$players->setHealth($players->getHealth() - 1);
					$players->sendTip(TextFormat::DARK_GRAY . "[" . TextFormat::RED . "UHC" . TextFormat::DARK_GRAY . "]" . TextFormat::RED . " 你现在不在安全区!" . TextFormat::YELLOW . "\n尽快到达坐标 X:0 Z:0 附近");
                                $safe = 2;
                            }
					}
                        }
                    }
					if($gt == 0){
                    $config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
                    $slot = new Config($this->getDataFolder() . "/slot.yml", Config::YAML);
 					$config->set("Start", 0);
                    $slot->set("Player", 0);
                    $config->save();
                    $slot->save();
                    foreach($this->getServer->getLevelByName("UHC")->getPlayers() as $players){
					$players->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
					$seconds = 180;
                    $gt = 2400;
                    $hp = 1;
					$players->setMaxHealth(20);
					$players->setHealth(20);
					$players->setGamemode(2);
					$players->getInventory()->clearAll();
					$players->removeAllEffects();
                        $players->sendMessage(TextFormat::DARK_GRAY . "[" . TextFormat::RED . "UHC" . TextFormat::DARK_GRAY . "] " . TextFormat::YELLOW . "No Winner");
                    }
                  	$this->reload($this->getServer()->getLevelByName("UHC"));
                    }
					}
                if($gt <= 2400){
                if($cp <= 1){
                    foreach($this->getServer()->getLevelByName("UHC")->getPlayers() as $players){
					if($players->getGamemode() == 0){
                        $winner = $players->getName();
                    }
                    }
					foreach($level->getPlayers() as $players){
                            $players->sendMessage(TextFormat::DARK_GRAY . "[" . TextFormat::RED . "UHC" . TextFormat::DARK_GRAY . "] " . TextFormat::GREEN . $winner . " is Winner!!!");
							$players->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
							$players->getInventory()->clearAll();
							$players->setGamemode(2);
							$players->setMaxHealth(20);
							$players->setHealth(20);
							$players->removeAllEffects();
					}
					$this->reload($this->getServer()->getLevelByName("UHC"));
					$seconds = 180;
                        $gt = 2400;
                    $hp = 1;
                    $config = new Config($this->getDataFolder() . "/config.yml", Config::YAML);
                    $slot = new Config($this->getDataFolder() . "/slot.yml", Config::YAML);
					$config->set("Start", 0);
                    $slot->set("Player", 0);
                    $config->save();
                    $slot->save();
					}
                }
					}
    }
}