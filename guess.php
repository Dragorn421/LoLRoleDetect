<?php
if(array_key_exists('input', $_GET))
{
	// We need to include champions data before using roledetect
	require('include/static/champions.php');
	require('include/roledetect.php');
	$input = json_decode($_GET['input'], TRUE);
	// create the players array
	$players = [];
	foreach($input as $k=>$player)
	{
		// get champion roles
		$roles = [];
		foreach(getChampionRoles($player['champion']) as $k=>$v)
		{
			$role = getRole($v);
			if($role)// make sure this role exist
				array_push($roles, $role);
		}
		// because this is a test, players have no summoner id
		$id = count($players) + 1;
		// the key can be anything, not necessarily summoner id
		$players[$id] = new Player(	$id,
									$player['champion'],				// champion id
									getChampionKey($player['champion']),// because this is a test, using champion key as name for the player
									$roles,
									getSpell($player['spell1']),
									getSpell($player['spell2']));
	}
	// check we have 5 players, does not work if not
	if(count($players) == 5)
	{
		// resolve the whole team: returns all scores
		$solved = resolve($players);//[Position:[{player:Player, score:Int}]]
		// show only top guesses (most likely usage)
		echo '<h1>Final summary</h1>';
		foreach(reducedSolvedToSimplest(reduceSolved($solved)) as $lane=>$playerId)
		{
			echo $lane . ': ' . $players[$playerId]->toHTML() . '<br>';
		}
		// show all scores (more for debug)
		echo '<h1>Final guesses details</h1>';
		foreach($solved as $posId=>$guesses)
		{
			echo '<h2>' . getPosition($posId)->toHTML() . '</h2>';
			foreach($guesses as $guess)
			{
				echo '<span style="display: inline-block;width:20px;height:20px;"> </span>'	// this is a dirty way to indent the text
						. $guess->player->toHTML()
						. ' (score for ' . getPosition($posId)->name . ' lane: ' . $guess->score . ')<br>';
			}
		}
	}
	else
		echo 'Exactly 5 players are not provided';
}
?>