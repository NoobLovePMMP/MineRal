<?php

namespace Noob\MineRal\forms;

use pocketmine\{Server, player\Player};
use Noob\MineRal\MineRal;
use Noob\MineRal\libs\LootSpace369\FormAPI\CustomForm;
use Noob\MineRal\libs\LootSpace369\FormAPI\SimpleForm;
use Noob\MineRal\libs\LootSpace369\FormAPI\ModalForm;
use pocketmine\item\StringToItemParser;
use pocketmine\math\Vector3;
use Noob\CoinAPI;

class MineRalForm {

	public string $prefix = "[MineRal] ";
	public $contentMenu = [
		"§eCấp Độ:§f {level}\n",
		"§eÔ Chứa:§f {storage}/{level}00\n\n",
		"§aĐá Cuội:§f {cobblestone}\n",
		"§aThan:§f {coal}\n",
		"§aSắt Thô:§f {iron}\n",
		"§aVàng Thô:§f {gold}\n",
		"§aKim Cương:§f {diamond}\n",
		"§aLục Bảo:§f {emerald}"
	];
	

    public function openMenu(Player $player){
		$content = MineRal::getInstance()->tagToData($player, MineRal::getInstance()->arrayToString($this->contentMenu));
		$form = new SimpleForm("§6Kho Của §e". $player->getName(), $content, function(Player $player, $data){
			if($data === null){
				return true;
			}
			switch($data){
				case 0:
					break;
				case 1:
					$this->addOrRemove($player);
					break;
				case 2:
					$this->sellMenu($player);
					break;
				case 3:
					$this->settingMenu($player);
					break;
				case 4:
					$this->upgradeMenu($player);
					break;
			}
		});
		$form->addButton("§cĐóng", "https://cdn-icons-png.flaticon.com/128/1828/1828665.png");
		$form->addButton("§l§a● §fThêm/Rút Khoáng Sản §a●", "https://cdn-icons-png.flaticon.com/128/1907/1907675.png");
		$form->addButton("§l§a● §fBán Khoáng Sản §a●", "https://cdn-icons-png.flaticon.com/128/4106/4106426.png");
		$form->addButton("§l§a● §fCài Đặt §a●", "https://cdn-icons-png.flaticon.com/128/738/738853.png");
		$form->addButton("§l§a● §fNâng Cấp Kho §a●", "https://cdn-icons-png.flaticon.com/128/8078/8078564.png");
		$player->sendForm($form);
    }

	public function addOrRemove(Player $player){
		$form = new CustomForm("§6Kho Của §e". $player->getName(), function(Player $player, $data){
			if($data === null){
				$this->openMenu($player);
				return true;
			}
			switch($data[0]){
				case 0:
					$this->addAllOre($player);
					break;
				case 1:
					$this->addOreInHand($player);
					break;
				case 2:
					$this->removeAllOre($player);
					break;
				case 3:
					$this->removeOre($player);
					break;
			}
		});
		$form->addDropdown("§l§a● §fChọn Điều Bạn Muốn:", ["Thêm Tất Cả Khoáng Sản", "Thêm Khoáng Sản Trên Tay", "Rút Tất Cả Khoáng Sản", "Rút Khoảng Sản Theo Loại"]);
		$player->sendForm($form);
	}

	public function addAllOre(Player $player){
		$ore = ["cobblestone", "coal", "raw_iron", "raw_gold", "diamond", "emerald"];
		$items = $player->getInventory()->getContents();
		foreach($items as $item){
			if(!$item->isNull()){
				$itemName = strtolower($item->getName());
				for($vt = 0; $vt < strlen($itemName); $vt++){
					if($itemName[$vt] == ' '){
						$itemName[$vt] = '_';
					}
				}
				if(in_array($itemName, $ore)){
					MineRal::getInstance()->addToMineRal($player, $itemName, $item->getCount());
					$player->getInventory()->remove($item);
				}
			}
		}
		$player->sendMessage("§l§a● §fĐã Thêm Khoáng Sản Vào Kho !");
	}

	public function addOreInHand(Player $player){
		$ore = ["cobblestone", "coal", "raw_iron", "raw_gold", "diamond", "emerald"];
		$itemHand = $player->getInventory()->getItemInHand();
		$itemName = strtolower($itemHand->getName());
		for($vt = 0; $vt < strlen($itemName); $vt++){
			if($itemName[$vt] == ' '){
				$itemName[$vt] = '_';
			}
		}
		if(in_array($itemName, $ore)){
			MineRal::getInstance()->addToMineRal($player, $itemName, $itemHand->getCount());
			$player->getInventory()->remove($itemHand);
			$player->sendMessage("§l§a● §fĐã Thêm Khoáng Sản Vào Kho !");
		}
	}

	public function removeAllOre(Player $player){
		$ore = ["cobblestone", "coal", "raw_iron", "raw_gold", "diamond", "emerald"];
		for($i = 0; $i < count($ore); $i++){
			$count = MineRal::getInstance()->getStorage()->getNested($player->getName(). "." . $ore[$i]);
			if($count > 0){
				$item = StringToItemParser::getInstance()->parse($ore[$i])->setCount($count);
				if($player->getInventory()->canAddItem($item)){
					$player->getInventory()->addItem($item);
				}
				else{
					$player->getPosition()->getWorld()->dropItem(new Vector3($player->getPosition()->getX(), $player->getPosition()->getY(), $player->getPosition()->getZ()), $item);
				}
				MineRal::getInstance()->takeToInventory($player, $ore[$i], (int)$count);
			}
		}
		$player->sendMessage("§l§a● §fĐã Rút Khoáng Sản Khỏi Kho !");
	}

	public function removeOre(Player $player){
		$form = new CustomForm("§6Kho Của §e". $player->getName(), function(Player $player, $data){
			if($data === null){
				$this->openMenu($player);
				return true;
			}
			if(!is_numeric($data[1]) || $data[1] < 0){
				$player->sendMessage("§l§a● §fVui Lòng Nhập Số Khoáng Sản Muốn Rút !");
				return true;
			}
			$ore = ["cobblestone", "coal", "raw_iron", "raw_gold", "diamond", "emerald"];
			$oreType = $ore[$data[2]];
			if($data[1] <= MineRal::getInstance()->getStorage()->getNested($player->getName(). "." . $oreType)){
				$count = (int)$data[1];
				$item = StringToItemParser::getInstance()->parse($oreType)->setCount($count);
				if($player->getInventory()->canAddItem($item)){
					$player->getInventory()->addItem($item);
				}
				else{
					$player->getPosition()->getWorld()->dropItem(new Vector3($player->getPosition()->getX(), $player->getPosition()->getY(), $player->getPosition()->getZ()), $item);
				}
				MineRal::getInstance()->takeToInventory($player, $oreType, (int)$count);
				$player->sendMessage("§l§a● §fĐã Rút Khoáng Sản Khỏi Kho !");
			}
			else{
				$player->sendMessage("§l§a● §fBạn Không Đủ Khoáng Sản !");
			}
		});
		$content = MineRal::getInstance()->tagToData($player, MineRal::getInstance()->arrayToString($this->contentMenu));
		$form->addLabel($content);
		$form->addInput("§l§a● §fNhập Số Lượng Muốn Rút:", "10");
		$form->addDropdown("§l§a● §fChọn Loại Khoáng Sản:", ["Đá Cuội", "Than", "Sắt Thô", "Vàng Thô", "Kim Cương", "Lục Bảo"]);
		$player->sendForm($form);
	}

	public function sellMenu(Player $player){
		$form = new CustomForm("§6Kho Của §e". $player->getName(), function(Player $player, $data){
			if($data === null){
				$this->openMenu($player);
				return true;
			}
			switch($data[0]){
				case 0:
					$this->sellAll($player);
					break;
				case 1:
					$this->sellOre($player);
					break;
			}
		});
		$form->addDropdown("§l§a● §fChọn Điều Bạn Muốn:", ["Bán Tất Cả Khoán Sản", "Bán Khoáng Sản Theo Loại"]);
		$player->sendForm($form);
	}

	public function sellAll(Player $player){
		$ore = ["cobblestone", "coal", "raw_iron", "raw_gold", "diamond", "emerald"];
		$total = 0;
		for($i = 0; $i < count($ore); $i++){
			$count = MineRal::getInstance()->getStorage()->getNested($player->getName(). "." . $ore[$i]);
			if($count > 0){
				$price = MineRal::getInstance()->getSellPrice()->get($ore[$i]);
				CoinAPI::getInstance()->addCoin($player, $price*$count);
				MineRal::getInstance()->takeToInventory($player, $ore[$i], (int)$count);
				$total += ($price*$count);
			}
		}
		$player->sendMessage("§l§a● §fBạn Đã Nhận Được ". $total ." Coin");
	}

	public function sellOre(Player $player){
		$form = new CustomForm("§6Kho Của §e". $player->getName(), function(Player $player, $data){
			if($data === null){
				$this->openMenu($player);
				return true;
			}
			if(!is_numeric($data[1]) || $data[1] < 0){
				$player->sendMessage("§l§a● §fVui Lòng Nhập Số Khoáng Sản Muốn Rút !");
				return true;
			}
			$ore = ["cobblestone", "coal", "raw_iron", "raw_gold", "diamond", "emerald"];
			$oreType = $ore[$data[2]];
			if($data[1] <= MineRal::getInstance()->getStorage()->getNested($player->getName(). "." . $oreType)){
				$count = (int)$data[1];
				$price = MineRal::getInstance()->getSellPrice()->get($oreType);
				CoinAPI::getInstance()->addCoin($player, $price*$count);
				MineRal::getInstance()->takeToInventory($player, $oreType, (int)$count);
				$player->sendMessage("§l§a● §fBạn Đã Nhận Được ". $price*$count . " Coin");
			}
			else{
				$player->sendMessage("§l§a● §fBạn Không Đủ Khoáng Sản !");
			}
		});
		$content = MineRal::getInstance()->tagToData($player, MineRal::getInstance()->arrayToString($this->contentMenu));
		$form->addLabel($content);
		$form->addInput("§l§a● §fNhập Số Lượng Muốn Bán:", "10");
		$form->addDropdown("§l§a● §fChọn Loại Khoáng Sản:", ["Đá Cuội", "Than", "Sắt Thô", "Vàng Thô", "Kim Cương", "Lục Bảo"]);
		$player->sendForm($form);
	}

	public function settingMenu(Player $player){
		$pickup = MineRal::getInstance()->getStorage()->getNested($player->getName(). ".Auto-Pickup");
		$autosell = MineRal::getInstance()->getStorage()->getNested($player->getName(). ".Auto-Sell");
		$content = MineRal::getInstance()->tagToData($player, MineRal::getInstance()->arrayToString($this->contentMenu));
		$form = new SimpleForm("§6Kho Của §e". $player->getName(), $content, function(Player $player, $data) use ($pickup, $autosell){
			if($data === null){
				$this->openMenu($player);
				return true;
			}
			switch($data){
				case 0:
					break;
				case 1:
					$msg = "";
					if($pickup == "Yes"){
						MineRal::getInstance()->setPickup($player, false);
						$msg = "§l§a● §fBạn Đã Tắt Tự Động Vào Kho !";
					}
					else{
						MineRal::getInstance()->setPickup($player, true);
						$msg = "§l§a● §fBạn Đã Bật Tự Động Vào Kho !";
					}
					$player->sendMessage($msg);
					break;
				case 2:
					$msg = "";
					if($autosell == "Yes"){
						MineRal::getInstance()->setSell($player, false);
						$msg = "§l§a● §fBạn Đã Tắt Tự Động Bán Khoáng Sản";
					}
					else{
						MineRal::getInstance()->setSell($player, true);
						$msg = "§l§a● §fBạn Đã Bật Tự Động Bán Khoáng Sản";
					}
					$player->sendMessage($msg);
					break;
			}
		});
		$status_pickup = MineRal::getInstance()->getStatusPickup($player); 
		$status_sell = MineRal::getInstance()->getStatusSell($player); 
		$content = MineRal::getInstance()->tagToData($player, MineRal::getInstance()->arrayToString($this->contentMenu));
		$form->addButton("§cĐóng", "https://cdn-icons-png.flaticon.com/128/1828/1828665.png");
		$form->addButton("§l§a● §fTự Động Vào Kho §a●\n§6Trạng Thái: ". $status_pickup, "https://cdn-icons-png.flaticon.com/128/10103/10103182.png");
		$form->addButton("§l§a● §fBán Khoáng Sản §a●\n§6Trạng Thái: ". $status_sell, "https://cdn-icons-png.flaticon.com/128/4106/4106426.png");
			$player->sendForm($form);
    }

	public function upgradeMenu(Player $player){
		$content = MineRal::getInstance()->tagToData($player, "§l§a● §fBạn Có Muốn Sử Dụng {price} Coin để nâng kho lên cấp {next_level} ?");
		$form = new ModalForm("§6Kho Của §e". $player->getName(), $content, "§aNâng Cấp", "§cKhông Phải Bây Giờ", function(Player $player, $data){
			if($data === null){
				$this->openMenu($player);
				return true;
			}
			if($data == true){
				$price = MineRal::getInstance()->getManager()->get("price-to-upgrade");
				if(CoinAPI::getInstance()->myCoin($player) >= $price){
					CoinAPI::getInstance()->reduceCoin($player, $price);
					MineRal::getInstance()->addLevel($player, 1);
					$this->upgradeMenu($player);
					$msg = MineRal::getInstance()->tagToData($player, "§l§a● §fKho Của Bạn Đã Lên Cấp {level} ?");
					$player->sendMessage($msg);
				}
				else{
					$player->sendMessage("§l§a● §fBạn Không Đủ Coin Để Nâng !");
				}
			}
		});
		$player->sendForm($form);
    }
}