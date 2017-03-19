<?php

namespace jl\JumpLeague\Listeners;

use jl\JumpLeague\Game\ArenaManager;
use jl\JumpLeague\Language\Messages;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\Server;

class PlayerCommandPreprocessListener implements Listener
{

    public function onCmd(PlayerCommandPreprocessEvent $event)
    {
        $player = $event->getPlayer();
        $msg = $event->getMessage();

        $args = explode(" ", $msg);
        $command = array_shift($args);

        if (strtolower($command) == "/start") {
            $event->setCancelled(true);
            if ($player->hasPermission("jl.start")) {
                if (ArenaManager::inArena($player)) {
                    $arena = ArenaManager::getArena($player);
                    if ($arena->gamestate == 1) {
                        if ($arena->lobbytimer >= 12) {
                            $arena->lobbytimer = 11;
                            $player->sendMessage(Messages::get("jumpleague.game.forcestart"));
                        }
                    }
                }
            }
        }
        if (strtolower($command) == "/hub") {
            if (ArenaManager::inArena($player)) {
                $arena = ArenaManager::getArena($player);
                $arena->removePlayer($player);
                $arena->removeSpectator($player);
                $player->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
            }
        }
    }

}