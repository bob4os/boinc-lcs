<?php

/**
* BOINC LCS Live Client State
*
* Tested on Apache/2.2.6 and PHP/5.2.4 and Lighttpd/1.4.19 with PHP/5.2.5 (FastCGI)
*
* LICENSE: This source file is subject to the GNU GENERAL PUBLIC LICENSE v3.0
* which is available at: http://www.gnu.org/licenses/gpl-3.0.txt
*
@package    BOINC-LCS
@version    3.2.1
@author     Willy Babernits <wbabernits@onenext.de>
@link       http://www.onenext.de/
*/

	is_readable('config.php') ? require_once 'config.php' : die("ERROR: Cannot read config") ;

	$version = '3.2.1';

	if($refresh<10)
		$refresh = 10;

	function cleanup($str) {
		return addslashes(strip_tags($str));
	}

	function calculate($bytes,$divider,$add) {
		return round((number_format((double)$bytes, 0, '', '') / $divider),$add);
	}

	function boincstamptodate($format,$stamp) {
		return date($format,number_format((double)$stamp, 0, '', ''));
	}

	function secondstodate($seconds) {

	    $periods = array(
	        'months'  => 2629743,
	        'weeks'   => 604800,
	        'days'    => 86400,
	        'hours'   => 3600,
	        'minutes' => 60,
	        'seconds' => 1
	    );

	    $durations = array();

	    foreach ($periods as $period => $seconds_in_period) {
	        if ($seconds >= $seconds_in_period) {
	            $durations[$period] = floor($seconds / $seconds_in_period);
	            $seconds -= $durations[$period] * $seconds_in_period;
	        }
	        else {
	            $durations[$period] = 0;
	        }
	    }

	    return $durations;

	}

	if(!function_exists('simplexml_load_file') or phpversion() < 5)
		die('ERROR: You need <a href="http://de.php.net/">PHP/5</a> and <a href="http://de2.php.net/manual/en/ref.simplexml.php">SimpleXML</a>.');

	echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <title>BOINC LCS <?php echo $version; ?></title>
<?php
	if(isset($_GET["refresh"])) {
?>
  <meta http-equiv="refresh" content="<?php echo $refresh.'; URL='.$_SERVER["PHP_SELF"].'?'.cleanup($_SERVER['QUERY_STRING']); ?>" />
<?php
	}
?>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <link rel="stylesheet" type="text/css" href="style.css" />
  <script type="text/javascript" src="scripts.js"></script>
  <style type="text/css">
    div.tdh, div.td {
	float: left;
    }
  </style>
</head>
<body>
<div id="header">BOINC Live Client State</div>
<div id="time"><?php echo date("r")." - "; echo isset($_GET["refresh"]) ? '<a class="bright" href="'.$_SERVER["PHP_SELF"].'">Turn autorefresh off</a>' : '<a class="bright" href="'.$_SERVER["PHP_SELF"].'?refresh">Turn autorefresh on</a>' ; ?></div>
<?php
	$error = '';
	if(is_array($clients)) {
		$i=0;
		foreach($clients as $name=>$client) {
			$elements = @simplexml_load_file($client);
			if(empty($elements)) {
				$error = '<div class="error">ERROR: One or more clients cannot be displayed. Please check your config!</div>';
			} else {
				$cpu_model = explode("[",$elements->host_info->p_model);
?>
<div style="<?php echo $i%$clientsperline==0 ? 'clear:left;' : 'float:left;' ; ?>">
<table cellspacing="0" cellpadding="5">
  <tr class="subsection">
    <th colspan="2"><img align="left" class="icon" src="img/client.png" alt="client" width="16" height="16" />Client Name: <?php echo $name; ?></th>
  </tr>
  <tr class="subsection">
    <th colspan="2"><img align="left" class="icon" src="img/tasks.png" alt="tasks" width="16" height="16" />Active Task/s</th>
  </tr>
<?php
				if(is_object($elements->active_task_set)) {
					foreach($elements->active_task_set->active_task as $task) {

        				// use checkpoint_fraction_done instead of fraction_done if available
        				If(isset($task->checkpoint_fraction_done))
        					$task->fraction_done = $task->checkpoint_fraction_done;

?>
  <tr>
    <td width="150">Project URL</td>
    <td width="310"><a href="<?php echo $task->project_master_url; ?>"><?php echo $task->project_master_url; ?></a></td>
  </tr>
  <tr>
    <td>Result Name</td>
    <td><?php echo $task->result_name; ?></td>
  </tr>
  <tr>
    <td>Work done</td>
    <td><?php echo bcmul($task->fraction_done,100,2); ?> %</td>
  </tr>
  <tr class="noh">
    <td></td>
    <td><div class="fractioncontainer"><div class="fractiondone" style="width:<?php echo bcmul($task->fraction_done,100,0); ?>%;"></div></div></td>
  </tr>
  <tr>
    <td class="projectdivider">Computing time</td>
    <td class="projectdivider"><?php $time = secondstodate(number_format((double)$task->current_cpu_time, 0, '', '')); echo ($time["hours"] < 1 ? '0 Hours ' : $time["hours"] . ' Hours ') . ($time["minutes"] < 1 ? '0 Minutes ' : $time["minutes"] . ' Minutes ') . ($time["seconds"] < 1 ? '0 Seconds' : $time["seconds"] . ' Seconds'); ?></td>
  </tr>
<?php
					}
				}
?>
  <tr class="noh">
    <td colspan="2">&#160;</td>
  </tr>
  <tr class="subsection">
    <th colspan="2"><img align="left" class="icon" src="img/computer.png" alt="computer" width="16" height="16" />Computer Info</th>
  </tr>
  <tr>
    <td>Domain Name</td>
    <td><?php echo $elements->host_info->domain_name; ?></td>
  </tr>
  <tr>
    <td>IP</td>
    <td><?php echo $elements->host_info->ip_addr; ?></td>
  </tr>
  <tr>
    <td>Operating System</td>
    <td><?php echo $elements->host_info->os_name." ".$elements->host_info->os_version; ?></td>
  </tr>
  <tr class="subsection">
    <th colspan="2"><img align="left" class="icon" src="img/cpu.png" alt="cpu" width="16" height="16" />CPU</th>
  </tr>
  <tr>
    <td>Number of CPUs</td>
    <td><?php echo $elements->host_info->p_ncpus; ?></td>
  </tr>
  <tr>
    <td>Manufacturer</td>
    <td><?php echo $elements->host_info->p_vendor; ?></td>
  </tr>
  <tr>
    <td>Model</td>
    <td><?php echo $cpu_model[0]; ?></td>
  </tr>
  <tr>
    <td>L2 Cache</td>
    <td><?php echo calculate($elements->host_info->m_cache,1024,2); ?> KB</td>
  </tr>
  <tr class="subsection">
    <th colspan="2"><img align="left" class="icon" src="img/bench.png" alt="bench" width="16" height="16" />Benchmark</th>
  </tr>
  <tr>
    <td>Floating point speed</td>
    <td><?php echo calculate($elements->host_info->p_fpops,1000000,2); ?> million ops/sec</td>
  </tr>
  <tr>
    <td>Integer speed</td>
    <td><?php echo calculate($elements->host_info->p_iops,1000000,2); ?> million ops/sec</td>
  </tr>
  <tr class="subsection">
    <th colspan="2"><img align="left" class="icon" src="img/harddisk.png" alt="harddisk" width="16" height="16" />Harddisk and Memory</th>
  </tr>
  <tr>
    <td>Total disc space</td>
    <td><?php echo calculate($elements->host_info->d_total,1073741824,2); ?> GB</td>
  </tr>
  <tr>
    <td>Free disc space</td>
    <td><?php echo calculate($elements->host_info->d_free,1073741824,2); ?> GB</td>
  </tr>
  <tr>
    <td>Memory</td>
    <td><?php echo calculate($elements->host_info->m_nbytes,1048576,2); ?> MB</td>
  </tr>
<?php
// count projects
	$pcount = count($elements->project);
	$addtxt = $pcount>1 ? ' Projects' : ' Project' ;
?>
  <tr class="subsection">
    <th colspan="2"><img align="left" class="icon" src="img/projects.png" alt="projects" width="16" height="16" /><?php echo $pcount.$addtxt; ?> <img class="fakelink" onclick="boxClose('projects<?php echo $i; ?>');return false;" src="img/hide.gif" alt="hide" /><img class="fakelink" onclick="boxOpen('projects<?php echo $i; ?>');return false;" src="img/show.gif" alt="show" /></th>
  </tr>
  <tr>
    <td class="projects" colspan="2">
      <div class="table" style="display:none;" id="projects<?php echo $i; ?>">
<?php
// project listing
				if(is_object($elements->project)) {
					foreach($elements->project as $project) {
?>
        <div class="tr">
          <div class="tdh">Project Name</div>
          <div class="tdhr"><a href="<?php echo $project->master_url; ?>"><?php echo $project->project_name; ?></a></div>
        </div>
        <div class="tr">
          <div class="td">Username</div>
          <div class="tdr"><?php echo $project->user_name; ?></div>
        </div>
        <div class="tr">
          <div class="td">Team</div>
          <div class="tdr"><?php echo preg_match("/./", $project->team_name) ? $project->team_name : 'No team found' ; ?></div>
        </div>
        <div class="tr">
          <div class="td">Host ID</div>
          <div class="tdr"><a href="<?php echo $project->master_url; ?>show_host_detail.php?hostid=<?php echo $project->hostid; ?>"><?php echo $project->hostid; ?></a></div>
        </div>
        <div class="tr">
          <div class="td">Member since</div>
          <div class="tdr"><?php echo boincstamptodate("d.m.Y",$project->user_create_time); ?></div>
        </div>
        <div class="tr">
          <div class="td">Average credit</div>
          <div class="tdr"><?php echo number_format((double)$project->user_expavg_credit,2,'.',','); ?></div>
        </div>
        <div class="tr">
          <div class="td">Total credit</div>
          <div class="tdr"><?php echo number_format((double)$project->user_total_credit,2,'.',','); ?></div>
        </div>
<?php
					}
				}
?>
      </div>
    </td>
  </tr>
</table>
</div>
<?php
				$i++;
			}
		}
	}

// error message
	echo $error;

?>
<div id="footer">
  <a class="bright" href="http://www.onenext.de/">BOINC LCS <?php echo $version; ?> &#169; 2007-2011 OneNext Solutions</a> | <a class="bright" href="javascript:alert('BOINC LCS <?php echo $version; ?>\n\nReleased under the GNU/GPL License v3.0\nWritten by Willy Babernits 2007/2011 for OneNext Solutions\nPowered by PHP and BOINC\n\nSend bugs, problems, wishes to:\nwbabernits[at]onenext[dot]de\nSubject BOINC LCS')">Credits</a> | <a class="bright" href="http://validator.w3.org/check?uri=referer">Valid XHTML</a> | <a class="bright" href="http://www.famfamfam.com/">Icons by FamFamFam</a>
</div>
</body>
</html>
