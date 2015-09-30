<?php

/**
 * 0 or 1       - die (isolation)
 * 2 or 3       - survive
 * 3            - birth
 * 4 or more    - die (overcrowding)
 */
class Organism
{
    CONST ISOLATION = 2,
        BIRTHABLE = 3,
        OVERCROWDING = 4;

	const DEAD = 0;

	static $colors = [
		'black', 'yellow', 'blue', 'red', 'orange'
	];

    /** @var int X position */
    public $x;

    /** @var int Y position */
    public $y;

    /** @var int Species number */
    public $species;

    /** @var array of surroundings Organisms */
    private $surroundings = [];

    public function __construct($x, $y, $species = NULL)
    {
        $this->x = (int)$x;
        $this->y = (int)$y;

        if (!is_null($species))
            $this->species = $species;
    }

    /**
     * Check what to do with Organism and return operation result.
	 *
     * @param $life
     * @return array
     */
    public function whatShouldIDo($life)
    {
        // Set surroundings array.
        $this->getSurroundings($life);

        // Check birth.
        if ($this->isDead() && $parent = $this->isBirthAble()) {
            return ['result' => Result::BIRTH, 'parent' => $parent];
        }

        // Check isolation.
        if (!$this->isDead() && $this->isIsolated()) {
            return ['result' => Result::ISOLATED];
        }

        // Check overcrowding.
        if (!$this->isDead() && $this->isOvercrowded()) {
            return ['result' => Result::OVERCROWDED];
        }
    }

    /**
     * Check if two Organisms species are equal.
	 *
     * @param Organism $organism
     * @return bool
     */
    public function equals(Organism $organism)
    {
        return $this->species === $organism->getSpecies();
    }

    /**
     * Return true if Organism is dead.
	 *
     * @return bool
     */
    public function isDead()
    {
        return $this->species === self::DEAD;
    }

    /**
     * Create new Organism and return him.
	 *
     * @param int $x
     * @param int $y
     * @param int $species
     * @return Organism
     */
    public static function create($x, $y, $species)
    {
        $organism = new Organism((int)$x, (int)$y, (int)$species);
        return $organism;
    }

    /**
     * Check if the Organism is isolated.
	 *
     * @return bool
     */
    private function isIsolated()
    {
        return !isset($this->surroundings[$this->species]) || $this->surroundings[$this->species] < self::ISOLATION;
    }

    /**
     * Check if the Organism is overcrowded.
	 *
     * @return bool
     */
    private function isOvercrowded()
    {
        return isset($this->surroundings[$this->species]) && $this->surroundings[$this->species] >= self::OVERCROWDING;
    }

    /**
     * Check if can give a birth into the Organism,
     * if can, returns random parent's Organism species ID.
	 *
     * @return int
     */
    private function isBirthAble()
    {
        $parents = [];

        foreach ($this->surroundings as $species => $surrounding) {
            if ($surrounding == self::BIRTHABLE)
                array_push($parents, $species);
        }

        if (count($parents) == 0)
            return FALSE;

        return $parents[array_rand($parents)];
    }

	/**
	 * Return parents if dead organism can be birthable
	 *
	 * @param $x
	 * @param $y
	 * @param $life
	 * @return int
	 */
	public static function canBeBirthable($x, $y, $life)
	{
		$organism = new Organism($x, $y);

		$organism->getSurroundings($life);

		if($parent = $organism->isBirthAble()) {
			return ['result' => Result::BIRTH, 'parent' => $parent];
		}

		return FALSE;
	}

    /**
     * Set array of surrounding Organisms at current iteration.
	 *
	 * @param array $life
     */
    private function getSurroundings($life)
    {
        $this->surroundings = [];

        // Iterate through Organism's surrounding Organism.
        for ($i = -1; $i <= 1; $i++) {
            for ($j = -1; $j <= 1; $j++) {

                // Middle organism, skip
                if ($i == 0 && $j == 0)
                    continue;

				// Check borders or not existing elements
				if (!isset($life[$this->x + $i][$this->y + $j]))
					continue;

                // Get controlled surroundings organism.
                $organism = $life[$this->x + $i][$this->y + $j];

                if (isset($this->surroundings[$organism->species])) {
                    $this->surroundings[$organism->species]++;

                } else {
                    $this->surroundings[$organism->species] = 1;
                }
            }
        }
    }
}