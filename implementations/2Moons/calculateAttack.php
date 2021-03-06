<?php

/**
 *  OPBE
 *  Copyright (C) 2013  Jstar
 *
 * This file is part of OPBE.
 * 
 * OPBE is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OPBE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with OPBE.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OPBE
 * @author Jstar <frascafresca@gmail.com>
 * @copyright 2013 Jstar <frascafresca@gmail.com>
 * @license http://www.gnu.org/licenses/ GNU AGPLv3 License
 * @version alpha(2013-2-4)
 * @link https://github.com/jstar88/opbe
 */
function calculateAttack(&$attackers, &$defenders, $FleetTF, $DefTF)
{
    global $pricelist, $CombatCaps;

    // building attackers
    $attackerGroupObj = new PlayerGroup();
    foreach ($attackers as $fleetID => $attacker)
    {
        $player = $attacker['player'];
        if ($attackerGroupObj->existPlayer($player['id']))
        {
            $attackerPlayerObj = $attackerGroupObj->getPlayer($player['id']);
        }
        else
        {
            $attackerPlayerObj = new Player($player['id']);
            $attackerPlayerObj->setTech($player['military_tech'], $player['shield_tech'], $player['defence_tech']);
            $attackerGroupObj->addPlayer($attackerPlayerObj);
        }

        $attackerFleetObj = new Fleet($fleetID);
        foreach ($attacker['unit'] as $element => $amount)
        {
            $fighters = getFighters($element, $amount);
            $attackerFleetObj->add($fighters);
        }
        $attackerPlayerObj->addFleet($attackerFleetObj);
    }

    // building defenders
    $defenderGroupObj = new PlayerGroup();
    foreach ($defenders as $fleetID => $defender)
    {
        $player = $attacker['player'];
        if ($defenderGroupObj->existPlayer($player['id']))
        {
            $defenderPlayerObj = $defenderGroupObj->getPlayer($player['id']);
        }
        else
        {
            $defenderPlayerObj = new Player($player['id']);
            $defenderPlayerObj->setTech($player['military_tech'], $player['shield_tech'], $player['defence_tech']);
            $defenderGroupObj->addPlayer($defenderPlayerObj);
        }

        $defenderFleetObj = getFleet($fleetID);
        foreach ($defender['unit'] as $element => $amount)
        {
            $fighters = getFighters($element, $amount);
            $defenderFleetObj->add($fighters);
        }
        $defenderPlayerObj->addFleet($defenderFleetObj);
    }

    // start of battle

    $opbe = new Battle($attackerGroupObj, $defenderGroupObj);
    $opbe->startBattle();
    $report = $opbe->getReport();

    //to do: update attackers and defenders array data with the report info.

    if ($report->defenderHasWin())
    {
        $won = "r"; // defender
    }
    elseif ($report->attackerHasWin())
    {
        $won = "a"; // attacker
    }
    else
    {
        $won = "w"; // draw
    }


}

function getFighters($id, $count)
{
    global $CombatCaps, $pricelist;
    $rf = $CombatCaps[$id]['sd'];
    $shield = $CombatCaps[$id]['shield'];
    $cost = array($pricelist[$element]['cost'][901], $pricelist[$element]['cost'][902]);
    $power = $CombatCaps[$id]['attack'];
    if ($id < 300)
        return new Ship($id, $count, $rf, $shield, $cost, $power);
    return new Defense($id, $count, $rf, $shield, $cost, $power);
}
function getFleet($id)
{
    if ($id == 0)
    {
        return new HomeFleet(0);
    }
    return new Fleet($id);
}

?>