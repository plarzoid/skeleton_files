<?php

require_once("player.php");
require_once("settings.php");

class Choices {

	//choices arrays


	function Choices() {
		//do nothing, really
	}

	function getRedeemFunctionChoices(){
		return array(array("text"=>"Spend Points", "value"=>"SPEND"),array("text"=>"Add Points", "value"=>"ADD"));
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

	
	function getPlayerListChoices(){
		$p = new Player();

		$players = $p->getActivePlayers();

		$ret = array(array("text"=>"", "value"=>0));

		if(empty($players)){return $ret;}	
		foreach($players as $player){
			$ret[] = array("text"=>$player[lastname].', '.$player[firstname], "value"=>$player[id]);
		}

		return $ret;
	}
	
	function getLocationType(){
		$ret = array(
			array('value'=>1, 'text'=>'US State'),
			array('value'=>0, 'text'=>'Country'));
		return $ret;
	}

	function getFactionChoices(){
		$ret = array(
			array("text"=>"Cygnar", "value"=>"CYG"),
                        array("text"=>"Khador", "value"=>"KHA"),
                        array("text"=>"Protectorate", "value"=>"POM"),
                        array("text"=>"Cryx", "value"=>"CRX"),
                        array("text"=>"Retribution", "value"=>"RET"),
                        array("text"=>"Mercenaries", "value"=>"MER"),
			array("text"=>"Convergence", "value"=>"CON"),
                        array("text"=>"Skorne", "value"=>"SKO"),
                        array("text"=>"Trollbloods", "value"=>"TRO"),
                        array("text"=>"Circle", "value"=>"CIR"),
                        array("text"=>"Legion", "value"=>"LOE"),
                        array("text"=>"Minions", "value"=>"MIN")
		);

		return $ret;
	}
	
	function getNumOpponentsChoices(){
		$ret = array();

		for($i=2; $i<=10; $i++){
			$ret[] = array('value'=>$i, 'text'=>$i);
		}

		return $ret;
	}

	function getEventPlayerCountChoices(){
		$ret = array();

		for($i=1; $i<=64; $i++){
			$ret[] = array('value'=>$i, 'text'=>$i);
		}
		
		return $ret;
	}

	function getGameSizeChoices(){
		$gamesizes = array(25, 35, 50, 75, 100, "UNBOUND");

		$ret = array();

		foreach($gamesizes as $g){
			$ret[] = array("text"=>$g, "value"=>$g);
		}
		
		return $ret;
	}

	function getEvents(){

		$ret = array();
		$ret[] = array('value'=>'', 'text'=>'');		

		$s_db = new Settings();

		$settings = $s_db->getSettings();

		for($i=1; $i<=20; $i++){	
			if(($settings['event'.$i]+0) > 0){
				$ret[]=array('value'=>'event'.$i, 'text'=>$settings['event'.$i.'name']);
			}
		}
		return $ret;
	}

	function getStates(){
		$ret=array(
			array('text'=>'Alabama', 'value'=>'AL'),
			array('text'=>'Alaska', 'value'=>'AK'),
			array('text'=>'Arizona', 'value'=>'AZ'),
			array('text'=>'Arkansas', 'value'=>'AR'),
			array('text'=>'California', 'value'=>'CA'),
			array('text'=>'Colorado', 'value'=>'CO'),
			array('text'=>'Connecticut', 'value'=>'CT'),
			array('text'=>'Delaware', 'value'=>'DE'),
			array('text'=>'District of Columbia', 'value'=>'DC'),
			array('text'=>'Florida', 'value'=>'FL'),
			array('text'=>'Georgia', 'value'=>'GA'),
			array('text'=>'Hawaii', 'value'=>'HI'),
			array('text'=>'Idaho', 'value'=>'ID'),
			array('text'=>'Illinois', 'value'=>'IL'),
			array('text'=>'Indiana', 'value'=>'IN'),
			array('text'=>'Iowa', 'value'=>'IA'),
			array('text'=>'Kansas', 'value'=>'KS'),
			array('text'=>'Kentucky', 'value'=>'KY'),
			array('text'=>'Louisiana', 'value'=>'LA'),
			array('text'=>'Maine', 'value'=>'ME'),
			array('text'=>'Maryland', 'value'=>'MD'),
			array('text'=>'Massachusetts', 'value'=>'MA'),
			array('text'=>'Michigan', 'value'=>'MI'),
			array('text'=>'Minnesota', 'value'=>'MN'),
			array('text'=>'Mississippi', 'value'=>'MS'),
			array('text'=>'Missouri', 'value'=>'MO'),
			array('text'=>'Montana', 'value'=>'MT'),
			array('text'=>'Nebraska', 'value'=>'NE'),
			array('text'=>'Nevada', 'value'=>'NV'),
			array('text'=>'New Hampshire', 'value'=>'NH'),
			array('text'=>'New Jersey', 'value'=>'NJ'),
			array('text'=>'New Mexico', 'value'=>'NM'),
			array('text'=>'New York', 'value'=>'NY'),
			array('text'=>'North Carolina', 'value'=>'NC'),
			array('text'=>'North Dakota', 'value'=>'ND'),
			array('text'=>'Ohio', 'value'=>'OH'),
			array('text'=>'Oklahoma', 'value'=>'OK'),
			array('text'=>'Oregon', 'value'=>'OR'),
			array('text'=>'Pennsylvania', 'value'=>'PA'),
			array('text'=>'Rhode Island', 'value'=>'RI'),
			array('text'=>'South Carolina', 'value'=>'SC'),
			array('text'=>'South Dakota', 'value'=>'SD'),
			array('text'=>'Tennessee', 'value'=>'TN'),
			array('text'=>'Texas', 'value'=>'TX'),
			array('text'=>'Utah', 'value'=>'UT'),
			array('text'=>'Vermont', 'value'=>'VT'),
			array('text'=>'Virginia', 'value'=>'VA'),
			array('text'=>'Washington', 'value'=>'WA'),
			array('text'=>'West Virginia', 'value'=>'WV'),
			array('text'=>'Wisconsin', 'value'=>'WI'),
			array('text'=>'Wyoming', 'value'=>'WY')
		);	
		return $ret;
	}

	function getCountries(){
		$ret = array(
			array('text'=>'Afghanistan', 'value'=>'AFG'),
			array('text'=>'Albania', 'value'=>'ALB'),
			array('text'=>'Algeria', 'value'=>'ALG'),
			array('text'=>'American Samoa', 'value'=>'AMS'),
			array('text'=>'Andorra', 'value'=>'AND'),
			array('text'=>'Angola', 'value'=>'AGA'),
			array('text'=>'Anguilla', 'value'=>'AGU'),
			array('text'=>'Antigua and Barbuda', 'value'=>'ANB'),
			array('text'=>'Argentina', 'value'=>'ARG'),
			array('text'=>'Armenia', 'value'=>'ARM'),
			array('text'=>'Australia', 'value'=>'AUS'),
			array('text'=>'Austria', 'value'=>'ARA'),
			array('text'=>'Azerbajan', 'value'=>'AZR'),
			array('text'=>'Bahamas', 'value'=>'BAH'),
			array('text'=>'Bahrain', 'value'=>'BAR'),
			array('text'=>'Bangladesh', 'value'=>'BAN'),
			array('text'=>'Barbados', 'value'=>'BAB'),
			array('text'=>'Belarus', 'value'=>'BEL'),
			array('text'=>'Belgium', 'value'=>'BEG'),
			array('text'=>'Belize', 'value'=>'BEZ'),
			array('text'=>'Benin', 'value'=>'BEN'),
			array('text'=>'Bermuda', 'value'=>'BER'),
			array('text'=>'Bhutan', 'value'=>'BHU'),
			array('text'=>'Bolivia', 'value'=>'BOL'),
			array('text'=>'Bosnia and Herzegovina', 'value'=>'BOS'),
			array('text'=>'Botswana', 'value'=>'BOT'),
			array('text'=>'Brazil', 'value'=>'BRA'),
			array('text'=>'Brunei Darussalam', 'value'=>'BRU'),
			array('text'=>'Bulgaria', 'value'=>'BUL'),
			array('text'=>'Burkina Faso', 'value'=>'BUR'),
			array('text'=>'Burundi', 'value'=>'BND'),
			array('text'=>'Cambodia', 'value'=>'CAM'),
			array('text'=>'Cameroon', 'value'=>'CAR'),
			array('text'=>'Canada', 'value'=>'CAN'),
			array('text'=>'Chile', 'value'=>'CHI'),
			array('text'=>'China', 'value'=>'CNA'),
			array('text'=>'Colombia', 'value'=>'COL'),
			array('text'=>'Costa Rica', 'value'=>'CTA'),
			array('text'=>'Cuba', 'value'=>'CUB'),
			array('text'=>'Cyprus', 'value'=>'CYP'),
			array('text'=>'Czech Republic', 'value'=>'CZR'),
			array('text'=>'Democratic Republic Congo', 'value'=>'CON'),
			array('text'=>'Denmark', 'value'=>'DEN'),
			array('text'=>'Djibouti', 'value'=>'DJI'),
			array('text'=>'Dominican Republic', 'value'=>'DOM'),
			array('text'=>'East Timor', 'value'=>'TIM'),
			array('text'=>'Ecuador', 'value'=>'ECU'),
			array('text'=>'Egypt', 'value'=>'EGY'),
			array('text'=>'El Salvador', 'value'=>'SAL'),
			array('text'=>'England', 'value'=>'ENG'),
			array('text'=>'Eritrea', 'value'=>'ERI'),
			array('text'=>'Estonia', 'value'=>'EST'),
			array('text'=>'Ethiopia', 'value'=>'ETH'),
			array('text'=>'Faroe Islands', 'value'=>'FAR'),
			array('text'=>'Fiji', 'value'=>'FIJI'),
			array('text'=>'Finland', 'value'=>'FIN'),
			array('text'=>'France', 'value'=>'FRA'),
			array('text'=>'French Polynesia', 'value'=>'FPL'),
			array('text'=>'Gambia', 'value'=>'GAM'),
			array('text'=>'Georgia (Sakartvelo)', 'value'=>'GEO'),
			array('text'=>'Germany', 'value'=>'GER'),
			array('text'=>'Gabon', 'value'=>'GAB'),
			array('text'=>'Ghana', 'value'=>'GHA'),
			array('text'=>'Greece', 'value'=>'GRE'),
			array('text'=>'Greenland Kalaallit Nunaat', 'value'=>'GLD'),
			array('text'=>'Grenada', 'value'=>'GRE'),
			array('text'=>'Gouadeloupe', 'value'=>'GOU'),
			array('text'=>'Guam', 'value'=>'GAM'),
			array('text'=>'Guatemala', 'value'=>'GAT'),
			array('text'=>'Guernsey', 'value'=>'GNS'),
			array('text'=>'Guyana', 'value'=>'GUY'),
			array('text'=>'Guyane', 'value'=>'GYE'),
			array('text'=>'Haiti', 'value'=>'HTI'),
			array('text'=>'Honduras', 'value'=>'HON'),
			array('text'=>'Hong Kong', 'value'=>'HKG'),
			array('text'=>'Hrvatska (Croatia)', 'value'=>'HRV'),
			array('text'=>'Hungary', 'value'=>'HUN'),
			array('text'=>'Iceland', 'value'=>'ICE'),
			array('text'=>'India', 'value'=>'IND'),
			array('text'=>'Indonesia', 'value'=>'IDO'),
			array('text'=>'Iran', 'value'=>'IRN'),
			array('text'=>'Iraq', 'value'=>'IRQ'),
			array('text'=>'Ireland', 'value'=>'IRE'),
			array('text'=>'Israel', 'value'=>'ISR'),
			array('text'=>'Italy', 'value'=>'ITA'),
			array('text'=>'Jamaica', 'value'=>'JAM'),
			array('text'=>'Japan', 'value'=>'JAP'),
			array('text'=>'Jordan', 'value'=>'HOR'),
			array('text'=>'Kazakhstan', 'value'=>'KAZ'),
			array('text'=>'Kenya', 'value'=>'KEN'),
			array('text'=>'Korea Republic', 'value'=>'KOR'),
			array('text'=>'Kosovo', 'value'=>'KOS'),
			array('text'=>'Kurdistan', 'value'=>'KUR'),
			array('text'=>'Kuwait', 'value'=>'KUW'),
			array('text'=>'Kyrgyzstan', 'value'=>'KYR'),
			array('text'=>'Laos', 'value'=>'LAO'),
			array('text'=>'Latvia', 'value'=>'LAT'),
			array('text'=>'Lebanon', 'value'=>'LEB'),
			array('text'=>'Lesotho', 'value'=>'LES'),
			array('text'=>'Liberia', 'value'=>'LIB'),
			array('text'=>'Libyan Arab Jamahiriya', 'value'=>'JAM'),
			array('text'=>'Liechtenstein', 'value'=>'LIE'),
			array('text'=>'Lithuania', 'value'=>'LIT'),
			array('text'=>'Luxembourg', 'value'=>'LUX'),
			array('text'=>'Macau', 'value'=>'MAC'),
			array('text'=>'Macedonia', 'value'=>'MCD'),
			array('text'=>'Malawi', 'value'=>'MAW'),
			array('text'=>'Malaysia', 'value'=>'MYS'),
			array('text'=>'Mali', 'value'=>'MAL'),
			array('text'=>'Malta', 'value'=>'MAT'),
			array('text'=>'Marshall Islands', 'value'=>'MAR'),
			array('text'=>'Mauritania', 'value'=>'MAU'),
			array('text'=>'Martinique', 'value'=>'MTQ'),
			array('text'=>'Mauritius', 'value'=>'MRT'),
			array('text'=>'Mexico', 'value'=>'MEX'),
			array('text'=>'Micronesia', 'value'=>'MIC'),
			array('text'=>'Moldova', 'value'=>'MOL'),
			array('text'=>'Monaco', 'value'=>'MON'),
			array('text'=>'Mongolia', 'value'=>'MGA'),
			array('text'=>'Morocco', 'value'=>'MOR'),
			array('text'=>'Mozambique', 'value'=>'MOZ'),
			array('text'=>'Namibia', 'value'=>'NAM'),
			array('text'=>'Nepal', 'value'=>'NEP'),
			array('text'=>'Netherlands', 'value'=>'NET'),
			array('text'=>'Netherlands Antilles', 'value'=>'NAT'),
			array('text'=>'New Caledonia', 'value'=>'CAL'),
			array('text'=>'New Zealand (Aotearoa)', 'value'=>'ZEA'),
			array('text'=>'Nicaragua', 'value'=>'NIC'),
			array('text'=>'Nigeria', 'value'=>'NIG'),
			array('text'=>'Niue', 'value'=>'NIU'),
			array('text'=>'Norfolk Island', 'value'=>'NOR'),
			array('text'=>'Northern Ireland', 'value'=>'NIR'),
			array('text'=>'Northern Mariana Islands', 'value'=>'NMI'),
			array('text'=>'Norway', 'value'=>'NWY'),
			array('text'=>'Oman', 'value'=>'OMA'),
			array('text'=>'Pakistan', 'value'=>'PAK'),
			array('text'=>'Palau', 'value'=>'PAL'),
			array('text'=>'Palestina', 'value'=>'PST'),
			array('text'=>'Panama', 'value'=>'PAN'),
			array('text'=>'Papua New Guinea', 'value'=>'PNG'),
			array('text'=>'Paraguay', 'value'=>'PGY'),
			array('text'=>'Peru', 'value'=>'PER'),
			array('text'=>'Philippines', 'value'=>'PHI'),
			array('text'=>'Portugal', 'value'=>'PTG'),
			array('text'=>'Puerto Rico', 'value'=>'PTR'),
			array('text'=>'Qatar', 'value'=>'QAT'),
			array('text'=>'Reunion', 'value'=>'REU'),
			array('text'=>'Romania', 'value'=>'ROM'),
			array('text'=>'Russian Federation (AsianPart)', 'value'=>'RFA'),
			array('text'=>'Russian Federation (European Part)', 'value'=>'RFE'),
			array('text'=>'Rwanda', 'value'=>'RWA'),
			array('text'=>'Saint Kitts and Nevis', 'value'=>'SKN'),
			array('text'=>'Saint Vincent and the Grenadines', 'value'=>'SVG'),
			array('text'=>'Samoa (American Samoa)', 'value'=>'SAS'),
			array('text'=>'Samoa (Western Samoa)', 'value'=>'SWA'),
			array('text'=>'San Marino', 'value'=>'SMO'),
			array('text'=>'Saudi Arabia', 'value'=>'SAA'),
			array('text'=>'Scotland', 'value'=>'SCO'),
			array('text'=>'Senegal', 'value'=>'SEN'),
			array('text'=>'Seychelles', 'value'=>'SEY'),
			array('text'=>'Sierra Leone', 'value'=>'SAL'),
			array('text'=>'Singapore', 'value'=>'SGP'),
			array('text'=>'Slovakia', 'value'=>'SLO'),
			array('text'=>'Slovenia', 'value'=>'SLA'),
			array('text'=>'Solomon Islands', 'value'=>'SOL'),
			array('text'=>'Somalia', 'value'=>'SOM'),
			array('text'=>'Somaliland', 'value'=>'SOD'),	
			array('text'=>'South Africa', 'value'=>'SOA'),
			array('text'=>'Spain', 'value'=>'SPA'),
			array('text'=>'Sri Lanka', 'value'=>'SRI'),
			array('text'=>'Sudan', 'value'=>'SUD'),
			array('text'=>'Suriname', 'value'=>'SUR'),
			array('text'=>'Svalbard and Jan Mayen', 'value'=>'SVA'),
			array('text'=>'Swaziland', 'value'=>'SWA'),
			array('text'=>'Sweden', 'value'=>'SWE'),
			array('text'=>'Switzerland', 'value'=>'SWI'),
			array('text'=>'Syrian Arab Republic', 'value'=>'SYR'),
			array('text'=>'Taiwan', 'value'=>'TWI'),
			array('text'=>'Tanzania', 'value'=>'TAN'),
			array('text'=>'Thailand', 'value'=>'THA'),
			array('text'=>'Tibet', 'value'=>'TIB'),
			array('text'=>'Togo', 'value'=>'TOG'),
			array('text'=>'Tonga', 'value'=>'TON'),
			array('text'=>'Trinidad and Tobago', 'value'=>'TRI'),
			array('text'=>'Tunisia', 'value'=>'TUN'),
			array('text'=>'Turkey', 'value'=>'TKY'),
			array('text'=>'Turkmenistan', 'value'=>'TRK'),
			array('text'=>'Turks and Caicos Islands', 'value'=>'TCI'),
			array('text'=>'Uganda', 'value'=>'UGD'),
			array('text'=>'Ukraine', 'value'=>'UKR'),
			array('text'=>'United Arab Emirates', 'value'=>'UAE'),
			array('text'=>'United Kingdom', 'value'=>'UNK'),
			array('text'=>'Uruguay', 'value'=>'URU'),
			array('text'=>'Uzbekistan', 'value'=>'UZB'),
			array('text'=>'Vatican City State Holy See', 'value'=>'VCS'),
			array('text'=>'Venezuela', 'value'=>'VEN'),
			array('text'=>'Viet Nam', 'value'=>'VIE'),
			array('text'=>'Virgin Islands (British)', 'value'=>'VGB'),
			array('text'=>'Virgin Islands (U.S.)', 'value'=>'VGU'),
			array('text'=>'Wales', 'value'=>'WAL'),
			array('text'=>'Yemen', 'value'=>'YEM'),
			array('text'=>'Yugoslavia', 'value'=>'YUG'),
			array('text'=>'Zambia', 'value'=>'ZAM'),
			array('text'=>'Zimbabwe', 'value'=>'ZIM')
		);

		return $ret;
	}

	function getLocationName($locCode){
		
		$states = $this->getStates();
		$countries = $this->getCountries();

		if(strlen($locCode) == 2){
			foreach($states as $s){
				if(in_array($locCode, $s)){
					return $s['text'].", USA";
				}
			}
		}

		if(strlen($locCode) == 3){
                        foreach($countries as $c){
                                if(in_array($locCode, $c)){
                                        return $c['text'];
                                }
                        }
                }	
		
		return "Location Code not found!";
	}
}
?>
