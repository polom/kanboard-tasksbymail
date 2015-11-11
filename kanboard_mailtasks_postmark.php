<?php
require('kanboard_mailtasks_config.inc.php');
require('kanboard_mailtasks_helpers.php');

$projects = array();
$tasks = array();

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
    $header = imap_header($imap_client, $email_id);
    $body_text = imap_fetchbody($imap_client, $email_id,1);
    $body_html = imap_fetchbody($imap_client, $email_id,2);

    // Check if 'mail_prefix' if found in the TO field
    if (strpos($header->to[0]->mailbox,$mail_prefix) !== false){

      // Yes, so this mail is for us. Now let's get the project identifier :
      $project_identifier = str_replace($mail_prefix,'',$header->to[0]->mailbox);
      // Prepare json payload :
      $json =  json_encode(array("From"=>$header->sender[0]->mailbox.'@'.$header->sender[0]->host,"Subject"=>$header->subject,"MailboxHash"=>$project_identifier,"TextBody"=>$body_text,"HtmlBody"=>$body_html));

      // We need to POST this payload to the postmark webhook of our Kanboard :
      $ch = curl_init($postmark_webhook_url);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      $response = curl_exec($ch);
      // Response will be PARSED or FAILED depending on success or failure
      do_debug('Task "'.$header->subject.'" - '.$response);
      curl_close($ch);
    } else {
      do_debug("Ignored message (no project identifier found)");
    }
  }
} else {
  do_debug('No mails');
}
?>