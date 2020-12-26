<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\event\member;

use fpe\entity\IMember;
use pocketmine\event\Event;

abstract class MemberEvent extends Event
{

    /** @var IMember */
    protected $member;

    public function __construct(IMember $member)
    {
        $this->member = $member;
    }

    public function getMember()
    {
        return $this->member;
    }

}