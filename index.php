<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
<?php

/**
 * This is an example of Convaw's Game of Life at PHP (https://en.wikipedia.org/wiki/Conway%27s_Game_of_Life)
 *
 * There is no need for use some framework.
 * This example shows analytics, OOP, PHP, programming and English skills.
 *
 * For this example index is shortened (functions, css, php, html). In real situation it will be in separated files.
 *
 * For website example look at http://www.tartaletka.cz/ (my girlfriend's website created by me).
 */

require_once 'lib/Writer/IWriter.php';
require_once 'lib/Writer/XML.php';

function __autoload($class)
{
	require_once 'lib/' . $class . '.php';
}

$world = new World(new XML());
$life = $world->simulate();

?>

<style type="text/css">
	table {
		border-collapse: collapse;
	}

	table td {
		width: 10px;
		height: 10px;
		text-align: center;
		border: 1px solid #eeeeee;
	}
</style>

<div id="life"></div>

<script type="text/javascript">
	(function ($) {

		$.fn.life = function () {

			var _this = $(this);
			var simulation = <?=$life?>;

			$.each(simulation.life, function (iteration, life) {
				setTimeout(function () {
					var table = $('<table>');

					for (var i = -2; i < simulation.cells; i++) {
						var row = $('<tr>');

						for (var j = -1; j < simulation.cells; j++) {
							var cell = $('<td>');

							if (life[i + 1] !== undefined && life[i + 1][j + 1] !== undefined) {
								var organism = life[i + 1][j + 1];
								cell.css('background', organism.color);
							}

							row.append(cell);
						}

						table.append(row);
					}

					_this.html(table);
				}, 500 + (iteration * 500));
			});

			return this;

		};

	}(jQuery));

	// Usage example:
	$("#life").life();
</script>
