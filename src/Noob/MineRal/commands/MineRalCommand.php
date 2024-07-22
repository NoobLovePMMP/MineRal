<?php

namespace Noob\MineRal\commands;

use pocketmine\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use Noob\MineRal\MineRal;
use Noob\MineRal\forms\MineRalForm;
use pocketmine\Server;

class MineRalCommand extends Command implements PluginOwned
{
    private MineRal $plugin;
    public string $prefix = "[MineRal] ";

    public function __construct(MineRal $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("mineral","Open MineRal Menu", null, []);
        $this->setPermission("mineral.cmd");
    }

    public function execute(CommandSender $player, string $label, array $args)
    {
        if (!$player instanceof Player) {
            $this->getOwningPlugin()->getLogger()->notice("Xin hãy sử dụng lệnh trong trò chơi");
            return 1;
        }
        $form = new MineRalForm;
        $form->openMenu($player);
    }

    public function getOwningPlugin(): MineRal
    {
        return $this->plugin;
    }
}