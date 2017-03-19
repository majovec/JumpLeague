<?php

namespace Bluplayz\JumpLeague\Main;

use Bluplayz\JumpLeague\Commands\CommandJumpLeague;
use Bluplayz\JumpLeague\Game\ArenaManager;
use Bluplayz\JumpLeague\Language\Messages;
use Bluplayz\JumpLeague\Listeners\BlockBreakListener;
use Bluplayz\JumpLeague\Listeners\BlockPlaceListener;
use Bluplayz\JumpLeague\Listeners\ChunkLoadListener;
use Bluplayz\JumpLeague\Listeners\EntityDamageListener;
use Bluplayz\JumpLeague\Listeners\InventoryCloseListener;
use Bluplayz\JumpLeague\Listeners\PlayerCommandPreprocessListener;
use Bluplayz\JumpLeague\Listeners\PlayerDeathListener;
use Bluplayz\JumpLeague\Listeners\PlayerInteractListener;
use Bluplayz\JumpLeague\Listeners\PlayerJoinListener;
use Bluplayz\JumpLeague\Listeners\PlayerMoveListener;
use Bluplayz\JumpLeague\Listeners\PlayerRespawnListener;
use Bluplayz\JumpLeague\Tasks\OpenDelayedInventory;
use pocketmine\block\Block;
use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\FloatingInventory;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\tile\Chest;
use pocketmine\tile\Tile;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class JumpLeague extends PluginBase
{
    const PREFIX = "§7[§cJumpLeague§7]§f";

    public static $pfad = "";
    public static $instance;

    public static $registerSignWho = "";
    public static $registerSign = false;

    public static $lasthit = [];
    public static $levels = [];

    public static $oldloc = [];
    public static $inChest = [];

    public function onEnable()
    {
        $this->getLogger()->info(self::PREFIX . TextFormat::GOLD . " lade EnderGames...");

        // Save instance
        self::$instance = $this;

        // init Directory
        $this->initDir();

        // init Config
        $this->initConfig();

        // Register Commands
        $this->registerCommands();

        // Register Events
        $this->registerEvents();

        // init Messages
        new Messages();

        // init ArenaManager
        new ArenaManager();

        //Delete old ArenaWorlds
        for ($i = 0; $i < 250; $i++) {
            if (file_exists(Server::getInstance()->getDataPath() . "worlds/JumpLeagueArena" . $i)) {
                self::deleteDirectory(Server::getInstance()->getDataPath() . "worlds/JumpLeagueArena" . $i);
            }
            if (file_exists(Server::getInstance()->getDataPath() . "worlds/JumpLeagueLobby" . $i)) {
                self::deleteDirectory(Server::getInstance()->getDataPath() . "worlds/JumpLeagueLobby" . $i);
            }
            if (file_exists(Server::getInstance()->getDataPath() . "worlds/JumpLeagueDM" . $i)) {
                self::deleteDirectory(Server::getInstance()->getDataPath() . "worlds/JumpLeagueDM" . $i);
            }
        }

        $this->getLogger()->info(self::PREFIX . TextFormat::GOLD . " EnderGames geladen");
    }

    public static function getInstance()
    {
        return self::$instance;
    }

    private function registerCommands()
    {
        $this->getServer()->getCommandMap()->register("jl", new CommandJumpLeague());
    }

    private function registerEvents()
    {

        $this->getServer()->getPluginManager()->registerEvents(new PlayerJoinListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new BlockPlaceListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new BlockBreakListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new ChunkLoadListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new PlayerCommandPreprocessListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new PlayerDeathListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new PlayerRespawnListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new PlayerInteractListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new PlayerMoveListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new InventoryCloseListener(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new EntityDamageListener(), $this);

    }

    public static function deleteArena($arenaname)
    {
        unlink(self::$pfad . "EnderGames/Arenas/" . $arenaname . ".yml");
    }

    private function initDir()
    {
        @mkdir($this->getDataFolder());

        @mkdir($this->getServer()->getDataPath() . "players/Data/");
        @mkdir($this->getServer()->getDataPath() . "players/Data/EnderGames");
        @mkdir($this->getServer()->getDataPath() . "players/Data/EnderGames/Arenas");
        @mkdir($this->getServer()->getDataPath() . "players/Data/EnderGames/Maps");

        self::$pfad = $this->getServer()->getDataPath() . "players/Data/";

        $buchstaben = range("a", "z");
        $zahlen = range("0", "9");
        $array = array_merge($buchstaben, $zahlen);

        foreach ($array as $a) {
            @mkdir($this->getServer()->getDataPath() . "players/Data/" . $a);
        }
    }

    private function initConfig()
    {
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $items = array(
            array(261, 0, 1),
            array(262, 0, 5),
            array(268, 0, 1),
            array(298, 0, 1),
            array(299, 0, 1),
            array(300, 0, 1),
            array(301, 0, 1)
        );
        if (!$config->exists("chestitems")) {
            $config->set("chestitems", $items);
            $config->save();
        }
    }

    public static function copymap($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::copymap($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public static function fillChests(Level $level)
    {
        $config = new Config(self::getInstance()->getDataFolder() . "config.yml", Config::YAML);
        $tiles = $level->getTiles();
        foreach ($tiles as $t) {
            if ($t instanceof Chest) {
                $chest = $t;
                $chest->getInventory()->clearAll();
                if ($chest->getInventory() instanceof ChestInventory) {
                    for ($i = 0; $i <= 26; $i++) {
                        $rand = rand(1, 3);
                        if ($rand == 1) {
                            $k = array_rand($config->get("chestitems"));
                            $v = $config->get("chestitems")[$k];
                            $chest->getInventory()->setItem($i, Item::get($v[0], $v[1], $v[2]));
                        }
                    }
                }
            }
        }
    }

    public static function fillChest(Chest $chest)
    {
        $config = new Config(self::getInstance()->getDataFolder() . "config.yml", Config::YAML);
        $chest->getInventory()->clearAll();
        if ($chest->getInventory() instanceof ChestInventory) {
            for ($i = 0; $i <= 26; $i++) {
                $rand = rand(1, 3);
                if ($rand == 1) {
                    $k = array_rand($config->get("chestitems"));
                    $v = $config->get("chestitems")[$k];
                    $chest->getInventory()->setItem($i, Item::get($v[0], $v[1], $v[2]));
                }
            }
        }
    }

    public static function deleteDirectory($dirPath)
    {
        if (is_dir($dirPath)) {
            $objects = scandir($dirPath);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
                        self::deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dirPath . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dirPath);
        }
    }

    public static function TeleportToLobby(Player $player){
        $level = Server::getInstance()->getDefaultLevel();
        $player->setHealth(20);
        $player->getInventory()->clearAll();
        $player->getInventory()->setArmorContents(null);
        $player->teleport($level->getSafeSpawn());
    }

    public static function createChest(Player $player)
    {
        $chestBlock = new \pocketmine\block\Chest();

        if(ArenaManager::inArena($player)) {
            $arena = ArenaManager::getArena($player);
            $x = $player->getX();
            $y = $player->getY();
            $z = $player->getZ();

            $player->getLevel()->setBlock(new Vector3($x, $y, $z), $chestBlock, true, true);

            $nbt = new CompoundTag("", [
                new ListTag("Items", []),
                new StringTag("id", Tile::CHEST),
                new IntTag("x", $x),
                new IntTag("y", $y),
                new IntTag("z", $z)
            ]);
            $nbt->Items->setTagType(NBT::TAG_Compound);
            $tile = Tile::createTile("Chest", $player->getLevel()->getChunk($x >> 4, $z >> 4), $nbt);
            if ($tile instanceof Chest) {
                $tile->getInventory()->clearAll();
                $tile->getInventory()->setContents($player->getInventory()->getContents());
            }
        }
    }

    public static function arenaExists($arenaname)
    {
        return file_exists(self::$pfad . "EnderGames/Arenas/" . $arenaname . ".yml");
    }

    public static function checkArena($arenaname)
    {
        $config = new Config(self::$pfad . "EnderGames/Arenas/" . $arenaname . ".yml", Config::YAML);

        if ($config->exists("ArenaName") &&
            $config->exists("MaxPlayers") &&
            $config->exists("Lobby") &&
            $config->exists("Spawns") &&
            $config->exists("DMSpawns") &&
            $config->exists("SpectatorSpawn") &&
            $config->exists("Builder") &&
            !$config->get("Finished")
        ) {
            for ($i = 0; $i < $config->get("MaxPlayers"); $i++) {
                if ($config->getNested("Spawns." . ($i + 1)) == null) {
                    //echo "Spawn $i fehlt";
                    return false;
                }
                if ($config->getNested("DMSpawns." . ($i + 1)) == null) {
                    //echo "Spawn $i fehlt";
                    return false;
                }
            }
            $config->set("Finished", true);
            $config->save();

            $lobbymap = $config->getNested("Lobby.Welt");
            $arenamap = $config->getNested("Spawns.1.Welt");
            $dmmap = $config->getNested("DMSpawns.1.Welt");

            self::copymap(Server::getInstance()->getDataPath() . "worlds/" . $lobbymap, self::$pfad . "EnderGames/Maps/" . $lobbymap);
            self::copymap(Server::getInstance()->getDataPath() . "worlds/" . $arenamap, self::$pfad . "EnderGames/Maps/" . $arenamap);
            self::copymap(Server::getInstance()->getDataPath() . "worlds/" . $dmmap, self::$pfad . "EnderGames/Maps/" . $dmmap);

            return true;
        }
        return false;
    }

    public static function createArena($arenaname, $maxplayers)
    {
        $config = new Config(self::$pfad . "EnderGames/Arenas/" . $arenaname . ".yml", Config::YAML);
        $config->set("ArenaName", $arenaname);
        $config->set("MaxPlayers", $maxplayers);
        $config->set("Finished", false);
        $config->save();
    }

    public static function setDMSpawn(Player $player, $arenaname, $spawnid)
    {
        $config = new Config(self::$pfad . "EnderGames/Arenas/" . $arenaname . ".yml", Config::YAML);
        $config->setNested("DMSpawns." . $spawnid . ".Welt", $player->getLevel()->getFolderName());
        $config->setNested("DMSpawns." . $spawnid . ".X", $player->getX());
        $config->setNested("DMSpawns." . $spawnid . ".Y", $player->getY());
        $config->setNested("DMSpawns." . $spawnid . ".Z", $player->getZ());
        $config->setNested("DMSpawns." . $spawnid . ".Yaw", $player->getYaw());
        $config->setNested("DMSpawns." . $spawnid . ".Pitch", $player->getPitch());
        $config->save();
    }

    public static function setSpectatorSpawn(Player $player, $arenaname)
    {
        $config = new Config(self::$pfad . "EnderGames/Arenas/" . $arenaname . ".yml", Config::YAML);
        $config->setNested("SpectatorSpawn.Welt", $player->getLevel()->getFolderName());
        $config->setNested("SpectatorSpawn.X", $player->getX());
        $config->setNested("SpectatorSpawn.Y", $player->getY());
        $config->setNested("SpectatorSpawn.Z", $player->getZ());
        $config->setNested("SpectatorSpawn.Yaw", $player->getYaw());
        $config->setNested("SpectatorSpawn.Pitch", $player->getPitch());
        $config->save();
    }

    public static function setSpawn(Player $player, $arenaname, $spawnid)
    {
        $config = new Config(self::$pfad . "EnderGames/Arenas/" . $arenaname . ".yml", Config::YAML);
        $config->setNested("Spawns." . $spawnid . ".Welt", $player->getLevel()->getFolderName());
        $config->setNested("Spawns." . $spawnid . ".X", $player->getX());
        $config->setNested("Spawns." . $spawnid . ".Y", $player->getY());
        $config->setNested("Spawns." . $spawnid . ".Z", $player->getZ());
        $config->setNested("Spawns." . $spawnid . ".Yaw", $player->getYaw());
        $config->setNested("Spawns." . $spawnid . ".Pitch", $player->getPitch());
        $config->save();
    }

    public static function setBuilder($arenaname, $builder)
    {
        $config = new Config(self::$pfad . "EnderGames/Arenas/" . $arenaname . ".yml", Config::YAML);
        $config->setNested("Builder", $builder);
        $config->save();
    }

    public static function setLobby(Player $player, $arenaname)
    {
        $config = new Config(self::$pfad . "EnderGames/Arenas/" . $arenaname . ".yml", Config::YAML);
        $config->setNested("Lobby.Welt", $player->getLevel()->getFolderName());
        $config->setNested("Lobby.X", $player->getX());
        $config->setNested("Lobby.Y", $player->getY());
        $config->setNested("Lobby.Z", $player->getZ());
        $config->setNested("Lobby.Yaw", $player->getYaw());
        $config->setNested("Lobby.Pitch", $player->getPitch());
        $config->save();
    }
}