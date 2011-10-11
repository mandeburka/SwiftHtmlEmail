#!/usr/bin/php
<?php
require_once 'swift/lib/swift_required.php';
require_once 'config.php';
if ($argc < 2)
  die("Provide filename\n");
$mailFilePath = $argv[1];

if (!file_exists($mailFilePath))
  die ("File doesn't exists\n");

$body = fread(fopen($mailFilePath, 'r'), filesize($mailFilePath));

$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'sslv3')
        ->setUsername($mailer_config['gmail_username'])
        ->setPassword($mailer_config['gmail_password']);

$mailer = Swift_Mailer::newInstance($transport);

$message = Swift_Message::newInstance(basename($mailFilePath))
  ->setFrom($mailer_config['sender'])
  ->setTo($mailer_config['recipients']);

preg_match_all('/(background="([^<>\"]*)|src="([^<>\"]*)|url\(([^\(\)]*)\))/', $body, $matches);

if (count($matches) > 2){
  $images = array();
  for ($i = 2; $i < count($matches); $i++){
    foreach ($matches[$i] as $key => $value)
      if (!empty($value)){
        $path = dirname($mailFilePath).'/'.$value;
        $handle = fopen($path, "r");
        $data = fread($handle, filesize($path));
        $src = $message->embed(Swift_Image::newInstance($data, basename($path), 'image/png'));
        $body = str_replace($value, $src, $body);
        $images[$value] = $src;
      }
  }
}

print_r($images);

$message->setBody($body, 'text/html');
print "success sents - ".$mailer->send($message);
print "\n";