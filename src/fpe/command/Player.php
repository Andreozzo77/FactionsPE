<?php

namespace fpe\command;

use facitons\manager\Members;
use fpe\command\parameter\MemberParameter;
use fpe\dominate\Command;
use fpe\interfaces\IFPlayer;
use fpe\localizer\Localizer;
use fpe\utils\Text;
use pocketmine\command\CommandSender;

class Player extends Command
{

    public function setup()
    {
        $this->addParameter((new MemberParameter("player", MemberParameter::ANY_MEMBER))->setDefaultValue("self"));
    }

    public function perform(CommandSender $sender, $label, array $args)
    {
        $member = $this->getArgument(0);

        // INFO: Power (as progress bar)
        $progressbarQuota = 0;
        $playerPowerMax = $member->getPowerMax();

        if ($playerPowerMax != 0) {
            $progressbarQuota = $member->getPower() / $playerPowerMax;
        }

        # TODO: Calculate progress bar width
        //$sender->sendMessage(Localizer::translatable("player-power-progress-bar", [(new ProgressBar(ProgressBar::HEALTH_BAR_CLASSIC, $progressbarQuota, 10))->setColor(TextFormat::DARK_PURPLE)->render()]));
        // INFO: Power (as digits)
        $sender->sendMessage(Localizer::translatable("player-power", [$member->getPower(), $member->getPowerMax()]));
        $sender->sendMessage(Text::parse("<gold>Rank: <h>" . $member->getRole()));
        $sender->sendMessage(Text::parse("<gold>Faction: <h>" . ($member->hasFaction() ? $member->getFaction()->getName() : "none")));
        $sender->sendMessage(Text::parse("<gold>Last online: <h>" . Text::ago($member->getLastPlayed())));

        // INFO: Power Boost
        if ($member->hasPowerBoost()) {
            $powerBoost = $member->getPowerBoost();
            $powerBoostType = ($powerBoost > 0 ? Localizer::translatable("bonus") : Localizer::translatable("penalty"));
            $sender->sendMessage(Localizer::translatable("player-power-boost", [$powerBoost, $powerBoostType]));
        }
        return true;
    }
}