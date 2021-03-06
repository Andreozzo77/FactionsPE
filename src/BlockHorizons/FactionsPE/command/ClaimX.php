<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\command\parameter\FactionParameter;
use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\manager\Members;
use BlockHorizons\FactionsPE\manager\Plots;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\Player;

abstract class ClaimX extends Command
{

    /** @var bool */
    private $claim = true;

    /** @var int */
    private $factionArgIndex = 0;

    public function setup()
    {
        $this->addParameter((new FactionParameter("faction"))->setDefaultValue("me"));
    }

    public function isClaim(): bool
    {
        return $this->claim;
    }

    public function setClaim(bool $claim)
    {
        $this->claim = $claim;
        return $this;
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        if (!$sender instanceof Player) return false;
        $this->sender = $sender;

        // Args
        $newFaction = $this->getArgument($this->getFactionArgIndex());

        $plots = $this->getPlots($sender);
        // Apply / Inform

        if ($this->claim) {
            Plots::tryClaim($newFaction, Members::get($sender), $plots);
        } else {
            $msender = Members::get($sender);
            foreach ($plots as $plot) {
                $plot->unclaim($msender);
            }
        }

        $this->sender = null;
        return true;
    }

    public function getFactionArgIndex(): int
    {
        return $this->factionArgIndex;
    }

    public function setFactionArgIndex(int $factionArgIndex)
    {
        $this->factionArgIndex = $factionArgIndex;
    }

    /**
     * @param Position $pos
     * @return \BlockHorizons\FactionsPE\entity\Plot[]
     */
    public abstract function getPlots(Position $pos): array;


}