<?php

namespace Jupitern\Slim3\ServiceProviders;
use Jupitern\Slim3\ServiceProviders\ProviderInterface;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer implements ProviderInterface
{

	public static function register($serviceName, array $settings = [])
	{
		app()->getContainer()[$serviceName] = function ($c) use($settings) {
			return function($configsOverride = []) use($settings) {

				$configs = array_merge($settings, $configsOverride);

				$mail = new PHPMailer;
				$mail->CharSet = "UTF-8";
				$mail->isSMTP();
				$mail->isHTML(true);
				$mail->Host = $configs['host'];
				$mail->SMTPAuth = true;
				$mail->Username = $configs['username'];
				$mail->Password = $configs['password'];
				$mail->SMTPSecure = $configs['secure'];
				$mail->Port = $configs['port'];

				$mail->setFrom($configs['from'], $configs['fromName']);

				return $mail;
			};
		};
	}

}