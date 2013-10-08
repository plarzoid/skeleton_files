<?php 

//default to first tab in the list
if(!in_array($view, $views)){
	$view = $views[0];
}

//echo some table stuff out
echo '<table align="center" style="border-width: 3px 0px;" cellpadding="5px" cellspacing="0px" width="80%">
	<tr>';

//loop through all views, and echo out table code to generate the tab
foreach($views as $v){
      
	if($v == $view){//if this one is the current one, bold the tab's border, and remove bottom to the tab
      		?><td class="current_tab"><?php 
	} else {//else, this isn't the current tab, so thin borders, and separate it from rest of page
        	?><td class="tab"><?php 
	}
        
	?><a href='<?php echo "index.php?view=".$v;?>'?> <?php echo preg_replace("/_/", " ", $v);?></a></td><?php 
}

//inject filler tab to shove everything to the left
?><td class="tab_filler"><?php 

//close off the rows the tabs are in
?></tr><?php 

//now, give the rest of the page a nice big space to work in
?>

<tr><td class="current_page" colspan=<?php echo count($views)+1; ?>>

<?php 

$tab_template = "include/templates/" . $view . ".php";

//include the template associated with the current view
if(file_exists($tab_template)){
	include($tab_template);
} else {
	include("include/templates/tpl_not_found.tpl");
}

//close the table
?>
</td></tr>
</table>

<?php 

//end of createTabs script

?>
