# kanboard-tasksbymail

Create Kanboard tasks by email using your own mailboxes. 

Two experimental approches :

- kanboard_mailtasks_postmark.php uses Kanboard specialized Postmark webhook ;
- kanboard_mailtasks_postmark.php uses the Kanboard jsonRPC-API and createTask() method.

jsonRPC-API offer a more generic approach and can be used to create other calls.
Postmark webhook use offer a more flexible body creation (parses HTML to Markdown and gives nicer results), and uses send address to give task an author.

You'll need to :

- assign a unique identified to your project (see Kanboard project edition page) ;
- send and email to yourmailbox+yourpojectIdentifier@yourdomain.tld

If you use the Postmark webhook, it will check the send adress and verify that :

- the address is associated to a real user ;
- the user is associated to the specified project.

Project identifier doesn't seem to be case sensitive with Postmark webHook but seems to be uppercased when calling API (hence the strtoupper() use). 

Both scripts use the same config file.

Copy kanboard_mailtasks_config.inc.php.dist to kanboard_mailtasks_config.inc.php and enter your config values.
jsonrpc* variables can be ignored if you don't use the jsonRPC-API version

This is a rough first attempt. It is fully functionnal, as far as I have tested, not meaning exempt of bugs, and certainly missing basic functionnalities.
It will scan all mails in the given mailbox, marking them read regardless of whether they are indeed related to your Kanboard interactions.
It will not remember any "last seen mail" or something like that. If it get stuck / crashes, the mail beeing read will probably never become a task.
