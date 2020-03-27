<?php
namespace ff\mail;

interface MailInterface
{
    public function send($toAddress, $subject, $content);
    public function config($config);
}
