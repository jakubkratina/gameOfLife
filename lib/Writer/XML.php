<?php

class XML implements IWriter
{
    const FILENAME = 'output.xml';

    /** @var World */
    private $world;

    /**
     * Save output to XML file.
	 *
     * @return $this
     */
    public function save()
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF8"?><life></life>');
        $this->toXML($this->getArray(), $xml);

        $DOM = new DOMDocument;
        $DOM->preserveWhiteSpace = FALSE;
        $DOM->formatOutput = TRUE;
        $DOM->loadXML($xml->asXML());
        $DOM->save(self::FILENAME);

        return $this;
    }

    /**
     * Force output file download.
     */
    public function download()
    {
        header('Content-type: text/xml; charset=utf8');
        header('Content-disposition: attachment; filename=' . self::FILENAME);
        readfile(self::FILENAME);
    }

	/**
	 * Set world info.
	 *
	 * @param World $world
	 */
	public function set(World $world)
	{
		$this->world = $world;
	}

    /**
     * Return array for XML output.
	 *
     * @return array
     */
    private function getArray()
    {
        $array = [
            'world' => [
                'cells' => $this->world->getCells(),
                'iterations' => $this->world->getIterations()
            ],
            'organisms' => []
        ];

        foreach ($this->world->getLife() as $life) {
            foreach ($life as $organism) {

                if ($organism->isDead())
                    continue;

                array_push($array['organisms'], [
                    'x_pos' => $organism->x,
                    'y_pos' => $organism->y,
                    'species' => $organism->species
                ]);
            }
        }

        return $array;
    }

    /**
     * Add children to the SimpleXMLElement.
	 *
     * @param $array
     * @param $xml
     */
    private function toXML($array, &$xml)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'organism';
                }

                $node = $xml->addChild($key);
                $this->toXML($value, $node);

            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }
}