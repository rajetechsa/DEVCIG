<?php

error_reporting(E_ALL ^ E_NOTICE);

//Variables
$my_email = "luisenrique.lopez1811@gmail.com";
$from_email = "";
$subject = "Sugerencias CEDUCA CIG";
$site_url = "https://www.cig.org.gt/";
$site_name = "CIG";

$errors = array();

//Remove $_COOKIE elements from $_REQUEST.
if(count($_COOKIE)) { 
  foreach(array_keys($_COOKIE) as $value) {
    unset($_REQUEST[$value]);
  }
}

// Validate Email
if(isset($_REQUEST['email']) && !empty($_REQUEST['email'])) {
  $_REQUEST['email'] = trim($_REQUEST['email']);
  if(substr_count($_REQUEST['email'],"@") != 1 || stristr($_REQUEST['email']," ")) {
    $errors[] = "La dirección de correo electrónico es inválida";
  }else {
    $exploded_email = explode("@",$_REQUEST['email']);
    if(empty($exploded_email[0]) || strlen($exploded_email[0]) > 64 || empty($exploded_email[1])) { 
      $errors[] = "La dirección de correo electrónico es inválida";
    }else { 
      if(substr_count($exploded_email[1],".") == 0){
        $errors[] = "La dirección de correo electrónico es inválida";
      }else { 
        $exploded_domain = explode(".",$exploded_email[1]);
        if(in_array("",$exploded_domain)) { 
          $errors[] = "La dirección de correo electrónico es inválida";
        }else { 
          foreach($exploded_domain as $value) { 
            if(strlen($value) > 63 || !preg_match('/^[a-z0-9-]+$/i',$value)) {
              $errors[] = "La dirección de correo electrónico es inválida"; break;
            }
          }
        }
      }
    }
  }
}

// Check referrer is from same site.
if(!(isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) && stristr($_SERVER['HTTP_REFERER'],$_SERVER['HTTP_HOST']))){$errors[] = "You must enable referrer logging to use the form";}

// Check for a blank form.
function recursive_array_check_blank($element_value) {
  global $set;
  if(!is_array($element_value)) {
    if(!empty($element_value)) { 
      $set = 1;
    }
  }else {
    foreach($element_value as $value) { 
      if($set) {
        break;
      } 
      recursive_array_check_blank($value);
    }
  }
}

recursive_array_check_blank($_REQUEST);

if(!$set) {
  $errors[] = "Estas enviando un mensaje en blanco";
}

unset($set);

// Display any errors and exit if errors exist.
if(count($errors)){foreach($errors as $value){print "$value<br>";} exit;}

if(!defined("PHP_EOL")){define("PHP_EOL", strtoupper(substr(PHP_OS,0,3) == "WIN") ? "\r\n" : "\n");}

// Build message.
function build_message($request_input) {
  if(!isset($message_output)) {
    $message_output ="";
  }
  if(!is_array($request_input)) { 
    $message_output = $request_input;
  }else { 
    foreach($request_input as $key => $value) {
      if(!empty($value)) { 
        if(!is_numeric($key)) {
          $message_output .= str_replace("_"," ",ucfirst($key)).": ".build_message($value).PHP_EOL.PHP_EOL;
        }else { 
          $message_output .= build_message($value).", ";
        }
      }
    }
  }
  return rtrim($message_output,", ");
}

  $message = build_message($_REQUEST);
  $message = $message . PHP_EOL.PHP_EOL."-- ".PHP_EOL."Colegio de Ingenieros de Guatemala https://www.cig.org.gt/";
  $message = stripslashes($message);
  $subject = stripslashes($subject);

  if($from_email) {
    $headers = "From: " . $from_email;
    $headers .= PHP_EOL;
    $headers .= "Reply-To: " . $_REQUEST['email'];
  }else {
    $from_name = "";
    if(isset($_REQUEST['name']) && !empty($_REQUEST['name'])) {
      $from_name = stripslashes($_REQUEST['name']);
    }
    $headers = "From: {$from_name} <{$_REQUEST['email']}>";
  }

  mail($my_email,$subject,$message,$headers);
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="refresh" content="5;url=<?php print $site_url; ?>">
	<title><?php print $site_name; ?></title>
	<style>
		body 		{font-family:Arial, sans-serif; font-size:13px;}
		p 			{line-height:1.5em;}
		#sent-message 	{margin:200px auto 0px; width:400px; background:#efefef; border:1px solid #ccc; padding:20px;
					-moz-border-radius: 10px; 
					-webkit-border-radius: 10px; 
					border-radius:10px;
					-webkit-box-shadow: 0px 3px 3px #eee;
					-moz-box-shadow: 0px 3px 3px #eee;
					box-shadow: 0px 3px 3px #eee;}
	</style>
</head>
<body>
	<div id="sent-message">
		<p><strong>Gracias por tus sugerencias, tu mensaje se ha enviado.</strong></p>		
	</div>
</body>
</html>

