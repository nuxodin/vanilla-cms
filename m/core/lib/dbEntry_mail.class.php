<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;
// todo: limit amount of emails to send

class dbEntry_mail extends dbEntry {

	protected function construct() {
		$this->log_id     = liveLog::$id;
		$this->sender     = G()->SET['qg']['mail']['defSender']->v;
		$this->sendername = G()->SET['qg']['mail']['defSendername']->v;
	}
	function addTo($email, $name='', $data=[]) {
		$email = strtolower(trim($email));
		D()->mail_recipient->ensure([
			'mail_id' => $this,
			'email'   => $email,
			'name'    => $name,
			'data'    => serialize($data)
		]);
	}
	function addFile($file, $inline=0) {
		// if (is_object($file)) { // add dbfile
		// 	$name = $file->name;
		// 	$file = $file->path;
		// 	$type = $file->mime;
		// }
		$values = is_array($file) ? $file : ['path'=>$file, 'inline'=>$inline];
		$values['mail_id'] = $this;
		$values['hash']    = sha1($values['path']);
		D()->mail_attachment->ensure($values);
		return $values['hash'];
	}
	function getHtml($Recipient=null, $ZendMail=null) {
		$html = $this->html;
		$data = $Recipient ? unserialize($Recipient->data) : [];

		qg::fire('mail::gethtml', ['Mail'=>$this, 'Recipient'=>$Recipient, 'html'=>&$html, 'data'=>&$data, 'ZendMail'=>$ZendMail]);

		if ($data) {
			$T = new template($data);
			$html = $T->renderMarker($html);
		}
		return $html;
	}
	function getText($Recipient=null) {
		$data = $Recipient ? unserialize($Recipient->data) : [];
		$text = $this->text;
		if ($data) {
			$T = new template($data);
			$text = $T->renderMarker($text);
		}
		return $text;
	}
	function send() {
		$this->save(); // save
		$SET = G()->SET['qg']['mail'];
		require_once sysPATH.'Zend/Mail.php';

		if (G()->SET['qg']['mail']['smtp']['host']->v) {
			require_once sysPATH.'Zend/Mail/Transport/Smtp.php';
			$config = ['ssl' => 'tls'];
			if ($SET['smtp']['port']->v) {
				$config['port'] = $SET['smtp']['port']->v;
			}
			if ($SET['smtp']['username']->v) {
				$config['auth'] = 'login';
	            $config['username'] = $SET['smtp']['username']->v;
				$config['password'] = $SET['smtp']['password']->v;
			}
			$tr = new \Zend_Mail_Transport_Smtp($SET['smtp']['host']->v, $config);
		} else {
			require_once sysPATH.'Zend/Mail/Transport/Sendmail.php';
			$tr = new \Zend_Mail_Transport_Sendmail('-f'.$SET['replay']->v);
		}

		\Zend_Mail::setDefaultTransport($tr);

		$toWebmaster = debug ? $SET['on debugmode to']->v : false;

		foreach (D()->mail_recipient->selectEntries("WHERE mail_id = ".$this." AND sent = 0") as $Item) {
			$ZendMail = new \Zend_Mail('utf-8');
			$ZendMail->setFrom($this->sender, $this->sendername ?: $this->sender);
			$this->reply_to && $ZendMail->setReplyTo($this->reply_to, null);
			$ZendMail->setSubject(($toWebmaster?'Debug! ':'').$this->subject);

			$html = $this->getHtml($Item, $ZendMail);
			// if (strpos($html, 'cid:') !== false) { // dirty hack for thunderbird, it needs multipart/related for inline-images
			// 	$ZendMail->setType(\Zend_Mime::MULTIPART_RELATED); // ok?
			// }
			$ZendMail->setBodyHtml(($toWebmaster?'original receiver :'.$Item->email.'<br><br>':'').$html);
			$ZendMail->setBodyText(($toWebmaster?'original receiver :'.$Item->email."\n\n"    :'').$this->getText($Item, $ZendMail));
			$ZendMail->addTo($toWebmaster ?: $Item->email, $Item->name);

			foreach (D()->query("SELECT * FROM mail_attachment WHERE mail_id = ".$this->id) as $vs) {
				$At = $ZendMail->createAttachment(file_get_contents($vs['path']));
				$At->filename    = $vs['name']   ?: basename($vs['path']);
				$At->type        = $vs['type']   ?: File::extensionToMime(preg_replace('/.*\.([^.]+$)/', '$1', $vs['path']));
				$At->disposition = $vs['inline'] ? \Zend_Mime::DISPOSITION_INLINE : \Zend_Mime::DISPOSITION_ATTACHMENT;
				$At->id          = $vs['hash'];
			}

			$sent = false;
			try {
				time_limit(120);
				$sent = $ZendMail->send();
			} catch (\Exception $e) {
				trigger_error('mail sending failed :'.$e);
			}
			if ($sent) {
				$Item->sent = time();
				$Item->save(); // save
			}
		}
	}
}
