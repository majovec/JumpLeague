<?php

namespace jl\JumpLeague\Tasks;

use jl\JumpLeague\Game\ArenaManager;
use jl\JumpLeague\Main\JumpLeague;
use pocketmine\scheduler\PluginTask;

class JLCheckSignsTask extends PluginTask
{

    public function __construct(JumpLeague $owner)
    {
        parent::__construct($owner);
    }

    public function onRun($currentTick)
    {
        ArenaManager::checkSigns();
    }

}