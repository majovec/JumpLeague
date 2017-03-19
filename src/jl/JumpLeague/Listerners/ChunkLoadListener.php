<?php

namespace jl\JumpLeague\Listeners;

use jl\JumpLeague\Main\JumpLeague;
use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\Listener;
use pocketmine\tile\Chest;
use pocketmine\tile\Sign;

class ChunkLoadListener implements Listener
{

    public function onChunkLoad(ChunkLoadEvent $event)
    {
        $chunk = $event->getChunk();
        $level = $event->getLevel();

        if (in_array($level, JumpLeague::$levels)) {
            foreach ($chunk->getTiles() as $tile) {
                if ($tile instanceof Chest) {
                    JumpLeague::fillChest($tile);
                }
            }
        }

        foreach ($chunk->getTiles() as $tile) {
            if ($tile instanceof Sign) {
                $found = strpos($tile->getText()[0], "- EnderGames");

                if ($found !== false) {
                    $tile->setText("- EnderGames -", "§aArena wird", "§aGesucht...", "- EnderGames -");
                }
            }
        }
    }

}