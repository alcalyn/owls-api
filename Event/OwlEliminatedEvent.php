<?php

namespace Alcalyn\Owls\Event;

use Alcalyn\Owls\Model\Party;
use Alcalyn\Owls\Model\Owl;

class OwlEliminatedEvent extends Event
{
    /**
     * @var Owl
     */
    private $owl;

    /**
     * @param Party $party
     * @param Owl $owl
     */
    public function __construct(Party $party, Owl $owl)
    {
        parent::__construct($party);

        $this->owl = $owl;
    }
    /**
     * @return Party
     */
    public function getOwl()
    {
        return $this->owl;
    }

    /**
     * @param Owl $owl
     *
     * @return self
     */
    public function setOwl(Owl $owl)
    {
        $this->owl = $owl;

        return $this;
    }
}
