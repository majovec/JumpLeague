<?php

namespace jl\JumpLeague\Listeners;

use jl\JumpLeague\Main\JumpLeague;
use jl\JumpLeague\Tasks\JLCheckSignsTask;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Server;

class PlayerJoinListener implements Listener
{
    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        Server::getInstance()->getScheduler()->scheduleDelayedTask(new JLCheckSignsTask(JumpLeague::getInstance()), 30);
    }
}