<?php

namespace BlockHorizons\FactionsPE\command;

use Exception;
use BlockHorizons\FactionsPE\dominate\Command;
use BlockHorizons\FactionsPE\dominate\parameter\Parameter;
use BlockHorizons\FactionsPE\dominate\requirement\SimpleRequirement;
use BlockHorizons\FactionsPE\engine\SeeChunkEngine;
use BlockHorizons\FactionsPE\localizer\Localizer;
use BlockHorizons\FactionsPE\manager\Members;
use BlockHorizons\FactionsPE\manager\Plots;
use BlockHorizons\FactionsPE\utils\Text;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector2;

class SeeChunk extends Command
{

    function setup()
    {
        //$this->addAliases("sc");

        $this->addParameter(new Parameter("active", Parameter::TYPE_BOOLEAN, true));

        $this->addRequirement(new SimpleRequirement(SimpleRequirement::PLAYER));
    }

    function perform(CommandSender $sender, $label, array $args)
    {
        $member = Members::get($sender);
        $old = $member->isSeeingChunk();
        $target = $this->getParameterAt(0)->getValue(!$old);

        // Detect no change
        if ($old === $target) {
            $member->sendMessage(Localizer::translatable('seeing-chunk-no-change'));
            return true;
        } else {
            $member->setSeeChunk($target);
        }

        /** @var SeeChunkEngine $engine */
        /** @var \pocketmine\Player $sender */
        $engine = $this->getPlugin()->getEngine("SeeChunkEngine");
        try {
            if ($target) {

                $engine->setChunk($member, new Vector2(
                    $sender->getX() >> Plots::$CHUNK_SIZE,
                    $sender->getZ() >> Plots::$CHUNK_SIZE
                ), $sender->getLevel());

            } else {
                $engine->removeChunk($member);
            }
        } catch (Exception $e) {
            $member->sendMessage(Text::parse("<red>Internal error!"));
            return true;
        }

        $member->sendMessage(Localizer::translatable('seeing-chunk-' . ($target ? 'activated' : 'deactivated')));
        return true;
    }

}