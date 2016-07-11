<?php
if(array_key_exists('input', $_GET))
{
	require('include/static/champions.php');
	require('include/roledetect.php');
	$input = json_decode($_GET['input'], TRUE);
	$players = [];
	foreach($input as $k=>$player)
	{
		$roles = [];
		foreach(getChampionRoles($player['champion']) as $k=>$v)
		{
			$role = getRole($v);
			if($role)
				array_push($roles, $role);
		}
		$id = count($players) + 1;
		$players[$id] = new Player($id, $player['champion'], getChampionKey($player['champion']), $roles, getSpell($player['spell1']), getSpell($player['spell2']));
	}
	if(count($players) == 5)
	{
		$solved = resolve($players);//[Position:[{player:Player, score:Int}]]
		echo '<h1>Final summary</h1>';
		foreach(reducedSolvedToSimplest(reduceSolved($solved)) as $lane=>$playerId)
		{
			echo $lane . ': ' . $players[$playerId]->toHTML() . '<br>';
		}
		echo '<h1>Final guesses details</h1>';
		foreach($solved as $posId=>$guesses)
		{
			echo '<h2>' . getPosition($posId)->toHTML() . '</h2>';
			foreach($guesses as $guess)
			{
				echo '<span style="display: inline-block;width:20px;height:20px;"> </span>' . $guess->player->toHTML();
				echo ' (score for ' . getPosition($posId)->name . ' lane: ' . $guess->score . ')<br>';
			}
		}
	}
	else
		echo 'Exactly 5 players are not provided';
}
?>