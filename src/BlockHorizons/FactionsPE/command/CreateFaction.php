<?php
/*
 *   FactionsPE: PocketMine-MP Plugin
 *   Copyright (C) 2020 BlockHorizons
 */

namespace BlockHorizons\FactionsPE\command;

use BlockHorizons\FactionsPE\data\FactionData;
use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\dominate\parameter\Parameter;
use BlockHorizons\FactionsPE\dominate\requirement\SimpleRequirement;
use BlockHorizons\FactionsPE\command\requirement\FactionRequirement;
use BlockHorizons\FactionsPE\entity\Faction;
use BlockHorizons\FactionsPE\event\faction\FactionCreateEvent;
use BlockHorizons\FactionsPE\event\member\MembershipChangeEvent;
use BlockHorizons\FactionsPE\FactionsPE;
use BlockHorizons\FactionsPE\manager\Factions;
use BlockHorizons\FactionsPE\manager\Members;
use BlockHorizons\FactionsPE\utils\Gameplay;
use BlockHorizons\FactionsPE\localizer\Localizer;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class CreateFaction extends Command {

	public function setup() {
		$this->addParameter(new Parameter("name", Parameter::TYPE_STRING));
		$this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
		$this->addRequirement(new FactionRequirement(FactionRequirement::OUT_FACTION));
	}

	public function perform(CommandSender $sender, $label, array $args): bool{
		$need = 0;
		if (FactionsPE::get()->economyEnabled()) {
			if ($sender instanceof Player) {
				if (($has = FactionsPE::get()->getEconomy()->balance($sender)) < ($need = Gameplay::get('price.faction-creation', 0))) {
					$sender->sendMessage(Localizer::translatable('faction-create-insufficient-fund', [
						"has"  => $has,
						"need" => $need,
					]));
					return true;
				}
			}
		}

		$name = $this->getArgument(0);

		$errors = Faction::validateName($name);
		if (($c = count($errors)) > 0) {
			$sender->sendMessage(Localizer::translatable('invalid-faction-name', [
				"name"  => $name,
				"count" => $c,
			]));
			foreach ($errors as $n => $error) {
				$sender->sendMessage(Localizer::translatable('invalid-faction-name-error', [
					"error" => $error,
					"n"     => $n + 1,
				]));
			}
			return true;
		}

		$fid     = Faction::createId();
		$creator = Members::get($sender);

		$event = new FactionCreateEvent($creator, $fid, $name);
		$this->getPlugin()->getServer()->getPluginManager()->callEvent($event);
		if ($event->isCancelled()) {
			return false;
		}

		$faction = new Faction($fid, new FactionData([
			"name"    => $name,
            "members" => Factions::createMembersList($creator),
            "id"      => $fid
		]));
		Factions::attach($faction);
		$creator->updateFaction();

		$event = new MembershipChangeEvent($creator, $faction, MembershipChangeEvent::REASON_CREATE);
		$this->getPlugin()->getServer()->getPluginManager()->callEvent($event);
		// Ignore cancellation

		if (FactionsPE::get()->economyEnabled()) {
			if ($sender instanceof Player) {
				FactionsPE::get()->getEconomy()->takeMoney($sender, $need);
			}
		}

		$sender->sendMessage(Localizer::translatable('faction-created', compact("name")));
		if (Gameplay::get('log.faction-creation', true)) {
			FactionsPE::get()->getLogger()->info(Localizer::trans('log.member-created-faction', [
				$creator->getName(),
				$faction->getName(),
			]));
		}
		try {
			$faction->save();
		} catch (\Exception $e) {
			// Ignore :D
		}

		return true;
	}

	public function createForm(Player $player) {
		$fapi = $this->getPlugin()->getFormAPI();
		$form = $fapi->createCustomForm(function (Player $player, array $data) {
			$result = $data[1];
			if ($result !== null) {
				if ($this->execute($player, "", [$result])) {
					$this->getParent()->getFormHandler()->descriptionHandler($player);
				}
			}
		});

		$form->setTitle(Localizer::trans("create-faction-form-title"));
		$form->addLabel(Localizer::trans("create-faction-form-content"));
		$form->addInput(Localizer::trans(""));
		$form->sendToPlayer($player);
	}

}
