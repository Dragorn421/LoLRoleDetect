function Player(champion, spell1, spell2)
{
	this.champion = champion;
	this.spell1 = spell1;
	this.spell2 = spell2;
	this.build = buildPlayer;
	this.toJSON = playerToJSON;
}

function buildPlayer()
{
	var root = document.createElement('div');
	root.className = 'player';
	var championIcon = document.createElement('img');
	championIcon.className = 'champion-icon';
	championIcon.player = this;
	championIcon.src = 'http://ddragon.leagueoflegends.com/cdn/' + getCurrentPatch() + '/img/champion/' + Champions.getKey(this.champion) + '.png';
	championIcon.alt = Champions.getName(this.champion);
	championIcon.addEventListener('click', function(ev){
		var t = 'Pick a champion (has to match exactly one of these ones):';
		for(var k in Champions.byName)
			t += k + ',';
		var r = prompt(t).toLowerCase();
		if(!(r in Champions.byName))
		{
			alert('Champion ' + r + ' not found');
			return;
		}
		ev.target.player.champion = Champions.byName[r];
		updateTeam();
	});
	root.appendChild(championIcon);
	var summonerSpells = document.createElement('div');
	summonerSpells.className = 'summoner-spells';
	summonerSpells.appendChild(buildSpell(this.spell1, this, 1));
	summonerSpells.appendChild(buildSpell(this.spell2, this, 2));
	root.appendChild(summonerSpells);
	return root;
}

function buildSpell(spell, player, number)
{
	var spellIcon = document.createElement('img');
	spellIcon.className = 'summoner-spell';
	spellIcon.src = 'http://ddragon.leagueoflegends.com/cdn/' + getCurrentPatch() + '/img/spell/' + Summoners.getKey(spell) + '.png';
	spellIcon.alt = Summoners.getName(spell);
	spellIcon.addEventListener('click', function(){
		var t = 'Pick a spell (has to match exactly one of these ones):';
		for(var k in Summoners.byName)
			t += k + ',';
		var r = prompt(t).toLowerCase();
		if(!(r in Summoners.byName))
		{
			alert('Spell ' + r + ' not found');
			return;
		}
		player['spell' + number] = Summoners.byName[r];
		updateTeam();
	});
	return spellIcon;
}

function playerToJSON()
{
	return '{"champion":' + this.champion + ',"spell1":' + this.spell1 + ',"spell2":' + this.spell2 + '}';
}

function updateTeam()
{
	var teamNode = document.getElementById('team');
	while(teamNode.firstChild)
		teamNode.removeChild(teamNode.firstChild);
	for(var i=0;i<team.length;i++)
	{
		teamNode.appendChild(team[i].build());
	}
}

function runRoleDetection()
{
	var json = '[';
	for(var i=0;i<team.length;i++)
	{
		if(i != 0)
			json += ',';
		json += team[i].toJSON();
	}
	json += ']';
	var xhr = new XMLHttpRequest();
	xhr.open('GET', 'guess.php?input=' + encodeURIComponent(json), true);
	xhr.onreadystatechange = function() {
		if(xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0))
		{
			document.getElementById('role-detection-out').innerHTML = xhr.responseText;
		}
	};
	xhr.send();
}

function bodyLoaded()
{
	team = [];
	for(var i=0;i<5;i++)
		team[i] = new Player(17, 4, 14);
	updateTeam();
}

Champions.byName = {};
for(var k in Champions)
{
	if(typeof Champions[k] == 'object' && Champions[k].length && typeof Champions[k][1] == 'string')
	{
		Champions.byName[Champions[k][1].toLowerCase()] = k;
	}
}
Summoners.byName = {cleanse:1,clairvoyance:2,exhaust:3,flash:4,ghost:6,heal:7,smite:11,teleport:12,clarity:13,ignite:14,barrier:21};