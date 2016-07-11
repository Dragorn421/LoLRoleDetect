<?php
class Position
{

	public $id;
	public $name;

	public function __construct($id, $name)
	{
		$this->id = $id;
		$this->name = $name;
	}

	public function __toString()
	{
		return 'Position{id=' . $this->id . ',name=' . $this->name . '}';
	}

	public function toHTML()
	{
		return '<span class="position-' . strtolower($this->name) . '">' . $this->name . '</span>';
	}

}

function getPositions()
{
	static $positions = NULL;
	if(!$positions)
	{
		$positions = [	new Position(1, 'Top'),
						new Position(2, 'Jungle'),
						new Position(3, 'Mid'),
						new Position(4, 'Adc'),
						new Position(5, 'Sup')];
		
	}
	return $positions;
}

function getPosition($id)
{
	static $positionssById = NULL;
	if(!$positionssById)
	{
		$positions = getPositions();
		$positionssById = [];
		foreach($positions as $k=>$position)
			$positionssById[$position->id] = $position;
	}
	return $positionssById[$id];
}

function getPositionsByName()
{
	static $positionsByName = NULL;
	if(!$positionsByName)
	{
		$positions = getPositions();
		$positionsByName = [];
		foreach($positions as $k=>$pos)
		{
			$positionsByName[strtoupper($pos->name)] = $pos;
		}
	}
	return $positionsByName;
}

class Role
{

	public $id;
	public $name;
	public $positions;

	public function __construct($id, $name, $positions)
	{
		$this->id = $id;
		$this->name = $name;
		$this->positions = $positions;
	}

	public function __toString()
	{
		return 'Role{id=' . $this->id . ',positions=' . json_encode($this->positions) . '}';
	}

	public function toHTML()
	{
		return '<span class="role-' . strtolower($this->name) . '" title="Lanes: '
			. implode(array_map(function($e){
					return $e->name;
				}, $this->positions))
			. '">' . $this->name . '</span>';
	}

}

function getRole($name)
{
	static $roles = NULL;
	if(!$roles)
	{
		$positions = getPositionsByName();
		$roles = [];
		$roles['assassin'] =new Role(1, 'assassin',	[$positions['MID'], $positions['JUNGLE']]);
		$roles['fighter'] =	new Role(2, 'fighter',	[$positions['TOP'], $positions['JUNGLE']]);
		$roles['mage'] =	new Role(3, 'mage',		[$positions['MID'], $positions['SUP']]);
		$roles['marksman'] =new Role(4, 'marksman',	[$positions['ADC']]);
		$roles['melee'] =	$roles['fighter'];
		$roles['support'] =	new Role(5, 'support',	[$positions['SUP'], $positions['MID']]);
		$roles['tank'] =	new Role(6, 'tank',		[$positions['TOP'], $positions['JUNGLE'], $positions['SUP']]);
	}
	return $roles[strtolower($name)];
}

class Spell
{

	public $id;
	public $name;
	public $positions;
	public $scores;

	public function __construct($id, $name, $positions, $scores)
	{
		$this->id = $id;
		$this->name = $name;
		$this->positions = $positions;
		$this->scores = $scores;
	}

	public function __toString()
	{
		return 'Spell{id=' . $this->id . ',positions=' . json_encode($this->positions) . ',scores=' . json_encode($this->scores) . '}';
	}

	public function toHTML()
	{
		return '<span class="spell-' . strtolower($this->name) . '" title="Lanes: '
			. implode(',', array_map(function($e){
					return $e->name;
				}, $this->positions))
			. '">' . $this->name . '</span>';
	}

}

function getSpells()
{
	static $spells = NULL;
	if(!$spells)
	{
		$positions = getPositionsByName();
		$spells = [	new Spell(21, 'barrier', [$positions['MID']], [10]),
					new Spell(13, 'clarity', [$positions['MID']], [10]),
					new Spell(01, 'cleanse', [$positions['MID']], [10]),
					new Spell(03, 'exhaust', [$positions['SUP']], [10]),
					new Spell(04, 'flash', [], [], []),		// no lane everyone takes it
					new Spell(06, 'ghost', [$positions['TOP'], $positions['MID']], [5, 4]),//TODO unsure, mid or top first? + ghost may replace flash
					new Spell(07, 'heal', [$positions['ADC'], $positions['MID']], [4, 4]),
					new Spell(14, 'ignite', [$positions['MID'], $positions['TOP'], $positions['SUP']], [5, 4, 3]),
					new Spell(30, 'pororecall', [], []),// aram
					new Spell(31, 'porothrow', [], []),	// aram
					new Spell(11, 'smite', [$positions['JUNGLE']], [10]),
					new Spell(32, 'snowball', [], []),	// aram
					new Spell(12, 'teleport', [$positions['TOP'], $positions['MID']], [10, 4])];
	}
	return $spells;
}

function getSpell($id)
{
	static $spellsById = NULL;
	if(!$spellsById)
	{
		$spells = getSpells();
		$spellsById = [];
		foreach($spells as $k=>$spell)
			$spellsById[$spell->id] = $spell;
	}
	return $spellsById[$id];
}

class Player
{

	public $id;
	public $champion;
	public $name;
	public $roles;
	public $spell1;
	public $spell2;

	public function __construct($id, $champion, $name, $roles, $spell1, $spell2)
	{
		$this->id = $id;
		$this->champion = $champion;
		$this->name = $name;
		$this->roles = $roles;
		$this->spell1 = $spell1;
		$this->spell2 = $spell2;
	}

	public function __toString()
	{
		return 'Player{id=' . $this->id . ',champion=' . $this->champion . ',name=' . $this->name . ',roles=' . json_encode($this->roles) . ',spell1=' . $this->spell1 . ',spell2=' . $this->spell2 . '}';
	}

	public function toHTML()
	{
		return '<span class="player">' . $this->name . ' plays ' . getChampionKey($this->champion) . ' which is '
			. implode(',', array_map(function($role){
				return $role->toHTML();
			}, $this->roles))
			. ' with ' . $this->spell1->toHTML() . ' and ' . $this->spell2->toHTML() . '</span>';
	}

}

function getPlayer($player)
{
	$roles = [];
	foreach(getChampionRoles($player['championId']) as $k=>$v)
	{
		$role = getRole($v);
		if($role)
			array_push($roles, $role);
	}
	return new Player($player['summonerId'], getChampionKey($player['championId']), $player['summonerName'], $roles, getSpell($player['spell1Id']), getSpell($player['spell2Id']));
}

function getPlayers($players)
{
	$out = [];
	foreach($players as $player)
		array_push($out, getPlayer($player));
	return $out;
}

function guessPlayer($player)
{
	// $probabilityScores is a [Position:Int] or Map<Position, Integer>
	$probabilityScores = [];
	foreach(getPositions() as $k=>$pos)
		$probabilityScores[$pos->id] = 0;
	// roles
	$positionsAmount = count(getPositions());
	$rolesAmount = count($player->roles);
	foreach($player->roles[0]->positions as $i=>$pos)
		$probabilityScores[$pos->id] += ($positionsAmount - $i) * ($rolesAmount==1?2:1);
	if($rolesAmount > 1)
	{
		foreach($player->roles[1]->positions as $i=>$pos)
			$probabilityScores[$pos->id] += ($positionsAmount - $i);
	}
	// spell1
	foreach($player->spell1->positions as $i=>$pos)
		$probabilityScores[$pos->id] += $player->spell1->scores[$i];
	// spell2
	foreach($player->spell2->positions as $i=>$pos)
		$probabilityScores[$pos->id] += $player->spell2->scores[$i];
	// sort
	arsort($probabilityScores);// not actually needed given its use
	return $probabilityScores;
}

function resolve($players)
{
	global $disableRoleDetectDebug;
	// $guesses is a [Position:[{player:Player, score:Int}]] or Map<Position, List<Entry<Player, Integer>>>
	$guesses = [];
	// retrieve all players guesses
	foreach(getPositions() as $k=>$pos)
		$guesses[$pos->id] = [];
	foreach($players as $k=>$player)
	{
		// $playerGuesses is a [Position:Int] or Map<Position, Integer>
		$playerGuesses = guessPlayer($player);
		foreach($playerGuesses as $position=>$score)
			array_push($guesses[$position], (object)['player'=>$player,'score'=>$score]);
	}
	// sort each position scores
	foreach(getPositions() as $k=>$pos)
		usort($guesses[$pos->id], function($a,$b){
			return $b->score - $a->score;
		});
	for($try=0;$try<10;$try++)
	{
		// detect wrong guesses by searching if the same player is on multiple lanes
		// $positionnedPlayers is a [Player]
		$positionnedPlayers = [];// players that are on at least one lane
		// $problems is a [Player:[Position]] or Map<Player, List<Position>>
		$problems = [];// will contain all the lanes where each player is (there should be one lane per player)
		$wrongGuesses = 0;
		foreach(getPositions() as $k=>$pos)
		{
			$player = $guesses[$pos->id][0]->player;
			if(!array_key_exists($player->id, $problems))
				$problems[$player->id] = [];
			array_push($problems[$player->id], $pos);
			if(array_search($player, $positionnedPlayers) !== FALSE)
				$wrongGuesses++;
			else
				array_push($positionnedPlayers, $player);
		}
		if($wrongGuesses == 0)
			return $guesses;
		// fix wrong guesses by replacing the wrong one with the closest score to the left-over player
		// remove all players that are on one lane only
		foreach($problems as $playerId=>$positions)
		{
			if(count($positions) == 1)
				unset($problems[$playerId]);
		}
		$missingPlayers = array_diff($players, $positionnedPlayers);// players that are on no lane
		if(!$disableRoleDetectDebug)
			echo 'try ' . $try . ' problems=' . json_encode($problems) . '<br> missing=' . json_encode($missingPlayers) . '<br>';//TODO
		// we only treat one problem and then search for any problem again so changes don't get cancelled
		$positions = reset($problems);
		// $scoreDifferences is a [Position:[player:Player, score:Int]] or Map<Position, List<Entry<Player, Integer>>>
		$scoreDifferences = [];
		foreach($positions as $k=>$pos)
		{
			$bestScore = $guesses[$pos->id][0]->score;
			$scoreDifferences[$pos->id] = [];
			foreach($guesses[$pos->id] as $laneGuess)
			{
				if(array_search($laneGuess->player, $missingPlayers) !== FALSE)// if player is on no lane
				{
					array_push($scoreDifferences[$pos->id], (object)['player'=>$laneGuess->player,'score'=>($bestScore - $laneGuess->score)]);
				}
			}
		}
		$minScoreDiffPosId = null;	// position of player that has minimum score difference
		$minScoreDiffPlayer = null;	// player that has minimum score difference
		$minScoreDiff = null;
		foreach($scoreDifferences as $pos=>$differences)
		{
			foreach($differences as $difference)
			{
				if($minScoreDiff === null || $difference->score < $minScoreDiff)// < or <=
				{
					$minScoreDiffPosId = $pos;
					$minScoreDiffPlayer = $difference->player;
					$minScoreDiff = $difference->score;
				}
			}
		}
		if(!$disableRoleDetectDebug)
			echo 'try ' . $try . ' minscoreisnullstartdiffs '.json_encode($scoreDifferences).' enddiffs<br>';//TODO
		// move the player that has minimum score difference at the start of the list
		$e = null;
		foreach($guesses[$minScoreDiffPosId] as $i=>$guess)
		{
			if($guess->player == $minScoreDiffPlayer)
			{
				$e = $guess;
				unset($guesses[$minScoreDiffPosId][$i]);
				break;
			}
		}
		array_unshift($guesses[$minScoreDiffPosId], $e);
	}
	if(!$disableRoleDetectDebug)
		echo 'tried too many times, aborting<br>';//TODO
	return $guesses;
}

function reduceSolved($solved)
{
	$reduced = [];
	foreach($solved as $posId=>$guesses)
		$reduced[$posId] = $guesses[0];
	return $reduced;
}

function reducedSolvedToSimplest($reduced)
{
	$out = [];
	foreach($reduced as $posId=>$guess)
		$out[strtolower(getPosition($posId)->name)] = $guess->player->id;
	return $out;
}

global $disableRoleDetectDebug;
$disableRoleDetectDebug = TRUE;
?>