<?php

namespace jl\JumpLeague\Commands;

use jl\JumpLeague\Language\Messages;
use jl\JumpLeague\Main\JumpLeague;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;

class CommandJumpLeague extends Command
{

    public function __construct()
    {
        parent::__construct("EnderGames", "", null, ["jl"]);
    }

    public function execute(CommandSender $sender, string $label, array $args)
    {
        if (empty($args[0])) {
            self::help($sender, $label, $args);
            return;
        }

        switch (strtolower($args[0])) {
            case "help":
                self::help($sender, $label, $args);
                break;
            case "addarena":
                self::addArena($sender, $label, $args);
                break;
            case "deletearena":
            case "delarena":
                self::deleteArena($sender, $label, $args);
                break;
            case "setlobby":
                self::setLobby($sender, $label, $args);
                break;
            case "setspectator":
                self::setSpectator($sender, $label, $args);
                break;
            case "setspawn":
                self::setSpawn($sender, $label, $args);
                break;
            case "setdmspawn":
                self::setDMSpawn($sender, $label, $args);
                break;
            case "setbuilder":
                self::setBuilder($sender, $label, $args);
                break;
            case "registersign":
            case "regsign":
                self::registerSign($sender, $label, $args);
                break;
            case "reset":
                self::reset($sender, $label, $args);
                break;
            default:
                self::help($sender, $label, $args);
                break;
        }
    }


    private function addArena(CommandSender $sender, $label, array $args)
    {
        if (!$sender->hasPermission("jl.setup")) {
            $sender->sendMessage(Messages::get("jumpleague.command.no_permissions"));
            return;
        }
        if (empty($args[1]) || empty($args[2])) {
            self::help($sender, $label, $args);
            return;
        }

        $arenaname = $args[1];
        $maxplayers = (int)$args[2];

        if (JumpLeague::arenaExists($arenaname)) {
            $sender->sendMessage(Messages::get("jumpleague.command.addarena.arenaexists"));
            return;
        }
        JumpLeague::createArena($arenaname, $maxplayers);
        $sender->sendMessage(Messages::get("jumpleague.command.addarena.success"));
    }

    private function deleteArena(CommandSender $sender, $label, array $args)
    {
        if (!$sender->hasPermission("jl.setup")) {
            $sender->sendMessage(Messages::get("jumpleague.command.no_permissions"));
            return;
        }

        if (empty($args[1])) {
            self::help($sender, $label, $args);
            return;
        }

        $arenaname = $args[1];

        if (!JumpLeague::arenaExists($arenaname)) {
            $sender->sendMessage(Messages::get("jumpleague.command.deletearena.arena_not_exists"));
            return;
        }

        JumpLeague::deleteArena($arenaname);
        Server::getInstance()->shutdown();
    }

    private function reset(CommandSender $sender, $label, array $args)
    {
        if (!$sender->hasPermission("jl.setup")) {
            $sender->sendMessage(Messages::get("jumpleague.command.no_permissions"));
            return;
        }

        if (empty($args[3])) {
            return;
        }

        if ($args[1] != "true") {
            return;
        }

        if ($args[2] != "true") {
            return;
        }

        if ($args[3] != "true") {
            return;
        }

        JumpLeague::deleteDirectory(JumpLeague::$pfad . "EnderGames");
        JumpLeague::deleteDirectory(JumpLeague::getInstance()->getDataFolder());
        Server::getInstance()->shutdown();
    }

    private function registerSign(CommandSender $sender, $label, array $args)
    {
        if (!$sender->hasPermission("jl.setup")) {
            $sender->sendMessage(Messages::get("jumpleague.command.no_permissions"));
            return;
        }

        if (!$sender instanceof Player) {
            return;
        }

        JumpLeague::$registerSign = true;
        JumpLeague::$registerSignWho = $sender->getName();
        $sender->sendMessage(Messages::get("jumpleague.command.registersign.success"));
    }

    private function setBuilder(CommandSender $sender, $label, array $args)
    {
        if (!$sender->hasPermission("jl.setup")) {
            $sender->sendMessage(Messages::get("jumpleague.command.no_permissions"));
            return;
        }

        if (!$sender instanceof Player) {
            return;
        }

        if (empty($args[1]) || empty($args[2])) {
            self::help($sender, $label, $args);
            return;
        }
        array_shift($args);
        $arenaname = array_shift($args);
        $builder = implode(" ", $args);

        if (!JumpLeague::arenaExists($arenaname)) {
            $sender->sendMessage(Messages::get("jumpleague.command.deletearena.arena_not_exists"));
            return;
        }
        $sender->sendMessage(str_replace("{builder}", $builder, Messages::get("jumpleague.command.setbuilder.success")));
        JumpLeague::setBuilder($arenaname, $builder);

        if(JumpLeague::checkArena($arenaname)){
            $sender->sendMessage(str_replace("{arenaname}", $arenaname, Messages::get("jumpleague.command.create_arena.success")));
        }
    }

    private function setSpectator(CommandSender $sender, $label, array $args)
    {
        if (!$sender->hasPermission("jl.setup")) {
            $sender->sendMessage(Messages::get("jumpleague.command.no_permissions"));
            return;
        }

        if (!$sender instanceof Player) {
            return;
        }

        if (empty($args[1])) {
            self::help($sender, $label, $args);
            return;
        }

        $arenaname = $args[1];

        if (!JumpLeague::arenaExists($arenaname)) {
            $sender->sendMessage(Messages::get("jumpleague.command.deletearena.arena_not_exists"));
            return;
        }

        $sender->sendMessage(Messages::get("jumpleague.command.setspectator.success"));
        JumpLeague::setSpectatorSpawn($sender, $arenaname);

        if(JumpLeague::checkArena($arenaname)){
            $sender->sendMessage(str_replace("{arenaname}", $arenaname, Messages::get("jumpleague.command.create_arena.success")));
        }
    }

    private function setDMSpawn(CommandSender $sender, $label, array $args)
    {
        if (!$sender->hasPermission("jl.setup")) {
            $sender->sendMessage(Messages::get("jumpleague.command.no_permissions"));
            return;
        }

        if (!$sender instanceof Player) {
            return;
        }

        if (empty($args[1]) || empty($args[2])) {
            self::help($sender, $label, $args);
            return;
        }

        $arenaname = $args[1];
        $spawnid = (int)$args[2];

        if (!JumpLeague::arenaExists($arenaname)) {
            $sender->sendMessage(Messages::get("jumpleague.command.deletearena.arena_not_exists"));
            return;
        }

        $sender->sendMessage(str_replace("{spawnid}", $spawnid, Messages::get("jumpleague.command.setdmspawn.success")));
        JumpLeague::setDMSpawn($sender, $arenaname, $spawnid);

        if(JumpLeague::checkArena($arenaname)){
            $sender->sendMessage(str_replace("{arenaname}", $arenaname, Messages::get("jumpleague.command.create_arena.success")));
        }
    }

    private function setSpawn(CommandSender $sender, $label, array $args)
    {
        if (!$sender->hasPermission("jl.setup")) {
            $sender->sendMessage(Messages::get("jumpleague.command.no_permissions"));
            return;
        }

        if (!$sender instanceof Player) {
            return;
        }

        if (empty($args[1]) || empty($args[2])) {
            self::help($sender, $label, $args);
            return;
        }

        $arenaname = $args[1];
        $spawnid = (int)$args[2];

        if (!JumpLeague::arenaExists($arenaname)) {
            $sender->sendMessage(Messages::get("jumpleague.command.deletearena.arena_not_exists"));
            return;
        }

        $sender->sendMessage(str_replace("{spawnid}", $spawnid, Messages::get("jumpleague.command.setspawn.success")));
        JumpLeague::setSpawn($sender, $arenaname, $spawnid);

        if(JumpLeague::checkArena($arenaname)){
            $sender->sendMessage(str_replace("{arenaname}", $arenaname, Messages::get("jumpleague.command.create_arena.success")));
        }
    }

    private function setLobby(CommandSender $sender, $label, array $args)
    {
        if (!$sender->hasPermission("jl.setup")) {
            $sender->sendMessage(Messages::get("jumpleague.command.no_permissions"));
            return;
        }

        if (!$sender instanceof Player) {
            return;
        }

        if (empty($args[1])) {
            self::help($sender, $label, $args);
            return;
        }

        $arenaname = $args[1];

        if (!JumpLeague::arenaExists($arenaname)) {
            $sender->sendMessage(Messages::get("jumpleague.command.deletearena.arena_not_exists"));
            return;
        }

        JumpLeague::setLobby($sender, $arenaname);
        $sender->sendMessage(Messages::get("jumpleague.command.setlobby.success"));

        if(JumpLeague::checkArena($arenaname)){
            $sender->sendMessage(str_replace("{arenaname}", $arenaname, Messages::get("jumpleague.command.create_arena.success")));
        }
    }

    private function help(CommandSender $sender, $label, array $args)
    {
        if (!$sender->hasPermission("cl.sc")) {
            $sender->sendMessage(Messages::get("jumpleague.command.no_permissions"));
            return;
        }

        $helpcommands = [
            "§e/jumpleague help §7[§cZeigt alle EnderGames Command§7]",
            "§e/jumpleague addarena {ArenaName} {MaxPlayers} §7[§cErstellt eine neue Arena§7]",
            "§e/jumpleague delarena {ArenaName} §7[§cLöscht eine Arena§7]",
            "§e/jumpleague setSpawn {ArenaName} {SpawnID} §7[§cSetzt die Spawns der Arena§7]",
            "§e/jumpleague setDMSpawn {ArenaName} {SpawnID} §7[§cSetzt die DMSpawns der Arena§7]",
            "§e/jumpleague setlobby {ArenaName} §7[§cSetzt die Lobby der Arena§7]",
            "§e/jumpleague setSpectator {ArenaName} §7[§cSetzt den Spectator Spawn der Arena§7]",
            "§e/jumpleague setbuilder {ArenaName} {Builder} §7[§cSetzt den Builder der Map§7]",
            "§e/jumpleague registerSign §7[§cRegistriert ein neues Schild§7]",
            "§e/jumpleague reset true true true §7[§cRESETTET DAS GANZE PLUGIN!§7]"
        ];

        $currentsite = 1;

        if (!empty($args[0])) {
            if ((int)$args[0] != 0) {
                $currentsite = (int)$args[0];
            }
        }

        if (!empty($args[1])) {
            if ((int)$args[1] != 0) {
                $currentsite = (int)$args[1];
            }
        }

        $maxsites = ceil(count($helpcommands) / 7);

        if ($maxsites == 0) {
            $maxsites = 1;
        }

        if ($currentsite > $maxsites) {
            $currentsite = 1;
        }

        $sender->sendMessage(" ");
        $sender->sendMessage("§7=====[§bJumpLeague Commands [Seite " . $currentsite . " / " . $maxsites . "]§7]=====");

        if (count($helpcommands) == 0) {
            $sender->sendMessage("§7- §eKeine EnderGames Commands gefunden");
        }

        if ($currentsite > 1) {

            $remove = ($currentsite - 1) * 7;

            for ($i2 = 0; $i2 < $remove; $i2++) {
                array_shift($helpcommands);
            }
        }

        for ($i = 0; $i < 7; $i++) {
            $helpcommand = isset($helpcommands[$i]) ? $helpcommands[$i] : "";
            if ($helpcommand != "") $sender->sendMessage("§7- §e" . $helpcommand);
        }
        $sender->sendMessage("§7=====[§bJumpLeague Commands [Seite " . $currentsite . " / " . $maxsites . "]§7]=====");
    }
}
