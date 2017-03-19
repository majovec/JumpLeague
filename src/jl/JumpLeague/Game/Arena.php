<?php

namespace jl\JumpLeague\Game;

use jl\JumpLeague\Game\Tasks\JLGameTask;
use jl\JumpLeague\Language\Messages;
use jl\JumpLeague\Main\JumpLeague;
use jl\XLobby\Main\XLobby;
use pocketmine\entity\Attribute;
use pocketmine\entity\AttributeMap;
use pocketmine\item\Item;
use pocketmine\level\Location;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

class Arena
{

    public $arenaname = "";

    public $arenamanager = null;

    public $status = "offline";
    public $lobbytimer = 60;
    public $endtimer = 15;
    public $waitingtimer = 5;
    public $jrtimer = 10*60;
    //public $dmtimer = 10*60;

    public $minplayers = 1;
    public $maxplayers = 0;

    public $arenamap = "";
    public $lobbymap = "";
    public $dmmap = "";

    public $jrwinner = "";
    public $spectatorspawn = null;

    public $players = [];
    public $spectators = [];

    public $config = null;
    public $spawns = [];
    public $dmspawns = [];

    public $checkpoints = [];

    public $chests = [];

    public $freeze = [];

    public $leben = [];

    /*
     * Loading = 0
     * Lobby = 1
     * Ingame = 2
     * DeathMatch = 3
     * End = 4
     */

    public $gamestate = 0;

    public $arenaid = 0;

    public $startpos = [];

    public $gametaskid;

    public function __construct($arenaid = 0, Config $config)
    {
        $this->arenaid = $arenaid;
        $this->config = $config;

        $lobbymap = $config->getNested("Lobby.Welt");
        $arenamap = $config->getNested("Spawns.1.Welt");
        $dmmap = $config->getNested("DMSpawns.1.Welt");

        $lobbyfolder = "JumpLeagueLobby" . $arenaid;
        $arenafolder = "JumpLeagueArena" . $arenaid;
        $dmfolder = "JumpLeagueDM" . $arenaid;

        $this->lobbymap = $lobbyfolder;
        $this->arenamap = $arenafolder;
        $this->dmmap = $dmfolder;

        JumpLeague::copymap(JumpLeague::$pfad . "EnderGames/Maps/" . $lobbymap, Server::getInstance()->getDataPath() . "worlds/" . $lobbyfolder);
        JumpLeague::copymap(JumpLeague::$pfad . "EnderGames/Maps/" . $arenamap, Server::getInstance()->getDataPath() . "worlds/" . $arenafolder);
        JumpLeague::copymap(JumpLeague::$pfad . "EnderGames/Maps/" . $dmmap, Server::getInstance()->getDataPath() . "worlds/" . $dmfolder);

        $this->maxplayers = $config->get("MaxPlayers");
        $this->arenaname = $config->get("ArenaName");

        self::startGame();
    }

    public function getName()
    {
        return $this->arenaname;
    }

    public function addPlayer(Player $player)
    {
        if ($this->status == "online") {
            if (!in_array($player, $this->players)) {
                $player->teleport(Server::getInstance()->getLevelByName($this->lobbymap)->getSpawnLocation());

                $player->setHealth(20);
                $player->setXpLevel(0);
                $player->setGamemode(0);
                $player->setFood(20);
                $player->getInventory()->clearAll();

                $this->players[] = $player;
                ArenaManager::checkSigns();

                foreach(Server::getInstance()->getOnlinePlayers() as $p){
                    $player->showPlayer($p);
                }

                //var_dump($this->players);
            }
        }
    }

    public function removePlayer(Player $player)
    {
        if (in_array($player, $this->players)) {
            unset($this->players[array_search($player, $this->players)]);
            ArenaManager::checkSigns();

            //var_dump($this->players);
        }
    }

    public function TeleportToSpectatorSpawn(Player $player)
    {
        $player->teleport($this->spectatorspawn);
    }

    public function addSpectator(Player $player)
    {
        if ($this->status == "online") {
            if (!in_array($player, $this->spectators)) {
                $player->setGamemode(3);
                $this->spectators[] = $player;
            }
        }
    }

    public function removeSpectator(Player $player)
    {
        if (in_array($player, $this->spectators)) {
            $player->setGamemode(0);
            unset($this->spectators[array_search($player, $this->spectators)]);
        }
    }

    /*
    public function sendJRPopup(){

        $player->sendTip(
            C::BOLD.C::DARK_GRAY."                                                                 ┌──────────────────".C::RESET."\n".
            C::BOLD.C::DARK_GRAY."                                                                 │".C::GREEN." Username: ".C::RESET."\n".
            C::BOLD.C::DARK_GRAY."                                                                 │".C::DARK_GREEN."   ".C::BLUE.$player->getName().C::RESET."\n".
            C::BOLD.C::DARK_GRAY."                                                                 │".C::GREEN." Nickname: ".C::RESET."\n".
            C::BOLD.C::DARK_GRAY."                                                                 │".C::DARK_GREEN."   ".C::BLUE.$pName.C::RESET."\n".
            C::BOLD.C::DARK_GRAY."                                                                 │".C::GREEN." Rang: ".C::RESET."\n".
            C::BOLD.C::DARK_GRAY."                                                                 │".C::DARK_GREEN."   ".C::BLUE.$rang.C::RESET."\n".
            C::BOLD.C::DARK_GRAY."                                                                 │".C::GREEN." Lobby: ".C::RESET."\n".
            C::BOLD.C::DARK_GRAY."                                                                 │".C::DARK_GREEN."   ".C::BLUE.$player->getLevel()->getFolderName().C::RESET."\n".
            C::BOLD.C::DARK_GRAY."                                                                 │".C::GREEN." Players: ".C::RESET."\n".
            C::BOLD.C::DARK_GRAY."                                                                 │".C::DARK_GREEN."  ".$onlinePlayers.C::DARK_GRAY."/".C::DARK_RED.$maxPlayers.C::RESET."\n".
            C::BOLD.C::DARK_GRAY."                                                                 └──────────────────".C::RESET."\n"
        );
    }
    */

    public function sendJRPopup(){
        $popup = "";
        for($i=0;$i<6;$i++){
            //$popup .= " \n";
        }
        $popup .= " \n";
        $rechts = "                                                                 ";

        $best = 0;
        $bestplayer = null;
        $array = $this->players;
        $sortierteplayer = [];
        while(!empty($array)){
            foreach($array as $p){
                $entfernung = round($p->distance($this->startpos[$p->getName()]));
                if($entfernung >= $best){
                    $best = $entfernung;
                    $bestplayer = $p;
                }
            }
            $sortierteplayer[] = $bestplayer;
            unset($array[array_search($bestplayer, $array)]);
            $best = 0;
            $bestplayer = null;
        }

        $popup .= $rechts . "§8§l┌─────[§b" . date("i:s", $this->jrtimer) . "§8]───────────§r\n";
        foreach ($sortierteplayer as $player){
            if($player instanceof Player) {
                $name2 = $player->getName();
                $name2 .= ":";

                $entfernung = round($player->distance($this->startpos[$player->getName()]));

                $popup .= $rechts . "§8§l│ §a$name2 §b$entfernung §r\n";
            }
        }

        $popup .= $rechts . "§8§l└────────────────────§r\n";

        foreach ($this->players as $player) {
            $player->sendTip($popup);
        }
    }
    public function sendDMPopup(){
        $popup = "";
        for($i=0;$i<6;$i++){
            //$popup .= " \n";
        }
        $popup .= " \n";
        $rechts = "                                                                 ";

        $best = 0;
        $bestplayer = null;
        $array = $this->players;
        $sortierteplayer = [];
        while(!empty($array)){
            foreach($array as $p){
                $leben = $this->leben[$p->getName()];
                if($leben >= $best){
                    $best = $leben;
                    $bestplayer = $p;
                }
            }
            $sortierteplayer[] = $bestplayer;
            unset($array[array_search($bestplayer, $array)]);
            $best = 0;
            $bestplayer = null;
        }

        $popup .= $rechts . "§8§l┌─────[§bPlayers§8]───────────§r\n";
        foreach ($sortierteplayer as $player){
            if($player instanceof Player) {
                $name2 = $player->getName();
                $name2 .= ":";

                $leben = $this->leben[$player->getName()];

                $popup .= $rechts . "§8§l│ §a$name2 §b$leben §r\n";
            }
        }

        $popup .= $rechts . "§8§l└────────────────────§r\n";

        foreach ($this->players as $player) {
            $player->sendTip($popup);
        }
    }

    public function startGame()
    {
        // load maps
        Server::getInstance()->loadLevel($this->lobbymap);
        Server::getInstance()->loadLevel($this->arenamap);
        Server::getInstance()->loadLevel($this->dmmap);

        $this->lobbytimer++;
        $this->endtimer++;
        $this->waitingtimer++;

        // init Spawns
        $newspawns = [];
        for ($i = 0; $i < $this->maxplayers; $i++) {
            $spawnid = $i + 1;

            $x = $this->config->getNested("Spawns." . $spawnid . ".X");
            $y = $this->config->getNested("Spawns." . $spawnid . ".Y");
            $z = $this->config->getNested("Spawns." . $spawnid . ".Z");
            $yaw = $this->config->getNested("Spawns." . $spawnid . ".Yaw");
            $pitch = $this->config->getNested("Spawns." . $spawnid . ".Pitch");
            $level = Server::getInstance()->getLevelByName($this->arenamap);
            $pos = new Location($x, $y, $z, $yaw, $pitch, $level);
            $newspawns[] = $pos;
        }
        $this->spawns = $newspawns;

        // init DMSpawns
        $newspawns = [];
        for ($i = 0; $i < $this->maxplayers; $i++) {
            $spawnid = $i + 1;

            $x = $this->config->getNested("DMSpawns." . $spawnid . ".X");
            $y = $this->config->getNested("DMSpawns." . $spawnid . ".Y");
            $z = $this->config->getNested("DMSpawns." . $spawnid . ".Z");
            $yaw = $this->config->getNested("DMSpawns." . $spawnid . ".Yaw");
            $pitch = $this->config->getNested("DMSpawns." . $spawnid . ".Pitch");
            $level = Server::getInstance()->getLevelByName($this->dmmap);
            $pos = new Location($x, $y, $z, $yaw, $pitch, $level);
            $newspawns[] = $pos;
        }
        $this->dmspawns = $newspawns;

        // init SpectatorSpawn
        $pos = null;
        for ($i = 0; $i < $this->maxplayers; $i++) {
            $x = $this->config->getNested("SpectatorSpawn.X");
            $y = $this->config->getNested("SpectatorSpawn.Y");
            $z = $this->config->getNested("SpectatorSpawn.Z");
            $yaw = $this->config->getNested("SpectatorSpawn.Yaw");
            $pitch = $this->config->getNested("SpectatorSpawn.Pitch");
            $level = Server::getInstance()->getLevelByName($this->dmmap);
            $pos = new Location($x, $y, $z, $yaw, $pitch, $level);
        }
        $this->spectatorspawn = $pos;

        $this->gamestate++;
        $this->status = "online";
        $task = Server::getInstance()->getScheduler()->scheduleRepeatingTask(new JLGameTask(JumpLeague::getInstance(), $this), 20);
        $this->gametaskid = $task->getTaskId();
    }

    public function lobby()
    {
        if (count($this->players) >= $this->minplayers) {
            $this->lobbytimer--;

            if ($this->lobbytimer == 60 ||
                $this->lobbytimer == 45 ||
                $this->lobbytimer == 30 ||
                $this->lobbytimer == 20 ||
                $this->lobbytimer == 10 ||
                $this->lobbytimer == 5 ||
                $this->lobbytimer == 4 ||
                $this->lobbytimer == 3 ||
                $this->lobbytimer == 2 ||
                $this->lobbytimer == 1
            ) {
                $this->sendMessageToAll(str_replace("{timer}", $this->lobbytimer, Messages::get("jumpleague.game.lobbytimer.message")));
            }

            if($this->lobbytimer == 6){
                $this->sendMessageToAll(str_replace("{builder}", $this->config->get("Builder"), Messages::get("jumpleague.game.showbuilder")));
            }

            foreach ($this->players as $player) {
                //set exp
                if ($player->getAttributeMap() instanceof AttributeMap) {
                    $exp = $this->lobbytimer / 60;
                    $player->getAttributeMap()->getAttribute(Attribute::EXPERIENCE)->setValue((float)$exp, true, true);
                    $player->getAttributeMap()->getAttribute(Attribute::EXPERIENCE_LEVEL)->setValue($this->lobbytimer, true, true);
                }
            }

            if ($this->lobbytimer == 0) {
                $this->gamestate = 2;

                foreach ($this->players as $player) {
                    $this->freeze[] = $player;
                    foreach ($this->players as $player2) {
                        $player->hidePlayer($player2);
                        //evtl bug wegen map wechsel
                    }
                    $player->teleport($this->spawns[array_search($player, $this->players)]);
                    $this->checkpoints[$player->getName()] = $player->getPosition();
                    $this->startpos[$player->getName()] = $player->getPosition();
                    $player->getInventory()->clearAll();
                }

                $level = $this->spawns[0]->getLevel();
                JumpLeague::$levels[] = $level;

                ArenaManager::checkSigns();
            }
        } else {
            if ((time() % 30) == 0) {
                $this->sendMessageToAll(Messages::get("jumpleague.game.not_enough_players"));
            }
        }
    }

    public function jr()
    {
        //var_dump($this->chests);
        $this->sendJRPopup();

        if (count($this->players) <= 1) {
            //$this->gamestate = 4;
        }

        if ($this->waitingtimer > 0) {
            $this->waitingtimer--;
            if ($this->waitingtimer == 5 ||
                $this->waitingtimer == 4 ||
                $this->waitingtimer == 3 ||
                $this->waitingtimer == 2 ||
                $this->waitingtimer == 1
            ) {
                $this->sendMessageToAll(str_replace("{timer}", $this->waitingtimer, Messages::get("jumpleague.game.waitingtimer.message")));
            }
        }
        if ($this->waitingtimer == 0) {
            $this->freeze = [];
            $this->waitingtimer--;
            $this->sendMessageToAll(Messages::get("jumpleague.game.start.message"));
            foreach ($this->players as $player) {
                JumpLeague::$lasthit[$player->getName()] = "";
            }
        }
        if ($this->waitingtimer < 0) {

            if ($this->jrtimer > 0) {
                $this->jrtimer--;

                $min = $this->jrtimer / 60;

                if(is_int($min) && $min != 0){
                    $this->sendMessageToAll(str_replace("{min}", $min, Messages::get("jumpleague.game.jrtimer.message")));
                }

                if($this->jrtimer <= 5 && $this->jrtimer > 0){
                    $this->sendMessageToAll(str_replace("{timer}", $this->jrtimer, Messages::get("jumpleague.game.jrtimer.message2")));
                }

            }

            if ($this->jrtimer == 0) {
                $this->jrtimer--;
                $this->sendMessageToAll(Messages::get("jumpleague.game.dmstart.message"));
                foreach ($this->players as $player) {
                    JumpLeague::$lasthit[$player->getName()] = "";
                }
                $this->gamestate = 3;

                foreach ($this->players as $player) {
                    $this->leben[$player->getName()] = 3;
                    $player->teleport($this->dmspawns[array_search($player, $this->players)]);

                    foreach ($this->players as $player2) {
                        $player->showPlayer($player2);
                        //evtl bug wegen map wechsel
                    }
                }
            }

        }
    }

    public function deathmatch()
    {
        $this->sendDMPopup();
        if (count($this->players) <= 1) {
            $this->gamestate = 4;
        }
    }

    public function end()
    {
        $level = $this->spawns[0]->getLevel();
        if (in_array($level, JumpLeague::$levels)) {
            unset(JumpLeague::$levels[array_search($level, JumpLeague::$levels)]);
        }

        if ($this->endtimer > 0) {
            $this->endtimer--;
            if ($this->endtimer == 15 ||
                $this->endtimer == 10 ||
                $this->endtimer == 5 ||
                $this->endtimer == 4 ||
                $this->endtimer == 3 ||
                $this->endtimer == 2 ||
                $this->endtimer == 1
            ) {
                $this->sendMessageToAll(str_replace("{timer}", $this->endtimer, Messages::get("jumpleague.game.endtimer.message")));
            }
        }
        if ($this->endtimer == 0) {
            $this->endtimer--;
            $this->sendMessageToAll(Messages::get("jumpleague.game.restart"));
            foreach ($this->players as $player) {
                $this->removePlayer($player);
                $player->getInventory()->clearAll();
                $player->setHealth(20);
                $player->setXpLevel(0);
                $player->setGamemode(0);
                $player->setFood(20);
            }
            foreach ($this->spectators as $player) {
                $this->removeSpectator($player);$player->setHealth(20);
                $player->setXpLevel(0);
                $player->setGamemode(0);
                $player->setFood(20);
                $player->getInventory()->clearAll();
            }
            ArenaManager::deleteArena($this);
            $this->status = "offline";

            $level1 = Server::getInstance()->getLevelByName($this->lobbymap);
            $level2 = Server::getInstance()->getLevelByName($this->arenamap);
            $level3 = Server::getInstance()->getLevelByName($this->dmmap);

            Server::getInstance()->unloadLevel($level1);
            Server::getInstance()->unloadLevel($level2);
            Server::getInstance()->unloadLevel($level3);

            JumpLeague::deleteDirectory(Server::getInstance()->getDataPath() . "worlds/" . $this->lobbymap);
            JumpLeague::deleteDirectory(Server::getInstance()->getDataPath() . "worlds/" . $this->arenamap);
            JumpLeague::deleteDirectory(Server::getInstance()->getDataPath() . "worlds/" . $this->dmmap);

            Server::getInstance()->getScheduler()->cancelTask($this->gametaskid);
        }
    }

    public function sendMessageToAll($msg)
    {
        foreach ($this->players as $player) {
            $player->sendMessage($msg);
        }
        foreach ($this->spectators as $player) {
            $player->sendMessage($msg);
        }
    }

    public function sendMessageToPlayers($msg)
    {
        foreach ($this->players as $player) {
            $player->sendMessage($msg);
        }
    }

    public function sendMessageToSpectators($msg)
    {
        foreach ($this->spectators as $player) {
            $player->sendMessage($msg);
        }
    }
}