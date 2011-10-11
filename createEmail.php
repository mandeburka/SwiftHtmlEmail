<?php
require_once 'swift/lib/swift_required.php';

if ($argc < 2)
  die('Provide filename');
$mailFilePath = $argv[1];

print $mailFilePath;

if (!file_exists($mailFilePath))
  die ('File doesn\'t exixts');

$body = fread(fopen($mailFilePath, 'r'), filesize($mailFilePath));

$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'sslv3')
        ->setUsername('')
        ->setPassword('');

$mailer = Swift_Mailer::newInstance($transport);

$message = Swift_Message::newInstance(basename($mailFilePath))
  ->setFrom(array('mandeburka@gmail.com'))
  ->setTo(array('agavkalyuk@malkosua.com', 'aliaxej@gmail.com', 'alaksiej.lavoncyk@tol.org'));

preg_match_all('/(background="([^<>\"]*)|src="([^<>\"]*)|url\(([^\(\)]*)\))/', $body, $matches);

print_r($matches);

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
print_r($mailer->send($message));