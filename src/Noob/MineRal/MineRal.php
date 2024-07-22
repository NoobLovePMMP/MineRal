<?php

namespace Noob\MineRal;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Server;
use Noob\MineRal\commands\MineRalCommand;
use Noob\MineRal\listener\EventListener;

class MineRal extends PluginBase {

    public $storage;
    public $manage;
    public $sell;
	public static $instance;


	public static function getInstance() : self {
		return self::$instance;
	}

	public function onEnable(): void{
        self::$instance = $this;
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getServer()->getCommandMap()->register("/mineral", new MineRalCommand($this));
        $this->storage = new Config($this->getDataFolder() . "storage.yml", Config::YAML);
        $this->sell = new Config($this->getDataFolder() . "sell.yml", Config::YAML, [
            "cobblestone" => 1,
            "coal" => 2,
            "raw_iron" => 3,
            "raw_gold" => 4,
            "diamond" => 5,
            "emerald" => 6
        ]);
        $this->manage = new Config($this->getDataFolder() . "manage.yml", Config::YAML, [
            "worlds" => [],
            "max-level" => 100,
            "price-to-upgrade" => 20000
        ]);
	}

    public function getStorage(){
        return $this->storage;
    }

    public function getManager(){
        return $this->manage;
    }

    public function getSellPrice(){
        return $this->sell;
    }

    public function getAmountStorage(Player $player): int{
        $ore = ["cobblestone", "coal", "raw_iron", "raw_gold", "diamond", "emerald"];
        $total = 0;
        for($i = 0; $i < count($ore); $i++){
            $total += $this->getStorage()->getNested($player->getName(). "." . $ore[$i]);
        }
        return $total;
    }

    public function tagToData(Player $player, string $tag){
        $ore = ["cobblestone", "coal", "raw_iron", "raw_gold", "diamond", "emerald"];
        $tags = [
            "{name}" => $player->getName(),
            "{level}" => $this->getStorage()->getNested($player->getName().".Level"),
            "{next_level}" => $this->getStorage()->getNested($player->getName().".Level") + 1,
            "{price}" => $this->getManager()->get("price-to-upgrade"),
            "{storage}" => $this->getAmountStorage($player),
            "{cobblestone}" => $this->getStorage()->getNested($player->getName(). "." . $ore[0]),
            "{coal}" => $this->getStorage()->getNested($player->getName(). "." . $ore[1]),
            "{iron}" => $this->getStorage()->getNested($player->getName(). "." . $ore[2]),
            "{gold}" => $this->getStorage()->getNested($player->getName(). "." . $ore[3]),
            "{diamond}" => $this->getStorage()->getNested($player->getName(). "." . $ore[4]),
            "{emerald}" => $this->getStorage()->getNested($player->getName(). "." . $ore[5]),
        ];
        foreach($tags as $data => $value){
            $tag = str_replace($data, $value, $tag);
        }
        return $tag;
    }

    public function arrayToString(array $arr): string{
        $str = "";
        foreach($arr as $data){
            $str .= $data;
        }
        return $str;
    }

    public function addToMineral(Player $player, string $type, int $count){
        $this->getStorage()->setNested($player->getName(). "." .$type, $this->getStorage()->getNested($player->getName(). "." .$type) + $count);
        $this->getStorage()->save();
    }

    public function takeToInventory(Player $player, string $type, int $count){
        $this->getStorage()->setNested($player->getName(). "." .$type, $this->getStorage()->getNested($player->getName(). "." .$type) - $count);
        $this->getStorage()->save();
    }

    public function getStatusPickup(Player $player): string{
        if($this->getStorage()->getNested($player->getName(). ".Auto-Pickup") == "Yes"){
            return "§aĐang Bật";
        }
        else{
            return "§cĐã Tắt";
        }
    }

    public function getStatusSell(Player $player): string{
        if($this->getStorage()->getNested($player->getName(). ".Auto-Sell") == "Yes"){
            return "§aĐang Bật";
        }
        else{
            return "§cĐã Tắt";
        }
    }

    public function setPickup(Player $player, bool $status){
        if($status == true){
            $this->getStorage()->setNested($player->getName(). ".Auto-Pickup", "Yes");
            $this->getStorage()->save();
        }
        else{
            $this->getStorage()->setNested($player->getName(). ".Auto-Pickup", "No");
            $this->getStorage()->save();
        }
    }

    public function setSell(Player $player, bool $status){
        if($status == true){
            $this->getStorage()->setNested($player->getName(). ".Auto-Sell", "Yes");
            $this->getStorage()->save();
        }
        else{
            $this->getStorage()->setNested($player->getName(). ".Auto-Sell", "No");
            $this->getStorage()->save();
        }
    }

    public function addLevel(Player $player, int $amount){
        $this->getStorage()->setNested($player->getName(). ".Level", $this->getStorage()->getNested($player->getName(). ".Level") + $amount);
        $this->getStorage()->save();
    }
}