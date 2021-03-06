<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\task;

use BlockHorizons\FactionsPE\entity\Member;
use BlockHorizons\FactionsPE\localizer\Localizer;
use BlockHorizons\FactionsPE\manager\Members;
use BlockHorizons\FactionsPE\utils\Gameplay;
use pocketmine\scheduler\Task;

class PowerUpdateTask extends Task
{

    public function onRun(int $currentTick)
    {
        foreach (Members::getAllOnline() as $member) {
            // if($member->isNone()) continue;
            if (!$member instanceof Member) continue;
            if ($member->getPlayer()->isAlive() === false) continue;
            $newPower = $this->calculatePower($member);
            if ($newPower > $member->getPower()) {
                $member->getPlayer()->sendTip(Localizer::trans("power-regen-hud", [
                    "newPower" => $newPower,
                    "diff" => ($newPower - $member->getPower())
                ]));
                $member->setPower($this->calculatePower($member));
            }
        }
    }

    /**
     * Returns new power level
     */
    public function calculatePower(Member $member): int
    {
        if ($member->getPower() === $member->getPowerMax()) return $member->getPower();
        return $member->getPower() + Gameplay::get("power.player.per-update", 1);
    }

}

