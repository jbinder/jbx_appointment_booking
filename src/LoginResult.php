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
 * Holds the result of a login request.
 */
class LoginResult { 

    private $error = -1;
    private $errorString = "";
    private $templateVars = array();
    private $redirect = false;
    private $user = null;

    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function getError() {
        return $this->error;
    }

    public function setError($error) {
        $this->error = $error;
    }

    public function getErrorString() {
        return $this->errorString;
    }

    public function setErrorString($errorString) {
        $this->errorString = $errorString;
    }

    public function getTemplateVars() {
        return $this->templateVars;
    }

    public function addTemplateVarEntry($key, $value) {
        $this->templateVars[$key] = $value;
    }

    public function setRedirect($redirect) {
        $this->redirect = $redirect;
    }

    public function getRedirect() {
        return $this->redirect;
    }

}

?>
