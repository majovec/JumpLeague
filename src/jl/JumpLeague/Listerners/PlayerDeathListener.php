<?php

namespace jl\JumpLeague\Listeners;

use jl\JumpLeague\Game\ArenaManager;
use jl\JumpLeague\Language\Messages;
use jl\JumpLeague\Main\JumpLeague;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\level\sound\ExpPickupSound;
use pocketmine\Player;
use pocketmine\Server;

class PlayerDeathListener implements Listener
{

    public function onDeath(PlayerDeathEvent $event)
    {
        $player = $event->getEntity();
        $cause = $player->getLastDamageCause();
        $event->setDeathMessage("");

        if ($player instanceof Player) {
            if (ArenaManager::inArena($player)) {
                $event->setKeepInventory(true);
                $arena = ArenaManager::getArena($player);
                $arena->leben[$player->getName()] = ($arena->leben[$player->getName()] - 1);
                $leben = $arena->leben[$player->getName()];

                if ($cause instanceof EntityDamageByEntityEvent) {
                    $killer = $cause->getDamager();
                    if ($killer instanceof Player) {
                        $msg = str_replace("{player}", $player->getDisplayName(), Messages::get("jumpleague.game.killed_message"));
                        $msg = str_replace("{killer}", $killer->getDisplayName(), $msg);

                        $arena->sendMessageToAll($msg);
                    }
                } else {
                    if (JumpLeague::$lasthit[$player->getName()] != "") {
                        $killername = JumpLeague::$lasthit[$player->getName()];
                        $killer = Server::getInstance()->getPlayerExact($killername);
                        if ($killer != null) {
                            if (ArenaManager::inArena($killer)) {
                                $arena = ArenaManager::getArena($killer);
                                if ($arena->arenaid == ArenaManager::getArena($player)->arenaid) {
                                    $msg = str_replace("{player}", $player->getDisplayName(), Messages::get("jumpleague.game.killed_message"));
                                    $msg = str_replace("{killer}", $killer->getDisplayName(), $msg);

                                    $arena->sendMessageToAll($msg);
                                    $killer->getLevel()->addSound(new ExpPickupSound($killer), [$killer]);
                                } else {
                                    //killer ist nicht in der selben arena
                                    $arena->sendMessageToAll(str_replace("{player}", $player->getDisplayName(), Messages::get("jumpleague.game.die_message")));
                                }
                            }
                        } else {
                            //killer offline
                            $arena->sendMessageToAll(str_replace("{player}", $player->getDisplayName(), Messages::get("jumpleague.game.die_message")));
                        }
                    } else {
                        //kein killer
                        $arena->sendMessageToAll(str_replace("{player}", $player->getDisplayName(), Messages::get("jumpleague.game.die_message")));
                    }
                }

                if($leben <= 0){
                    $arena->sendMessageToAll(str_replace("{alive}", count($arena->players) - 1, Messages::get("jumpleague.game.playersleft")));
                    $arena->removePlayer($player);
                    $arena->addSpectator($player);
                }
            } else {
                $event->setKeepInventory(false);
            }
        }
    }

}