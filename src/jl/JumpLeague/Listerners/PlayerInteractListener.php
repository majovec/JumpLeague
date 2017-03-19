<?php

namespace jl\JumpLeague\Listeners;

use jl\JumpLeague\Game\ArenaManager;
use jl\JumpLeague\Language\Messages;
use jl\JumpLeague\Main\JumpLeague;
use jl\JumpLeague\Tasks\JLCheckSignsTask;
use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\Server;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;

class PlayerInteractListener implements Listener
{

    public function onInteract(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $tile = $player->getLevel()->getTile($block);

        //EnderGames::openChest($player);

        if (JumpLeague::$registerSign == true) {
            if (JumpLeague::$registerSignWho == $player->getName()) {
                if ($tile instanceof Sign) {
                    $sign = $tile;
                    $sign->setText("- EnderGames -", "§aArena wird", "§aGesucht...", "- EnderGames -");
                    Server::getInstance()->getScheduler()->scheduleDelayedTask(new JLCheckSignsTask(JumpLeague::getInstance()), 5);

                    JumpLeague::$registerSign = false;
                    JumpLeague::$registerSignWho = "";
                }
            }
        } elseif ($tile instanceof Sign) {
            $sign = $tile;
            $text = $sign->getText();

            // strpos($string, $suchendeswort);
            $found = strpos($text[0], "- EnderGames");

            if ($found !== false) {
                $found2 = strpos($text[0], "- EnderGames -");
                if ($found2 === false) {
                    $arenaid = explode(" ", $text[0])[2];
                    $arena = ArenaManager::getArenaByID($arenaid);


                    if (count($arena->players) >= $arena->maxplayers) {
                        if ($player->hasPermission("jl.premium")) {
                            $randomplayer = array_rand($arena->players);
                            Server::getInstance()->dispatchCommand($randomplayer, "hub");
                            $randomplayer->sendMessage(Messages::get("jumpleague.game.full.kicked.message"));
                            $arena->addPlayer($player);
                        } else {
                            $player->sendMessage(Messages::get("jumpleague.game.full.message"));
                        }
                    } else {
                        $arena->addPlayer($player);
                    }
                }
            }

        }
        if(ArenaManager::inArena($player)){
            $arena = ArenaManager::getArena($player);
            if($block->getId() == Block::CHEST && $arena->gamestate == 2){
                $event->setCancelled();
                //EnderGames::openChest($player);

                $index = $block->getFloorX() . ":" . $block->getFloorY() . ":" . $block->getFloorZ();

                if(!isset($arena->chests[$index])){
                    $arena->chests[$index] = [];
                }

                if(!in_array($player->getName(), $arena->chests[$index])) {

                    $arena->chests[$index][] = $player->getName();

                    $randomitems = [];

                    $config = new Config(JumpLeague::getInstance()->getDataFolder() . "config.yml", Config::YAML);
                    for ($i = 0; $i < mt_rand(3, 5); $i++) {
                        $k = array_rand($config->get("chestitems"));
                        $v = $config->get("chestitems")[$k];
                        $randomitem = Item::get($v[0], $v[1], $v[2]);
                        $randomitems[] = $randomitem;
                    }
                    foreach($randomitems as $item){
                        $player->getInventory()->addItem($item);
                    }
                }
            }
        }
    }

}