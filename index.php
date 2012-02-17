<?php

/**
  * Title: ISR IP Adress Mask for SMF 
  * Version : 1.3.3.7
  * Description: IP Adress Mask for SMF it's an semi-automatic patching tool for Simple Machine Forum which masks all real IPs of users that are belonging to some groups from a SMF drived-by website. You can include or exclude an unlimited number of groups for being protected by IPAM 4 SMF. Even more, you can choose even the mask (it could be a dynamic set) that will patch users IPs. IPAM 4 SMF have options like installation guider, automatic backup for modified files, patch remover. 
  * Authors: Full installer by Andrei Avădănei & patch by kNight
  * URL : http://insecurity.ro
  * License: GNU GENERAL PUBLIC LICENSE, Version 3, 29 June 2007 
  *
  **/
  
class IPAM_SMF { 

  public function __construct()
  {
	 //Nothing goes here!
	 if(!file_exists('template.tpl')) die('Could not load template. Please download again the app.');
  }
  
  public function run()
  { 
	  $_GET['id']      = !isset($_GET['id'])?1:(int)$_GET['id'];
	  $_POST['submit'] = !isset($_POST['submit'])?FALSE:TRUE;
	  
	  if(in_array($_GET['id'],array(1,2,3,4)))
	  {
		 if($_POST['submit'] == TRUE)
			$this->work($_GET['id']);
		else
			$this->render($_GET['id']);
	  } 
  }
  
  private function isPatched($root = '/forum')
  {
	  $file = $root.DIRECTORY_SEPARATOR.'Settings.php';
	  return (file_exists($file) && stripos(file_get_contents($file),'IPAM_SMF')!== FALSE);
  }
  
  private function work($id)
  {
	  switch($id)
	  {
		  case 1: //check if is already patched
		  	if(isset($_POST['root']))
			{				
				if(!is_dir($_POST['root'])) return $this->error(2);
				if($this->isPatched($_POST['root']))
					$this->render(1, '', '', 'none', '', '', '', 'Status: <font color="#00FF00">IP Adress Mask for SMF allready installed. <a href="?id=4&root='.urlencode($_POST['root']).'">Remote patch!</a></font>');					 
				else
					$this->render(1, '', '', 'none', '', '', '', 'Status: <font color="#FF0000">IP Adress Mask for SMF is not installed. <a href="?id=2&root='.urlencode($_POST['root']).'">Install now!</a></font>'); 
				return TRUE; 
			}
			return $this->error(1);
		  break;
		  case 2:
		  	if($this->isPatched($_POST['root']))
				return $this->render(1, '', '', 'none', '', '', '', 'Status: <font color="#00FF00">IP Adress Mask for SMF already installed. <a href="?id=4&root='.urlencode($_POST['root']).'">Remote patch!</a></font>');		
		  	
			if(isset($_POST['root'], $_POST['groups'],$_POST['type'],$_POST['ip']))
			{
				
				if(!in_array($_POST['type'], array('include','exclude'))) return $this->error(1);
				
				//if is a single group || we have more groups separated by comma and they are valid
				if((is_numeric($_POST['groups']) && ($_POST['groups'] = array($_POST['groups']))) || 
					(strpos($_POST['groups'], ',') !== FALSE && sizeof($_POST['groups'] = array_filter(explode(',',$_POST['groups']),create_function('$a','return is_numeric($a);'))))
				  )
				{ 
					
					if(!$this->updateFiles($_POST['root'], implode(',',$_POST['groups']),$_POST['type'],$_POST['ip']))
						return $this->error(4);
					else
						return $this->render(1, '', '', 'none', '', '', '', 'Status: <font color="#00FF00">Your SMF was successfully patched! You can find a backup of the original files in <em>'.getcwd().DIRECTORY_SEPARATOR.'backup'.DIRECTORY_SEPARATOR.'</em>.</font>'); 
				}
				return $this->error(3);
			}
			return $this->error(1);
		  break;
		  
		  case 3:
		  	//todo update patch
		  break;
		  case 4:
		  	if(!$this->isPatched($_GET['root']))
				return $this->render(1, '', '', 'none', '', '', '', 'Status: <font color="#FF0000">IP Adress Mask for SMF is not installed. <a href="?id=2&root='.urlencode($_GET['root']).'">Install now!</a></font>');
			
			if(isset($_POST['submit']))
			{
				$backup = getcwd().DIRECTORY_SEPARATOR.'backup'.DIRECTORY_SEPARATOR;
				$root   = $_GET['root'].DIRECTORY_SEPARATOR;
				if(!is_dir($backup))
					return $this->error(5);
				if(!is_file($backup.'Settings.php.backup') || !is_file($backup.'Security.php.backup') || !is_file($backup.'Load.php.backup') || !is_file($backup.'Subs.php.backup'))
					return $this->error(6);
				$this->restore($root.'Settings.php', $backup.'Settings.php.backup');                                    unlink($backup.'Settings.php.backup');
				$this->restore($root.'Sources'.DIRECTORY_SEPARATOR.'Security.php', $backup.'Security.php.backup');      unlink($backup.'Security.php.backup');
				$this->restore($root.'Sources'.DIRECTORY_SEPARATOR.'Load.php', $backup.'Load.php.backup');              unlink($backup.'Load.php.backup');
				$this->restore($root.'Sources'.DIRECTORY_SEPARATOR.'Subs.php', $backup.'Subs.php.backup');              unlink($backup.'Subs.php.backup');
				return $this->render(1, '', '', 'none', '', '', '', 'Status: <font color="#FF0000">IP Address Mask for SMF was successfully removed! </font>'); 
			}
		  break;
	  }
  }
  
  /* Here all magic things are happenning */
  private function updateFiles($root, $groups, $type, $mask)
  {
	  global $settings_code,$settings_find, $subs_code,$subs_find, $security_find1,
	  		 $security_find2, $security_find3, $security_code1, $security_code2, $security_code3,
			 $load_find1, $load_find2, $load_find3, $load_code1, $load_code2, $load_code3;
			 
	  /*** update Settings.php | add $settings_code at the end of file **/
	  $file = $root.DIRECTORY_SEPARATOR.'Settings.php';
	  if(file_exists($file) && is_writable($file))
	  {
	  	$settings_code = str_replace(array('###groups###','###type###','###mask###'),array($groups,$type,$mask),$settings_code);
	  	$this->backup($file);
	  	file_put_contents($file, str_replace($settings_find,$settings_code."\r\n".$settings_find,file_get_contents($file)));
	  } else return false; 
	  
	  /*** update Sources/Subs.php | add under line 432, just after "global $modSettings, $user_info, $smcFunc;": **/
	  $file = $root.DIRECTORY_SEPARATOR.'Sources/Subs.php';
	  if(file_exists($file) && is_writable($file))
	  {
		  $this->backup($file);
		  file_put_contents($file, str_replace($subs_find,$subs_find."\r\n".$subs_code."\r\n",file_get_contents($file)));
	  } else return false; 
	  
	 /*** update Sources/Security.php 
	 	1. Adauga, linia ~231, dupa "... || (isset($user_info['email'], $_SESSION['ban']['email']) && $_SESSION['ban']['email'] != $user_info['email'])) {":
			$IPEU = leetIP($user_info['groups']);
		2. Replace, linia ~238:
			In loc de: 'ip' => $user_info['ip'],
			Pui: 'ip' => ($IPEU ? $IPEU : $user_info['ip']),
		3. In loc de: 'ip2' => $user_info['ip2'],
			Pui: 'ip2' => ($IPEU ? $IPEU : $user_info['ip2']), **/ 
			
 	  $file = $root.DIRECTORY_SEPARATOR.'Sources/Security.php';
	  if(file_exists($file) && is_writable($file))
	  {
		  $this->backup($file);
		  $content = file_get_contents($file);
		  $content = str_replace($security_find1, $security_code1."\r\n".$security_find1, $content);
		  $content = str_replace(array($security_find2, $security_find3),array($security_code2, $security_code3),$content);
		  file_put_contents($file, $content);
	  } else return false; 
	  
	  /*** update Sources/Load.php
			Adauga, Linia 486 inainte de "// Set up the $user_info array.":
				$IPEU = leetIP($user_info['groups']);
				if($IPEU)
				{
					updateMemberData($id_member, array('member_ip' => $IPEU, 'member_ip2' => $IPEU));
					$_SESSION['ban']['ip'] = $IPEU;
					$_SESSION['ban']['ip2'] = $IPEU;
				}
			
			Replace, linia ~500:
			In loc de: 'ip' => $_SERVER['REMOTE_ADDR'],
			Pui: 'ip' => ($IPEU ? $IPEU : $_SERVER['REMOTE_ADDR']),
			
			In loc de: 'ip2' => $_SERVER['BAN_CHECK_IP'],
			Pui: 'ip2' => ($IPEU ? $IPEU : $_SERVER['BAN_CHECK_IP']),
				
			**/ 
	  $file = $root.DIRECTORY_SEPARATOR.'Sources/Load.php';
	  if(file_exists($file) && is_writable($file))
	  {
		  $this->backup($file);
		  $content = file_get_contents($file);
		  $content = str_replace($load_find1, $load_code1."\r\n".$load_find1, $content);
		  $content = str_replace(array($load_find2, $load_find3),array($load_code2, $load_code3),$content);
		  file_put_contents($file, $content);
	  } else return false;
	   
	  return true;
  }
  
  //assume that file exists | will create a $file.backup
  private function backup($file)
  {
	  if(!is_dir(getcwd().DIRECTORY_SEPARATOR.'backup'))
	  	 mkdir(getcwd().DIRECTORY_SEPARATOR.'backup');
	  
	  $fh = fopen(getcwd().DIRECTORY_SEPARATOR.'backup'.DIRECTORY_SEPARATOR.basename($file).'.backup','w');
	  if(!$fh)return; 
	  fwrite($fh, file_get_contents($file));
	  fclose($fh);
  }
  
  //assume that file exists
  private function restore($filec, $filen)
  {
	  $fh = fopen($filec,'w');
	  if(!$fh)return; 
	  fwrite($fh, file_get_contents($filen));
	  fclose($fh);
  }
  
  private function render($id, $title = '', $small_title = '', $display_errors = 'none', $errors = '', $content = '', $action = '', $shortcuts = '')
  { 
	  switch($id)
	  {
		  case 1:
		  	$title  = ' - step 1';
			$small_title = 'Step 1 - check if your SMF is not patched';
		  	$action = '?id=1';
			$content = ' <dl id="post_header">
            <dt> <span id="caption_subject">Forum path</span> </dt>
            <dd>
              <input type="text" name="root" value="'.(($x = @htmlentities($_POST['root'],ENT_QUOTES))==''?$_SERVER["DOCUMENT_ROOT"].'forum':$x).'" tabindex="1" size="65" maxlength="80" class="input_text">
            </dd>
          </dl>
          <hr class="clear">
          <p class="smalltext" id="shortcuts"> '.$shortcuts.' </p>
          <p id="post_confirm_buttons">
            <input type="submit" value="Check" tabindex="3" name="submit" class="button_submit">
          </p>';
		  
		  break;
		  case 2://url decode for root
		  		$title  = ' - step 2';
				$small_title = 'Step 2 - Let\'s do some magic!';
				$action = '?id=2&root='.@$_GET['root'];
				$shortcuts .= '<br /><br /><em> * Groups should be numeric and separated by comma. <br /> ** If value is  "include", the script will change IPs for users that are in nominated groups, else if is "exclude" will patch IPs only for users who are not in selected groups. </em>';
				$content = ' <dl id="post_header">
				<dt> <span id="caption_subject">Forum path</span> </dt>
				<dd>
				  <input type="text" name="root" value="'.htmlentities(urldecode(@$_GET['root']),ENT_QUOTES).'" tabindex="1" size="65" maxlength="80" class="input_text">
				</dd>
				<dt> <span id="caption_subject">Mask</span> </dt>
				<dd>
				  <input type="text" name="ip" value="'.(($x = @htmlentities($_POST['mask'],ENT_QUOTES))==''?'133.37.0.0':$x).'" tabindex="1" size="65" maxlength="80" class="input_text">
				</dd>
				<dt> <span id="caption_subject">Groups*</span> </dt>
				<dd>
				  <input type="text" name="groups" value="'.(($x = @htmlentities($_POST['groups'],ENT_QUOTES))==''?'':$x).'" tabindex="1" size="65" maxlength="80" class="input_text">
				</dd>
				<dt> <span id="caption_subject">Type**</span> </dt>
				<dd>
				  <select name="type">
				   <option value="include">Include Groups</option>
				   <option value="exclude">Exclude Groups</option>
				  </select>
				   
				</dd>
			  </dl>
			  <hr class="clear">
			  <p class="smalltext" id="shortcuts"> '.$shortcuts.' </p>
			  <p id="post_confirm_buttons">
				<input type="submit" value="Patch" tabindex="3" name="submit" class="button_submit">
				<span style="float:right;"><input type="button" value="Back" tabindex="4" name="back" class="button_submit" onclick="history.back();"></span>
			  </p>';
			  
		  break;
		  
		  case 3:
		  	 //todo
		  break; 
		  case 4:
		        $title  = ' - remote patch';
				$small_title = 'Remove IPAM 4 SMF patch';
				$action = '?id=4&root='.@$_GET['root'];
				$shortcuts .= '<br /><br /><em> * You should have your backed up files in /backup folder of the IPAM 4 SMF patcher directory. </em>';
				$content = ' <dl id="post_header">
				 <span> Are you sure you want to remove IPAM 4 SMF patch? </span>
			  </dl>
			  <hr class="clear">
			  <p class="smalltext" id="shortcuts"> '.$shortcuts.' </p>
			  <p id="post_confirm_buttons">
				<input type="submit" value="Yes" tabindex="3" name="submit" class="button_submit">
				<span><input type="button" value="No" tabindex="4" name="no" class="button_submit" onclick="document.location=\'index.php\';"></span>
			  </p>';
		  break;
	  }
	  ob_start();
	  include_once('template.tpl');
	  $output = ob_get_contents();
	  ob_end_clean();
	  echo $output;
	  return TRUE;
  }
  
  private function error($errID)
  {
	  $errorMessages = array(0 => 'Invalid request.',
	  						 1 => 'Invalid data sent.',
							 2 => 'This is not a directory.',
							 3 => 'Invalid group ids. They should be numeric.',
							 4 => 'There were several problems. Please check if we have read/write permissions.');
							 
	  $msg = isset($errorMessages[$errID])?$errorMessages[$errID]:$errorMessages[0];
	  $this->render($_GET['id'], '', '', '', $msg.' You will be redirected in 5 seconds. <meta http-equiv="Refresh" content="5;url=index.php?id='.$_GET['id'].'">');
	  return FALSE;
  }
}
  
//replaces  
$settings_code = '

/*** IPAM_SMF Patch - IP Adress Mask for SMF injection - http://insecurity.ro ***/

$leetIP       = "###mask###";          # The mask
$leetIPType   = "###type###";          # If value is  "include", the script will change IPs for users that are in $leetIPGroups, else if is "exclude" will patch IP only for users who are not in $leetIPGroups.
$leetIPGroups = array(###groups###);   # Group ID\'s
if(!function_exists("leetIP"))
{
	function leetIP($groups = false)
	{
		global $leetIP, $leetIPType, $leetIPGroups;
	    
		
		if(!$groups)
			return false;
		if(!is_array($groups))
			return false;
	    if(strrpos($leetIP, ".") !== FALSE && strrpos($leetIP, "*") !== FALSE)
		{
			$leetIPv = explode(".", $leetIP);
			if(sizeof($leetIPv) == 4) 
			{
				$orIPv = @explode(".", $_SERVER["REMOTE_ADDR"]);
				$last  = sizeof($orIPv) == 4 ? ($orIPv[0] + $orIPv[1] + $orIPv[2] + $orIPv[3])%256 : rand(1,256);
				if($leetIPv[3] == "*") $leetIPv[3] = $last;
				if($leetIPv[2] == "*") $leetIPv[2] = rand(1,256);
				if($leetIPv[2] == "*") $leetIPv[1] = rand(1,256);
				if($leetIPv[2] == "*") $leetIPv[0] = rand(1,256);
				$leetIP = implode(".",$leetIPv);
			}
		}
		
		$groupsCount       = count($groups);
		$leetIPGroupsCount = count($leetIPGroups);
	
		$isInGroups = false;
		$i = $j = 0;
	
		while(!$isInGroups && $i < $groupsCount)
		{
			$j = 0;
			while(!$isInGroups && $j < $leetIPGroupsCount)
			{
				if(intval($groups[$i]) == intval($leetIPGroups[$j]))
				{
					$isInGroups = true;
				}
				$j++;
			}
			$i++;
		}
	
		if($leetIPType == \'include\')
		{
			if($isInGroups) return $leetIP;
			else return false;
		} else {
			if($isInGroups) return false;
			else return $leetIP;
		}
	
	}
}
';

$settings_find = '?>';

$subs_code = '
	$IPEU = leetIP($user_info["groups"]);
	if($IPEU)
	{
		if(isset($data["member_ip"])) $data["member_ip"] = $IPEU;
		if(isset($data["member_ip2"])) $data["member_ip2"] = $IPEU;
	}';
	
$subs_find = 'global $modSettings, $user_info, $smcFunc;';

$security_code1 = '$IPEU = leetIP($user_info["groups"]);';
$security_code2 = "'ip' => (\$IPEU ? \$IPEU : \$user_info['ip']),";
$security_code3 = "'ip2' => (\$IPEU ? \$IPEU : \$user_info['ip2']),";

$security_find1 = '// Innocent until proven guilty.  (but we know you are! :P)';
$security_find2 = "'ip' => \$user_info['ip'],";
$security_find3 = "'ip2' => \$user_info['ip2'],";

$load_code1 = '
				$IPEU = leetIP($user_info["groups"]);
				if($IPEU)
				{
					updateMemberData($id_member, array("member_ip" => $IPEU, "member_ip2" => $IPEU));
					$_SESSION["ban"]["ip"] = $IPEU;
					$_SESSION["ban"]["ip2"] = $IPEU;
				}';
$load_code2 = "'ip' => (\$IPEU ? \$IPEU : \$_SERVER['REMOTE_ADDR']),";
$load_code3 = "'ip2' => (\$IPEU ? \$IPEU : \$_SERVER['BAN_CHECK_IP']),";

$load_find1 = '// Set up the $user_info array.';
$load_find2 = "'ip' => \$_SERVER['REMOTE_ADDR'],";
$load_find3 = "'ip2' => \$_SERVER['BAN_CHECK_IP'],";


  //run IPAM_SMF
$IPAM_SMF = new IPAM_SMF();
$IPAM_SMF->run();
?>