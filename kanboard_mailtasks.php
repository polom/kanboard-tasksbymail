<?php
require('kanboard_mailtasks_config.inc.php');
require('kanboard_mailtasks_helpers.php');
require('../vendor/autoload.php');
use JsonRPC\Client;

$projects = array();
$tasks = array();

// Let's call Kanboard
$client = new JsonRPC\Client($jsonrpc_url);
$client->authentication($jsonrpc_auth_name, $jsonrpc_auth_token);
// We need the projects list
$projects_tmp = $client->execute('getAllProjects');

// For each project, get identifier (if existing) and numeric ID
foreach ($projects_tmp as $proj) {
	if ($proj['identifier']){
		$projects[$proj['identifier']]['id'] = $proj['id'];
	}
}

// Now to the mails
$imap_client = imap_open($imap_server, $imap_account, $imap_pass);

if (!$imap_client) {
    echo imap_last_error();
    exit;
}

// Give me the unseen mails list
$emails_list = imap_search($imap_client, 'UNSEEN');

if ($emails_list) {
  // One by one we fetch unseen mails
  foreach ($emails_list as $email_id) {
    do_debug('Open mail '.$email_id);
    $header = imap_header($imap_client, $email_id);
    $body_text = imap_fetchbody($imap_client, $email_id,1);
    $body_html = imap_fetchbody($imap_client, $email_id,2);


    // Check if 'mail_prefix' if found in the TO field
    if (strpos($header->to[0]->mailbox,$mail_prefix) !== false){

      // Yes, so this mail is for us. Now let's get the project identifier :
  		$project_identifier = strtoupper(str_replace($mail_prefix,'',$header->to[0]->mailbox));

      // If we found something we can now go and create the task :
      if (isset($projects[$project_identifier])){
        $task['project_id'] = $projects[$project_identifier]['id'];
        $task['title'] = imap_utf8($header->subject);
        $task['description'] = $body_text;
        $response = $client->createTask($task);
        do_debug('Task "'.$header->subject.'" - '.$response);
      } else {
        do_debug('Task "'.imap_utf8($header->subject).'" not created : project identifier "'.$project_identifier.'" not found');
      }

    } else {
        do_debug('Mail #'.$email_id.'ignored : no project identifier');      
    }
  }
} else {
  do_debug('No mails');
}
?>