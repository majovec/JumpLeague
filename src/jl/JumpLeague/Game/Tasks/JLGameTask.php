<?php

namespace jl\JumpLeague\Game\Tasks;

use jl\JumpLeague\Game\Arena;
use jl\JumpLeague\Main\JumpLeague;
use pocketmine\scheduler\PluginTask;

class JLGameTask extends PluginTask
{

    public $arena = null;

    public function __construct(JumpLeague $owner, Arena $arena)
    {
        parent::__construct($owner);
        $this->arena = $arena;
    }

    public function onRun($currentTick)
    {
        $arena = $this->arena;

        if ($arena->gamestate == 1) {
            $arena->lobby();
        } elseif ($arena->gamestate == 2) {
            $arena->jr();
        } elseif ($arena->gamestate == 3) {
            $arena->deathmatch();
        } elseif ($arena->gamestate == 4) {
            $arena->end();
        }
    }

}