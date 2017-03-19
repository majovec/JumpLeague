<?php

namespace jl\JumpLeague\Listeners;

use jl\JumpLeague\Game\ArenaManager;
use jl\JumpLeague\Language\Messages;
use jl\JumpLeague\Main\JumpLeague;
use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\Item;

class PlayerMoveListener implements Listener
{

    public function onMove(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();

        if (ArenaManager::inArena($player)) {
            $arena = ArenaManager::getArena($player);
            if(in_array($player, $arena->freeze)){
                if($event->getFrom()->getX() != $event->getTo()->getX()){
                    $event->setCancelled();
                }
                if($event->getFrom()->getY() != $event->getTo()->getY()){
                    $event->setCancelled();
                }
                if($event->getFrom()->getZ() != $event->getTo()->getZ()){
                    $event->setCancelled();
                }
            }

            if($arena->gamestate == 2){
                $block = $player->getLevel()->getBlock($player->getPosition()->subtract(0, 1, 0));
                if($block->getId() == Block::GOLD_BLOCK){
                    //checkpoint
                    $arena->checkpoints[$player->getName()] = $block->add(0, 1, 0);
                    $player->sendPopup(Messages::get("jumpleague.game.checkpoint.popup"));
                }
                if($block->getId() == Block::DIAMOND_BLOCK){
                    //ziel
                    $arena->checkpoints[$player->getName()] = $block->add(0, 1, 0);
                    if($arena->jrwinner == "") {
                        $player->sendPopup(Messages::get("jumpleague.game.goal.message.self"));
                        $arena->sendMessageToAll(str_replace("{player}", $player->getName(), Messages::get("jumpleague.game.goal.message.all")));
                        $arena->jrtimer = 11;
                        $arena->jrwinner = $player->getName();
                        $player->getInventory()->addItem(Item::get(Item::DIAMOND_BOOTS));
                    }
                }
                if($player->getY() <= 40){
                    if(!in_array($player->getName(), JumpLeague::$inChest)){
                        $player->teleport($arena->checkpoints[$player->getName()]);
                    }
                }
            }
        }
    }

}