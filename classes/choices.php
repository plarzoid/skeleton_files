<?php

require_once("check.php");
require_once("db_players.php");
require_once("db_countries.php");
require_once("db_states.php");
require_once("db_game_systems.php");
require_once("db_game_system_factions.php");
require_once("db_game_sizes.php");
require_once("db_events.php");
require_once("db_achievements.php");

class Choices {

	//choices arrays


	function Choices() {
		//do nothing, really
	}

        function getConfigureModes(){
            $ret = array(   array("text"=>"Countries", "value"=>"countries"),
                            array("text"=>"States", "value"=>"states"),
                            array("text"=>"Game Systems", "value"=>"game_systems"),
                            array("text"=>"Factions", "value"=>"game_system_factions"),
                            array("text"=>"Game Sizes", "value"=>"game_sizes"),
                            array("text"=>"Events", "value"=>"events")
                        );

            return $ret;
        }


	function getRedeemFunctionChoices(){
		return array(array("text"=>"Spend Points", "value"=>"SPEND"),
                             array("text"=>"Add Points", "value"=>"ADD"));
	}

	function getYesNoChoices($default="No"){
		$yes = array("value"=>1, "text"=>"Yes");
		$no  = array("value"=>0, "text"=>"No");
		//returns choices for a Yes/No select box, with the default set to $default
		if(($default=="No") || ($default=="no") || ($default=="NO") 
			|| ($default==false) || ($default == "false") || ($default=="FALSE")){
			return array($no, $yes);
		} else {	
			return array($yes, $no);
		}
	}


        function getFeedbackTypes(){
            $types = array("Comment", "Bug", "Question", "Suggestion", "Praise");

            $ret = array();
            foreach($types as $t){
                $ret[] = array("text"=>$t, "value"=>$t);
            }

            return $ret;

        }

	
	function getPlayerChoices(){
	    $db = new Players();
	    $players = $db->getAll();

	    $ret = array(array("text"=>"Please select...", "value"=>null));

	    if(empty($players)){return $ret;}	
	    foreach($players as $player){
		$ret[] = array(
                    "text"=>$player[last_name].', '.$player[first_name], 
                    "value"=>$player[id]
                    );
	    }

	    return $ret;
	}
	
	function getIntegerChoices($min, $max, $step){
		$ret = array();
                if(Check::notInt($min)){echo "Bad min value!";}
                if(Check::notInt($max)){echo "Bad max value!";}
                if(Check::notInt($step)){echo "Bad step value!";}

		for($i=$min; $i<=$max; $i+=$step){
			$ret[] = array('value'=>$i, 'text'=>$i);
		}

		return $ret;
	}
	
        function getStates($parent_id){

            if(Check::isNull($parent_id)){
                return array(array("text"=>"No States Exist", "value"=>"null"));
            }

            $s = new States();

            $states = $s->getByParent($parent_id);

            if($states){
                $ret = array();

                foreach($states as $state){
                    $ret[] = array("value"=>$state[id], "text"=>$state[name]);
                }

		return $ret;
            }

            return null;
	}

	function getCountries(){

                $c = new Countries();
                $countries = $c->getAll();
                
                if($countries){
                    $ret = array();
                    foreach($countries as $country){
                        $ret[] = array("value"=>$country[id], "text"=>$country[name]);
                    }
		    return $ret;
                }

                return null;
	}

        function getGameSystems(){
            $gs = new Game_systems();
            $systems = $gs->getAll();

            if($systems){
                $ret = array(array("text"=>"Please select...", "value"=>null));
                foreach($systems as $system){
                    $ret[] = array("value"=>$system[id], "text"=>$system[name]);
                }
                
                return $ret;
            }

            return null;
        }

        function getGameSystemFactions($system_id){

            $gsf = new Game_system_factions();
            $factions = $gsf->getByParentGameSystem($system_id);

            if($factions){
                $ret = array(array("text"=>"Please select...", "value"=>null));
                foreach($factions as $faction){
                    $ret[] = array("value"=>$faction[id], "text"=>$faction[name]." (".$faction[acronym].")");
                }

                return $ret;
            }

            return null;
        }

        function getGameSizes($system_id){
            
            $gsz = new Game_sizes();
            $sizes = $gsz->getByParentGameSystem($system_id);

            if($sizes){
                $ret = array(array("text"=>"Please select...", "value"=>null));
                foreach($sizes as $size){
                    $text = $size[size];
                    if($size[name]){ $text.= " (".$size[name].")";}

                    $ret[] = array("value"=>$size[id], "text"=>$text);
                }

                return $ret;
            }

            return null;
        }

        function getEvents(){
            $edb = new Events();
            $events = $edb->getAll();

            if($events){
                $ret = array(array("text"=>"Please select...", "value"=>null));
                foreach($events as $event){
                    $ret[] = array("text"=>$event[name], "value"=>$event[id]);
                }

                return $ret;
            }
            
            return array(array("text"=>"None Exist!", "value"=>null));
        }

        function getAchievementTypes(){
            $ret = array(   array("text"=>"Standard", "value"=>0),
                            array("text"=>"Meta", "value"=>1)
                        );

            return $ret;
        }

        function getGameSystemAchievements($system_id){
            $adb = new Achievements();
            $achs = $adb->getByGameSystemId($system_id);

            if($achs){
                $ret = array(array("text"=>"Please select...", "value"=>null));
                foreach($achs as $ach){
                    $ret[] = array("value"=>$ach[id], "text"=>$ach[name]." (".$ach[points].")");
                }

                return $ret;
            }

            return array(array("text"=>"None Exist!", "value"=>null));
        }


        function getEventAchievementChoices(){
            $adb = new Achievements();
            $possible = $adb->getAll();

            $achs = array();
            foreach($possible as $p){
                if($p[event_id]){
                    $achs[] = $p;
                }
            }

            if($achs){
                $ret = array(array("text"=>"Please select...", "value"=>null));
                foreach($achs as $ach){
                    $ret[] = array("value"=>$ach[id], "text"=>$ach[name]." (".$ach[points].")");
                }

                return $ret;
            }

            return array(array("text"=>"None Exist!", "value"=>null));
        }


        function leaderboardSortChoices(){
            $columns = array("Name (L, F)"=>"name",
                         "# Games"=>"game_count",
                         "# Opponents"=>"opponents",
                         "# Locations"=>"locations",
                         "# Factions"=>"factions",
                         "Points Earned"=>"earned",
                         "Points Spent"=>"spent",
                         "Points"=>"points");

            $ret = array();
            foreach($columns as $t=>$v){
                $ret[] = array("text"=>$t, "value"=>$v);
            }

            return $ret;
        }

        function sortDirectionChoices(){
            return array(array("text"=>"Descending", "value"=>"1"),
                         array("text"=>"Ascending", "value"=>"0"));
        }


}
?>
