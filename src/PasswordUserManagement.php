<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011-2013 Johannes Binder <j.binder.x@gmail.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Handles user login using the password of the FE user.
 */
class PasswordUserManagement implements UserManagement {

    private $db = null;

    public function loginUser(array &$data) {
        $result = new LoginResult();
        $username = mysql_real_escape_string($data['username']);
        $password = mysql_real_escape_string($data['password']);

        $res = $this->db->exec_SELECTquery(
            '*',
            'fe_users',
            "username = '$username' and password = '$password'");
        $user = $this->db->sql_fetch_assoc($res);
        if ($user == null) {
            $result->setError(1);
        } else {
            $result->setUser($user);
        }
        $this->db->sql_free_result();
        return $result;
    }

    public function init(t3lib_DB &$db, array &$data) {
        $this->db = $db;
    }

    public function reset() {
        // nothing to be done
    }
}

?>
