<?php

namespace jl\JumpLeague\Listeners;

use jl\JumpLeague\Game\ArenaManager;
use jl\JumpLeague\Language\Messages;
use jl\JumpLeague\Main\JumpLeague;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;

class EntityDamageListener implements Listener
{

    public function onDamage(EntityDamageEvent $event)
    {
        $player = $event->getEntity();
        $cause = $player->getLastDamageCause();

        if($player instanceof Player) {
            if (ArenaManager::inArena($player)) {
                $arena = ArenaManager::getArena($player);
                if ($arena->gamestate <= 2) {
                    $event->setCancelled();
                }
                if ($arena->gamestate == 3) {
                    if ($event->getDamage() >= $player->getHealth()) {

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

                        if ($arena->status != "offline") {
                            if ($arena->leben[$player->getName()] > 0) {
                                $player->teleport($arena->dmspawns[array_rand($arena->dmspawns)]);
                                $player->setHealth(20);
                            } else {
                                JumpLeague::createChest($player);
                                $player->teleport($arena->spectatorspawn);
                                $player->setHealth(20);
                                $arena->sendMessageToAll(str_replace("{alive}", count($arena->players) - 1, Messages::get("jumpleague.game.playersleft")));
                                $arena->removePlayer($player);
                                $arena->addSpectator($player);
                            }
                        } else {
                            $player->teleport(Server::getInstance()->getDefaultLevel()->getSpawnLocation());
                            Server::getInstance()->dispatchCommand($player, "hub");
                        }
                    } else {
                        if ($cause instanceof EntityDamageByEntityEvent) {
                            $killer = $cause->getDamager();
                            if ($killer instanceof Player) {
                                JumpLeague::$lasthit[$player->getName()] = $killer->getName();
                            }
                        }
                    }
                }
            }
        }
    }

}