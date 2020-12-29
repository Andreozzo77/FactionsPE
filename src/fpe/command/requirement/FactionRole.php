<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace fpe\command\requirement;

use fpe\dominate\requirement\Requirement;
use fpe\localizer\Translatable;
use fpe\manager\Members;
use pocketmine\command\CommandSender;

class FactionRole extends Requirement
{

    /** @var string */
    protected $role;

    /**
     * @var string
     */
    protected $type;

    public function __construct(string $role)
    {
        $this->role = $role;
        $this->type = self::class;
    }

    public function hasMet(CommandSender $sender, $silent = false): bool
    {
        return Members::get($sender)->getRole() === $this->role;
    }

    public function createErrorMessage(CommandSender $sender = null): Translatable
    {
        return new Translatable("requirement.role-error", [
            "role" => $this->role
        ]);
    }

}