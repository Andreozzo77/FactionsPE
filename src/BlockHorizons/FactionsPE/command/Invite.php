<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\command\requirement\FactionPermission;
use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\dominate\parameter\Parameter;
use BlockHorizons\FactionsPE\dominate\requirement\SimpleRequirement;
use BlockHorizons\FactionsPE\FactionsPE;
use BlockHorizons\FactionsPE\localizer\Localizer;
use BlockHorizons\FactionsPE\manager\Permissions;
use BlockHorizons\FactionsPE\permission\Permission;
use pocketmine\Player;

class Invite extends Command
{

    public function __construct(FactionsPE $plugin, string $name, string $description, string $permission, array $aliases)
    {
        parent::__construct($plugin, $name, $description, $permission, $aliases);

        $this->addChild(new InviteAdd($plugin, "add", "Invite new member to your faction", Permissions::INVITE_ADD, ["invite"]));
        $this->addChild(new InviteList($plugin, "list", "List all active invitations for faction", Permissions::INVITE_LIST, ["ls"]));
        $this->addChild(new InviteRemove($plugin, "remove", "Delete an invitation", Permissions::INVITE_REMOVE, ["delete"]));

        $this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
        $this->addRequirement(new FactionPermission(Permissions::getById(Permission::INVITE)));

        $this->addParameter(new Parameter("add|remove|list"));
    }

    /**
     * TODO: Only invite?
     */
    public function inviteForm(Player $player)
    {
        $fapi = $this->getPlugin()->getFormAPI();
        $form = $fapi->createCustomForm(function (Player $player, array $data) {
            $result = $data[0] ?? $data[1];

            if ($result !== null) {
                $this->execute($player, "", ["add", $result]);
            }

        });
        $options = array_values(array_map(function ($player) {
            return $player->getName();
        }, $this->getPlugin()->getServer()->getOnlinePlayers()));
        $form->setTitle(Localizer::trans("invite-form-title"));
        $form->addLabel(Localizer::trans("invite-form-content"));
        $form->addInput(Localizer::trans("invite-form-input"));
        $form->addLabel(Localizer::trans("invite-input-label"));
        $form->addDropdown(Localizer::trans("select-player"), $options);
        $form->sendToPlayer($player);
    }

}