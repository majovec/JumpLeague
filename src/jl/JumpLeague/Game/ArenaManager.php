<?php

namespace jl\JumpLeague\Game;

use jl\JumpLeague\Main\JumpLeague;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;

class ArenaManager
{

    public static $arenaconfigs = [];
    public static $arenas = [];


    public static $arenabalance = [];

    public static $arenaid = 0;

    public static $config;

    public function __construct()
    {
        $files = scandir(JumpLeague::$pfad . "EnderGames/Arenas/");
        foreach ($files as $filename) {
            if ($filename != "." && $filename != "..") {
                $config = new Config(JumpLeague::$pfad . "EnderGames/Arenas/" . $filename, Config::YAML);
                if($config->get("Finished")) {
                    self::$arenaconfigs[] = $config;
                    self::$arenabalance[$config->get("ArenaName")] = 0;
                }
                //Server::getInstance()->getLogger()->info("§cFinished: " . $config->get("Finished"));
            }
        }
        //var_dump(self::$arenaconfigs);


        //Server::getInstance()->getScheduler()->scheduleDelayedTask(new ResetSignsTask(EnderGames::getInstance()), 20);
        self::resetSigns();
        self::$config = new Config(JumpLeague::$pfad . "EnderGames/config.yml", Config::YAML);
    }

    /*
     * SignFormat bei Aktiver Arena:
     * - EnderGames $id -
     * Arena99
     * 0 / 16
     * §aLobby oder §6Lobby
     */

    /*
     * SignFormat bei Suchender Arena:
     * - EnderGames -
     * §aArena wird
     * §aGesucht...
     * - EnderGames -
     */

    public static function createArena()
    {
        self::$arenaid++;

        //$config = self::$arenaconfigs[array_rand(self::$arenaconfigs)];

        $cfg = null;

        foreach (self::$arenaconfigs as $config) {
            $name = $config->get("ArenaName");

            if ($cfg == null) {
                $cfg = $config;
            } else {
                if (self::$arenabalance[$name] < self::$arenabalance[$cfg->get("ArenaName")]) {
                    $cfg = $config;
                }
            }
        }

        self::$arenabalance[$cfg->get("ArenaName")] = self::$arenabalance[$cfg->get("ArenaName")] + 1;

        $arena = new Arena(self::$arenaid, $cfg);
        self::$arenas[] = $arena;

        //var_dump($arena);

        return $arena;
    }

    public static function deleteArena(Arena $arena)
    {
        if (in_array($arena, self::$arenas)) {
            unset(self::$arenas[array_search($arena, self::$arenas)]);
        }
    }

    public static function resetSigns()
    {
        $signs = self::getSigns();
        foreach ($signs as $sign) {
            if ($sign instanceof Sign) {
                $text = $sign->getText();

                $found = strpos($text[0], " - EnderGames");
                if ($found !== false) {
                    $sign->setText(" - EnderGames - ", "§aArena wird", "§aGesucht...", " - EnderGames - ");
                }
            }
        }
    }

    public static function checkSigns()
    {
        $signs = self::getSigns();

        foreach ($signs as $sign) {
            if ($sign instanceof Sign) {
                if ($sign->getText()[0] == "- EnderGames -" && $sign->getText()[1] == "Loading") {
                    $sign->setText("- EnderGames -", "§aArena wird", "§aGesucht...", "- EnderGames -");
                }
                if ($sign->getText()[0] == "- EnderGames -" && $sign->getText()[2] == "§aGesucht...") {
                    if (count(self::$arenaconfigs) > 0) {
                        $arena = self::createArena();
                        $line1 = "- EnderGames " . $arena->arenaid . " -";
                        $line2 = $arena->arenaname;
                        $line3 = "§aLobby";
                        $line4 = "§e0 / " . $arena->maxplayers;

                        $sign->setText($line1, $line2, $line3, $line4);
                    }
                } else {
                    $found = strpos($sign->getText()[0], "- EnderGames");

                    if ($found !== false) {
                        $arenaid = explode(" ", $sign->getText()[0])[2];
                        $arena = ArenaManager::getArenaByID($arenaid);

                        if ($arena->gamestate > 1) {
                            $sign->setText("- EnderGames -", "§aArena wird", "§aGesucht...", "- EnderGames -");
                            self::checkSigns();
                        } else {
                            $line1 = "- EnderGames " . $arena->arenaid . " -";
                            $line2 = $arena->arenaname;
                            $line3 = count($arena->players) >= $arena->maxplayers ? "§6Lobby" : "§aLobby";
                            $line4 = "§e" . count($arena->players) . " / " . $arena->maxplayers;

                            $sign->setText($line1, $line2, $line3, $line4);
                        }
                    }
                }
            }
        }
    }

    public static function getSigns()
    {
        $signs = [];

        $levels = Server::getInstance()->getLevels();
        foreach ($levels as $level) {
            foreach ($level->getTiles() as $tile) {
                if ($tile instanceof Sign) {
                    // strpos($string, $suchendeswort);
                    $found = strpos($tile->getText()[0], "- EnderGames");

                    if ($found !== false) {
                        $signs[] = $tile;
                    }
                }
            }
        }
        return $signs;
    }

    public static function inArena(Player $player)
    {

        foreach (self::$arenas as $arena) {
            if (in_array($player, $arena->players) || in_array($player, $arena->spectators)) {
                return true;
            }
        }

        return false;
    }

    public static function getArena(Player $player)
    {

        foreach (self::$arenas as $arena) {
            if (in_array($player, $arena->players) || in_array($player, $arena->spectators)) {
                if($arena instanceof Arena) {
                    return $arena;
                }
            }
        }

        return null;
    }

    public static function getArenaByName($arenaname)
    {

        foreach (self::$arenas as $arena) {
            if ($arena->arenaname == $arenaname) {
                return $arena;
            }
        }

        return null;
    }

    public static function getArenaByID($arenaid)
    {

        foreach (self::$arenas as $arena) {
            if ($arena->arenaid == $arenaid) {
                return $arena;
            }
        }

        return null;
    }
}