#LoLRoleDetect
League of Legends Role Detection based on champion and spells

## License
[The MIT License (MIT)](https://github.com/Dragorn421/LoLRoleDetect/blob/master/LICENSE)  
Copyright (c) 2016 Dragorn421

## Setup
### Introduction
Welcome!  
People are not supposed to have access to the include directory, you should deny it with configuration or `.htaccess`.  
The only file you really need is include/roledetect.php, however it will only work if up-to-date static data
exists, such as some that was generated using include/updatestatic.php (this file also generates data for
javascript, you may want to remove that part, keep in mind the testing utility needs it).  

### updatestatic.php
If using updatestatic.php don't forget to set the api key. You should put the path to the include directory
so that the file can be run from anywhere.  
If you want to run updatestatic.php you can access `updatedata.php` from your browser or from command line under
some linux OS with `php -f /path/to/updatestatic.php`. Note that for these two to work you must for most cases
set the include path as described above, and the file must be able to write to the static directory(ies).  
Once you are done with it you should delete updatedata.php or move it to include so that people can't spam it.

### Test time
I said you only need one or two file, that is because files in the root folder here are meant to test it, not for
a proper use. You're free to mess around with it though.

### Use
You **MUST** include the champions.php static file yourself before using roledetect.php. I made it like that
because using relative paths feels to me really weird when using include.  

## Functions
See player DTO description here:  
https://developer.riotgames.com/api/methods -> current-game-v1.0 -> CurrentGameParticipant
### getPlayer(arr)
Convert player DTO from a live game to a Player object used by RoleDetect.
### getPlayers(arr)
Convert an array of player DTOs from a live game to an array of Player objects used by RoleDetect.
### resolve(arr)
Guesses where each player is. Won't work for a team that has not exactly 5 players.  
Returned array associate guesses to each position id. Guesses are an array of objects with `player` being the
target Player object and `score` being the score for this player. The score is an arbitrary number indicating
how likely it is that the player is at the position. Guesses are ordered for each position such as the first
player for each position is different and is the best guess given the team.
### reduceSolved(arr)
Convert resulting array of `resolve()` so that only the most likely player is kept for each position.  
Returned array associates a Player object to each position id.
### reducedSolvedToSimplest(arr)
Convert resulting array of `reduceSolved()` so that each position **name** (top, jungle, mid, adc, sup) is
mapped to a summoner id instead of position id mapped to Player object.

## Demo
http://brallos.pro/lol/roledetect/ (not the same code as the test utility provided in this repository, but same core code)

## Examples
See [guess.php](https://github.com/Dragorn421/LoLRoleDetect/blob/master/guess.php) file!
