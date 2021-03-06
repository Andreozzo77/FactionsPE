<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\dominate\parameter\Parameter;
use BlockHorizons\FactionsPE\dominate\requirement\SimpleRequirement;
use BlockHorizons\FactionsPE\localizer\Localizer;
use BlockHorizons\FactionsPE\manager\Members;
use BlockHorizons\FactionsPE\utils\Collections;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class Map extends Command
{

    public function setup()
    {

        $this->addParameter((new Parameter("auto-update", Parameter::TYPE_BOOLEAN))->setDefaultValue(null));

        $this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        if (!$sender instanceof Player) return false;
        if (isset($args[0])) {
            $val = $args[0];
            $fsender = Members::get($sender);
            if ($val) {
                $fsender->setMapAutoUpdating(true);
                $fsender->sendMessage(Localizer::translatable("map-auto-update-enabled"));
            } else {
                if ($fsender->isMapAutoUpdating()) {
                    $fsender->setMapAutoUpdating(false);
                    $sender->sendMessage(Localizer::translatable("map-auto-update-disabled"));
                }
            }
            return true;
        }

        /** @var Player $sender $map */
        $map = Collections::getMap($sender, Collections::MAP_WIDTH, Collections::MAP_HEIGHT, $sender->getYaw());
        foreach ($map as $line) {
            $sender->sendMessage($line);
        }
        return true;
    }

}