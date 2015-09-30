<?php

class World {

	/** @var int Dimensions for the World */
	private $cells;

	/** @var int Number of iterations */
	private $iterations;

	/** @var array of Organism */
	private $life = [];

	/** @var array of operations for single iteration */
	private $operations = [];

	/** @var bool If TRUE prints the World for each iteration */
	private $table = false;

	private $simulation = [];

	/** @var IWriter */
	private $writer;

	public function __construct(IWriter $writer, $file = __DIR__ . '/../input.xml')
	{
		$xml = @simplexml_load_file($file);
		if (!$xml)
			throw new InputFileNotFoundException;

		$this->setCells($xml->world->cells);
		$this->setIterations($xml->world->iterations);
		$this->setDefaults($xml);

		$this->writer = $writer;
	}

	/**
	 * Create default World's Organisms defined in XML input file.
	 *
	 * @param $xml
	 * @throws UnknownSpeciesID
	 */
	public function setDefaults($xml)
	{
		foreach ($xml->organisms->organism as $row)
		{
			$organism = new Organism (
				$row->x_pos,
				$row->y_pos,
				(int) $row->species
			);

			$this->addOrganismToSimulation($organism, 0);

			$this->life[$organism->x][$organism->y] = $organism;
		}
	}

	/**
	 * Iterate through each Organism in the World, created as a 2D array.
	 * Check surroundings of each Organism and do some operations.
	 *
	 * @return $this
	 */
	public function live()
	{
		if ($this->table)
			$this->table();

		for ($i = 0; $i < $this->iterations; $i++)
		{
			$this->operations = [];

			for ($x = 0; $x < $this->cells; $x++)
			{
				for ($y = 0; $y < $this->cells; $y++)
				{
					// Calculate operation
					$operation = !isset($this->life[$x][$y])
						? Organism::canBeBirthable($x, $y, $this->life)
						: $this->life[$x][$y]->whatShouldIDo($this->life);

					if ($operation)
					{
						$this->operations[] = array_merge($operation, [
							'x'         => $x,
							'y'         => $y
						]);
					}
				}
			}

			// After iteration is done, process all operations.
			$this->process();

			// Add current iteration life to output for simulation
			$this->addOrganismsToSimulation($i);

			if ($this->table)
				$this->table($i);
		}

		return $this;
	}

	/**
	 * Run world's simulation and return result for javascript drawing.
	 *
	 * @return string
	 */
	public function simulate()
	{
		$this->live();

		return json_encode([
			'life'  => $this->simulation,
			'cells' => $this->cells
		]);
	}

	/**
	 * Save the World.
	 *
	 * @return \XML
	 */
	public function save()
	{
		$this->writer->set($this);

		return $this->writer->save();
	}

	/**
	 * Set the World cells.
	 *
	 * @param int $cells
	 * @return $this
	 */
	public function setCells($cells)
	{
		$this->cells = (int) $cells;

		return $this;
	}

	/**
	 * Set number of the iterations.
	 *
	 * @param int $iterations
	 * @return $this
	 */
	public function setIterations($iterations)
	{
		$this->iterations = (int) $iterations;

		return $this;
	}

	/**
	 * Print the World for each iteration.
	 *
	 * @return $this
	 */
	public function show()
	{
		$this->table = true;

		return $this;
	}

	/**
	 * Return the World's life.
	 *
	 * @return array
	 */
	public function getLife()
	{
		return $this->life;
	}

	/**
	 * Return cells matrix value.
	 *
	 * @return int
	 */
	public function getCells()
	{
		return $this->cells;
	}

	/**
	 * Return number of iterations.
	 *
	 * @return int
	 */
	public function getIterations()
	{
		return $this->iterations;
	}

	/**
	 * Print the World as a table in a current iteration.
	 *
	 * @param null|int $iteration
	 * @param bool $position Show x, y position
	 */
	private function table($iteration = null, $position = false)
	{
		if (!is_null($iteration))
			echo "<h1> Result of iteration " . ($iteration + 1) . "</h1>";
		else
			echo "<h1>Default World</h1>";

		echo '<style type="text/css">table {border-collapse: collapse;}table td {width:10px;height:10px;text-align: center;border: 1px solid #eeeeee;}</style>';

		echo "<table>";
		for ($y = 0; $y < $this->cells; $y++)
		{
			echo "<tr>";

			for ($x = 0; $x < $this->cells; $x++)
			{

				$color = isset($this->life[$x][$y])
					? Organism::$colors[$this->life[$x][$y]->species]
					: 'white';

				echo "<td style='background-color: " . $color . "'>";

				if ($position)
					echo "{$x},&nbsp;{$y}";

				echo "</td>";
			}

			echo "</tr>";
		}
		echo "</table>";
	}

	/**
	 * Process all iteration operations and update current life.
	 */
	private function process()
	{
		foreach ($this->operations as $operation)
		{
			switch ($operation['result'])
			{
				case Result::ISOLATED:
					unset($this->life[$operation['x']][$operation['y']]);
					break;

				case Result::OVERCROWDED:
					unset($this->life[$operation['x']][$operation['y']]);
					break;

				case Result::BIRTH;
					if (!isset($this->life[$operation['x']][$operation['y']]))
					{
						$this->life[$operation['x']][$operation['y']] = new Organism (
							$operation['x'],
							$operation['y'],
							(int) $operation['parent']
						);
					}
					else
					{
						$this->life[$operation['x']][$operation['y']]->species = $operation['parent'];
					}

					break;
			}
		}
	}

	/**
	 * Iterate through current life and add organisms to simulation output array.
	 *
	 * @param int $iteration
	 */
	private function addOrganismsToSimulation($iteration)
	{
		for ($x = 0; $x < $this->cells; $x++)
		{
			for ($y = 0; $y < $this->cells; $y++)
			{
				if (isset($this->life[$x][$y]))
				{
					$this->addOrganismToSimulation($this->life[$x][$y], $iteration + 1);
				}
			}
		}

	}

	/**
	 * Add single organism to iteration output simulation array.
	 *
	 * @param Organism $organism
	 * @param $iteration
	 */
	private function addOrganismToSimulation(Organism $organism, $iteration)
	{
		$this->simulation[$iteration][$organism->x][$organism->y] = [
			'x'       => $organism->x,
			'y'       => $organism->y,
			'species' => $organism->species,
			'color'   => Organism::$colors[$organism->species]
		];
	}
}

class InputFileNotFoundException extends Exception {

}

class UnknownSpeciesID extends Exception {

}