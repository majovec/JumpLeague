<?php

namespace jl\JumpLeague\Listeners;

use jl\JumpLeague\Game\ArenaManager;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\Server;

class PlayerRespawnListener implements Listener
{

    public function onRespawn(PlayerRespawnEvent $event)
    {
        $player = $event->getPlayer();

        if (ArenaManager::inArena($player)) {
            $arena = ArenaManager::getArena($player);

            if ($arena->status != "offline") {
                if($arena->leben[$player->getName()] > 0){
                    $event->setRespawnPosition($arena->dmspawns[array_rand($arena->dmspawns)]);
                } else {
                    $event->setRespawnPosition($arena->spectatorspawn);
                }
            } else {
                $event->setRespawnPosition(Server::getInstance()->getDefaultLevel()->getSpawnLocation());
                Server::getInstance()->dispatchCommand($player, "hub");
            }
        }
    }

}