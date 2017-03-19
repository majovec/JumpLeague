<?php

namespace jl\JumpLeague\Language;

use jl\JumpLeague\Main\JumpLeague;
use pocketmine\utils\Config;

class Messages
{

    public static $messages;

    public function __construct()
    {
        $config = new Config(JumpLeague::getInstance()->getDataFolder() . "messages.yml", Config::YAML);
        self::$messages = $config;

        self::init($config);
    }

    private function init(Config $config)
    {
        if ($config->getNested("jumpleague.command.no_permission") == null) {
            $config->setNested("jumpleague.command.no_permission", "{PREFIX} &cDu hast keine Permission diesen Befehl ausführen zu können!");
            $config->save();
        }
        if ($config->getNested("jumpleague.command.deletearena.arena_not_exists") == null) {
            $config->setNested("jumpleague.command.deletearena.arena_not_exists", "{PREFIX} &cDiese Arena existiert nicht!");
            $config->save();
        }
        if ($config->getNested("jumpleague.command.registersign.success") == null) {
            $config->setNested("jumpleague.command.registersign.success", "{PREFIX} &bTippe nun ein Schild an!");
            $config->save();
        }
        if ($config->getNested("jumpleague.command.addarena.success") == null) {
            $config->setNested("jumpleague.command.addarena.success", "{PREFIX} &bDu hast eine neue Arena erstellt!");
            $config->save();
        }
        if ($config->getNested("jumpleague.command.addarena.arenaexists") == null) {
            $config->setNested("jumpleague.command.addarena.arenaexists", "{PREFIX} &cDiese Arena existiert bereits!");
            $config->save();
        }
        if ($config->getNested("jumpleague.command.setspawn.success") == null) {
            $config->setNested("jumpleague.command.setspawn.success", "{PREFIX} &bDu hast den {spawnid}. Spawn der Arena gesetzt!");
            $config->save();
        }
        if ($config->getNested("jumpleague.command.setspectator.success") == null) {
            $config->setNested("jumpleague.command.setspectator.success", "{PREFIX} &bDu hast den {spawnid}. Spectator Spawn der Arena gesetzt!");
            $config->save();
        }
        if ($config->getNested("jumpleague.command.setdmspawn.success") == null) {
            $config->setNested("jumpleague.command.setdmspawn.success", "{PREFIX} &bDu hast den {spawnid}. DMSpawn der Arena gesetzt!");
            $config->save();
        }
        if ($config->getNested("jumpleague.command.setbuilder.success") == null) {
            $config->setNested("jumpleague.command.setbuilder.success", "{PREFIX} &bDu hast den Builder der Arena gesetzt!");
            $config->save();
        }
        if ($config->getNested("jumpleague.command.create_arena.success") == null) {
            $config->setNested("jumpleague.command.create_arena.success", "{PREFIX} &bDie Arena {arenaname} wurde erfolgreich erstellt und kann nach einem Restart des Servers benutzt werden!");
            $config->save();
        }
        if ($config->getNested("jumpleague.command.setlobby.success") == null) {
            $config->setNested("jumpleague.command.setlobby.success", "{PREFIX} &bDu hast die WarteLobby der Arena gesetzt!");
            $config->save();
        }
        if ($config->getNested("jumpleague.game.lobbytimer.message") == null) {
            $config->setNested("jumpleague.game.lobbytimer.message", "{PREFIX} &bDie Runde startet in &e{timer} &bSekunden");
            $config->save();
        }
        if ($config->getNested("jumpleague.game.waitingtimer.message") == null) {
            $config->setNested("jumpleague.game.waitingtimer.message", "{PREFIX} &bNoch &e{timer} &bSekunden...");
            $config->save();
        }
        if ($config->getNested("jumpleague.game.jrtimer.message") == null) {
            $config->setNested("jumpleague.game.jrtimer.message", "{PREFIX} &bDas Jump & Run endet in &e{min} &bMinuten!");
            $config->save();
        }
        if ($config->getNested("jumpleague.game.jrtimer.message2") == null) {
            $config->setNested("jumpleague.game.jrtimer.message2", "{PREFIX} &bDas Jump & Run endet in &e{timer} &bSekunden!");
            $config->save();
        }
        if ($config->getNested("jumpleague.game.start.message") == null) {
            $config->setNested("jumpleague.game.start.message", "{PREFIX} &bDie Runde startet JETZT! Viel Glück!");
            $config->save();
        }
        if ($config->getNested("jumpleague.game.showbuilder") == null) {
            $config->setNested("jumpleague.game.showbuilder", "{PREFIX} &bMap wurde gebaut von&7: &e{builder}");
            $config->save();
        }
        if ($config->getNested("jumpleague.game.not_enough_players") == null) {
            $config->setNested("jumpleague.game.not_enough_players", "{PREFIX} &cEs sind nicht genug Spieler in dieser Arena!");
            $config->save();
        }
        if ($config->getNested("jumpleague.game.endtimer.message") == null) {
            $config->setNested("jumpleague.game.endtimer.message", "{PREFIX} &cDie Arena restartet in &e{timer} &cSekunden");
            $config->save();
        }
        if ($config->getNested("jumpleague.game.restart") == null) {
            $config->setNested("jumpleague.game.restart", "{PREFIX} &cDie Arena restartet jetzt!");
            $config->save();
        }
        if ($config->getNested("jumpleague.game.forcestart") == null) {
            $config->setNested("jumpleague.game.forcestart", "{PREFIX} &5Forcestart Aktiviert!");
            $config->save();
        }
        if ($config->getNested("jumpleague.game.die_message") == null) {
            $config->setNested("jumpleague.game.die_message", "{PREFIX} &b{player} &eist Gestorben!");
            $config->save();
        }
        if ($config->getNested("jumpleague.game.killed_message") == null) {
            $config->setNested("jumpleague.game.killed_message", "{PREFIX} &b{player} &ewurde von &b{killer} &egetötet!");
            $config->save();
        }
        if ($config->getNested("jumpleague.game.playersleft") == null) {
            $config->setNested("jumpleague.game.playersleft", "{PREFIX} &b{alive} &eSpieler übrig!");
            $config->save();
        }
        if ($config->getNested("jumpleague.game.full.message") == null) {
            $config->setNested("jumpleague.game.full.message", "{PREFIX} &cDie Arena ist bereits Voll! nur &6Premium &cSpieler können jetzt noch joinen!");
            $config->save();
        }
        if ($config->getNested("jumpleague.game.full.kicked.message") == null) {
            $config->setNested("jumpleague.game.full.kicked.message", "{PREFIX} &cDu wurdest gekickt um &6Premium &cSpieler freien Platz zu machen!");
            $config->save();
        }
        if ($config->getNested("jumpleague.game.checkpoint.popup") == null) {
            $config->setNested("jumpleague.game.checkpoint.popup", "{PREFIX} &6Checkpoint erreicht!");
            $config->save();
        }
        if ($config->getNested("jumpleague.game.goal.message.self") == null) {
            $config->setNested("jumpleague.game.goal.message.self", "{PREFIX} &bDu hast das Ziel erreicht! Glückwunsch!");
            $config->save();
        }
        if ($config->getNested("jumpleague.game.goal.message.all") == null) {
            $config->setNested("jumpleague.game.goal.message.all", "{PREFIX} &b{player} hat das Ziel erreicht!");
            $config->save();
        }
    }

    public static function get($pfad)
    {
        $msg = self::$messages->getNested($pfad);
        $msg = str_replace("{PREFIX}", JumpLeague::PREFIX, $msg);
        $msg = str_replace("&", "§", $msg);

        //EnderGames::getInstance()->getLogger()->info("Message: " . $msg);

        return $msg;
    }

}