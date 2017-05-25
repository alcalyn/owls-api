<?php

namespace Alcalyn\Owls\Model;

class Card
{
    /**
     * Normal card with a color and a number.
     *
     * @var int
     */
    const TYPE_NORMAL = 0;

    /**
     * Remove current card and allows to replay.
     *
     * @var int
     */
    const TYPE_REMOVE = 1;

    /**
     * Set number zero on choosen color.
     *
     * @var int
     */
    const TYPE_ZERO = 2;

    /**
     * Randomize all current numbers.
     *
     * @var int
     */
    const TYPE_RANDOMIZE = 3;

    /**
     * Random number for a normal card,
     * number is set randomly when card played.
     *
     * @var int
     */
    const NUMBER_RANDOM = 0;

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $type;

    /**
     * @var int
     */
    private $color;

    /**
     * @var int
     */
    private $number;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->type = self::TYPE_NORMAL;
        $this->color = 0;
        $this->number = 0;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param int $color
     *
     * @return self;
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param int $number
     *
     * @return self;
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }
}
