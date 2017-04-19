<?php
namespace qg;

class dbEntry_usr extends dbEntry {
	private $_grps = null;
	function grps() {
		if ($this->_grps === null) {
			$this->_grps = [0];
			foreach (D()->query("SELECT grp_id FROM usr_grp WHERE usr_id = '".$this."'") AS $vs)
				$this->_grps[(int)$vs['grp_id']] = (int)$vs['grp_id'];
		}
		return $this->_grps;
	}
}
function Usr($id = null) {
	if ($id === null) {
		$id = $_SESSION['liveUser'] ?? 0;
	} else {
		$id = (int)(string)$id;
	}
	$Usr = D()->usr->Entry($id);

	if ($id == 0) { $Usr->_is = false; $Usr->_full = true; $Usr->superuser = 0; } // performance (save one db-request for guests)

	return $Usr;
}

class dbEntry_log extends dbEntry {
	function Sess() {
		return D()->sess->Entry($this->sess_id);
	}
}

function Sess($id = null) {
	if ($id) return D()->sess->Entry($id);
	!liveSess::$id && trigger_error('Session not initialized') && exit();
	return D()->sess->Entry(liveSess::$id);
}
class dbEntry_sess extends dbEntry {
	function Usr() {
		if (!isset($this->Usr)) $this->Usr = Usr($this->usr_id);
		return $this->Usr;
	}
}

function Client($id = null) {
	if ($id) return D()->client->Entry($id);
	!liveClient::$id && trigger_error('Client not initialized') && exit();
	return D()->client->Entry(liveClient::$id);
}
class dbEntry_client extends dbEntry {
	function Usrs() {
		$Usrs = [];
		foreach (D()->client_usr->selectEntries("WHERE client_id = '".$this->id."' ORDER BY time DESC") as $Usr) {
			$Usrs[$Usr->usr_id] = $Usr;
		}
		return $Usrs;
	}
	function addUsr($id) {
		D()->client_usr->ensure([
			'client_id' => $this->id,
			'usr_id' => $id,
			'time' => time()
		]);
	}
}
class dbEntry_client_usr extends dbEntry {
	function Usr() {
		return D()->usr->Entry($this->usr_id);
	}
}
