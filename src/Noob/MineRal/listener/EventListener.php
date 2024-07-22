<?php

namespace Noob\MineRal\listener;

use Noob\MineRal\forms\MineRalForm;
use pocketmine\block\BlockTypeIds;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\Position;
use Noob\MineRal\MineRal;

class EventListener implements Listener{

    public $ore = [
        "stone", "cobblestone", "coal_ore", "iron_ore", "gold_ore", "diamond_ore", "emerald_ore"
    ];
    public $ore2 = [
        "cobblestone", "coal", "raw_iron", "raw_gold", "diamond", "emerald"
    ];

    public function onJoin(PlayerJoinEvent $ev){
        $player = $ev->getPlayer();
        if(!MineRal::getInstance()->getStorage()->exists($player->getName())){
            MineRal::getInstance()->getStorage()->set($player->getName(), [
                "Level" => 1,
                "Auto-Pickup" => "Yes",
                "Auto-Sell" => "No",
                "cobblestone" => 0,
                "coal" => 0,
                "raw_iron" => 0,
                "raw_gold" => 0,
                "diamond" => 0,
                "emerald" => 0
            ]);
            MineRal::getInstance()->getStorage()->save();
        }
    }

    public function inWorldCanBypass(Player $player, string $worldName): bool{
        if(MineRal::getInstance()->getManager()->get("worlds") === []) return false;
        foreach(MineRal::getInstance()->getManager()->get("worlds") as $data => $value){
            if(MineRal::getInstance()->tagToData($player, $value) == $worldName) return true;
        }
        return false;
    }

    public function onBreak(BlockBreakEvent $ev){
        $player = $ev->getPlayer();
            $worldName = $player->getWorld()->getFolderName();
            if($this->inWorldCanBypass($player, $worldName)){
                $blockName = strtolower($ev->getBlock()->getName());
                for($i = 0; $i < strlen($blockName); $i++){
                    if($blockName[$i] == ' ') $blockName[$i] = '_';
                }
                if(in_array($blockName, $this->ore)){
                    if(MineRal::getInstance()->getStatusPickup($player) == "§aĐang Bật"){
                        $drops = $ev->getDrops();
                        $ev->setDrops([]);
                        foreach($drops as $drop){
                            $itemName = strtolower($drop->getName());
                            for($i = 0; $i < strlen($itemName); $i++){
                                if($itemName[$i] == ' ') $itemName[$i] = '_';
                            }
                            if(in_array($itemName, $this->ore2)){
                                $count = $drop->getCount();
                                if(MineRal::getInstance()->getAmountStorage($player) + $count <= MineRal::getInstance()->getStorage()->getNested($player->getName(). ".Level")*100){
                                    MineRal::getInstance()->addToMineral($player, $itemName, $count);
                                    $player->sendPopup("§l§a● §fĐã Thêm ". $count . " ". $drop->getName(). " Vào Kho");
                                }
                                else{
                                    if(MineRal::getInstance()->getStatusSell($player) == "§aĐang Bật"){
                                        $form = new MineRalForm;
                                        $form->sellAll($player);

                                    }
                                    else{
                                        $player->sendMessage("§l§a● §fKho Của Bạn Đã Đầy !");
                                        $ev->cancel();
                                    }
                                }
                            }
                        }
                    }
                }
            }
    }
}