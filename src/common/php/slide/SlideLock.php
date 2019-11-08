<?php

namespace libresignage\common\php\slide;

use libresignage\common\php\Config;
use libresignage\common\php\exportable\Exportable;
use libresignage\common\php\auth\Session;
use libresignage\common\php\Util;

/**
* SlideLock object for locking slides.
*/
final class SlideLock extends Exportable {
	static $PRIVATE = [
		'session_id',
		'expire'
	];
	static $PUBLIC = [
		'session_id',
		'expire'
	];

	private $session_id = NULL;
	private $expire = NULL;

	public function __construct(Session $session = NULL) {
		if ($session !== NULL) {
			$this->session_id = $session->get_id();
			$this->expire = time() + Config::config('SLIDE_LOCK_MAX_AGE');
		}
	}

	public function __exportable_set(string $name, $value) {
		$this->{$name} = $value;
	}

	public function __exportable_get(string $name) {
		return $this->{$name};
	}

	public function __exportable_version(): string {
		return implode(
			'.',
			Util::parse_version_string(Config::config('LS_VER'))
		);
	}

	/**
	* Check whether a SlideLock has expired.
	*
	* @return bool TRUE = Expired, FALSE = Not expired.
	*/
	public function is_expired(): bool {
		if (empty($this->session_id)) { return TRUE; }
		$s = Session::from_id($this->session_id);
		return $s === NULL || time() > $this->expire;
	}

	/**
	* Check whether a SlideLock is owner by a session.
	*
	* @param Session $session The Session to check against.
	*
	* @return bool TRUE if $session owns lock, FALSE otherwise.
	*/
	public function is_owned_by(Session $session): bool {
		return $this->session_id === $session->get_id();
	}

	public function get_session_id() { return $this->session_id; }
	public function get_expire() { return $this->expire; }
}
