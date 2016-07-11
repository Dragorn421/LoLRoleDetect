<?php
function startsWith($haystack, $needle)
{
	/* http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php */
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

function requestAPI($url)
{
	// [IMPLEMENT RATE LIMIT HERE] wait until the rate limit allows a request
	// the actual request (multiple times in case of an error)
	$tryCount = 0;
	do {
		$res = file_get_contents($url);
		$responseCode = intval(explode(' ', $http_response_header[0])[1]);
		if($responseCode == 200)// ok
			return json_decode($res, TRUE);
		if($responseCode == 400 || $responseCode == 404)// bad request or not found
			return NULL;
		// 429 and other response codes (like 503) would lead to another attempt after a tiny delay
		$wait = 1;
		if($responseCode == 429)// rate limit
		{
			$wait = 10;// wait 10 seconds by default
			// search if Retry-After was set, if yes then wait that time
			foreach($http_response_header as $header)
				if(startsWith($header, 'Retry-After: '))
					$wait = intval(explode(' ', $header)[1]);
			// [IMPLEMENT RATE LIMIT HERE] set the time when the rate limit will allow an other call
		}
		sleep($wait);// will wait enough if rate limited, or 1 second if unknown error
		$tryCount++;
	} while($tryCount != 5);// no more than 5 attempts
	return NULL;
}

function genFunction($name, $data, $key, $globalVarName = null)
{
	$reducedData = [];
	foreach($data as $id=>$v)
	{
		$reducedData[intval($id)] = $v[$key];
	}
	if($globalVarName == null)
		return 'function ' . $name . '($id){$data=' . var_export($reducedData, true) . ';return $data[$id];}';
	else
		return '$' . $globalVarName . '=' . var_export($reducedData, true) . ';function ' . $name . '($id){global $' . $globalVarName . ';return $' . $globalVarName . '[$id];}';
}

// path to include directory, not needed unless file is not executed in the include directory
$includeDir = '';
$jsStaticDir = $includeDir . '../js/static/';

$apiKey = '00000000-0000-0000-0000-000000000000';

// change region and locale here
$root = 'https://global.api.pvp.net/api/lol/static-data/euw/v1.2/';
$locale = 'en_GB';

//version
$mainFile = $includeDir . 'static/main.php';
echo('Getting latest version	');//TODO
$realm = requestAPI($root . 'realm?api_key=' . $apiKey);
if($realm !== NULL && array_key_exists('v', $realm))
{
	$version = $realm['v'];
	// check if ddragon is up-to-date for this version
	if(file_get_contents('http://ddragon.leagueoflegends.com/cdn/' . $version . '/img/champion/Teemo.png') !== FALSE)
	{
		file_put_contents($mainFile, '<?php function getCurrentPatch(){return ' . var_export($version, TRUE) . ';} ?>');
		file_put_contents($jsStaticDir . 'main.js', 'function getCurrentPatch(){return ' . var_export($version, TRUE) . ';}');
	}
}

//champions
$championsFile = $includeDir . 'static/champions.php';
echo('Getting champions data	');//TODO
$champions = requestAPI($root . 'champion?locale=' . $locale . '&dataById=true&champData=all&api_key=' . $apiKey);
// php
if($champions === NULL)
{
	include_once($championsFile);
	$champions = array_map(function($key, $name, $title, $roles){
		return ['key'=>$key,'name'=>$name,'title'=>$title,'tags'=>$roles];
	}, $championKeys, $championNames, $championTitles, $championRoles);
}
else
	$champions = $champions['data'];
echo('Getting free champions data	');//TODO
$freeToPlay = requestAPI('https://euw.api.pvp.net/api/lol/euw/v1.2/champion?freeToPlay=true&api_key=' . $apiKey);
if($freeToPlay === NULL || !is_array($freeToPlay) || !array_key_exists('champions', $freeToPlay))
{
	include_once($championsFile);
	$freeToPlayIds = getChampionsFreeToPlay();
}
else
{
	$freeToPlayIds = [];
	foreach($freeToPlay['champions'] as $v)
		array_push($freeToPlayIds, $v['id']);
}
$t = '<?php ';
$t .= genFunction('getChampionKey', $champions, 'key', 'championKeys');
$t .= genFunction('getChampionName', $champions, 'name', 'championNames');
$t .= genFunction('getChampionTitle', $champions, 'title', 'championTitles');
$t .= genFunction('getChampionRoles', $champions, 'tags', 'championRoles');
$t .= 'function getChampionsFreeToPlay(){return ' . var_export($freeToPlayIds, true) . ';}';
$t .= 'function isChampionFreeToPlay($id){return in_array($id,getChampionsFreeToPlay(),TRUE);}';
$t .= ' ?>';
file_put_contents($championsFile, $t);
// javascript
$jsChampions = [];
foreach($champions as $id=>$data)
	$jsChampions[$id] = [$data['key'],$data['name'],$data['title'],$data['tags']];
$t = 'Champions=' . json_encode($jsChampions) . ';';
$t .= 'Champions.freeToPlay=' . json_encode($freeToPlayIds) . ';';
$t .= 'Champions.getKey=function(id){return Champions[id][0];};';
$t .= 'Champions.getName=function(id){return Champions[id][1];};';
$t .= 'Champions.getTitle=function(id){return Champions[id][2];};';
$t .= 'Champions.getTags=function(id){return Champions[id][3];};';
$t .= 'Champions.isFree=function(id){return Champions.freeToPlay.indexOf(id) >= 0;};';
file_put_contents($jsStaticDir . 'champions.js', $t);

//summoner spells
echo('Getting summoner spells data	');//TODO
$summonerSpells = requestAPI($root . 'summoner-spell?locale=' . $locale . '&dataById=true&api_key=' . $apiKey);
if($summonerSpells !== NULL)
{
	$summonerSpells = $summonerSpells['data'];
	// php
	$t = '<?php ';
	$t .= genFunction('getSummonerSpellKey', $summonerSpells, 'key');
	$t .= genFunction('getSummonerSpellName', $summonerSpells, 'name');
	$t .= ' ?>';
	file_put_contents($includeDir . 'static/summoners.php', $t);
	// javascript
	$jsSummoners = [];
	foreach($summonerSpells as $id=>$data)
		$jsSummoners[$id] = [$data['key'],$data['name']];
	$t = 'Summoners=' . json_encode($jsSummoners) . ';';
	$t .= 'Summoners.getKey=function(id){return Summoners[id][0];};';
	$t .= 'Summoners.getName=function(id){return Summoners[id][1];};';
	file_put_contents($jsStaticDir . 'summoners.js', $t);
}
?>