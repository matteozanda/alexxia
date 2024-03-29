<?php
/**
 *	Logout module for ALExxia
 *	
 *	Copyright (c) 2013 Maurizio Carboni. All rights reserved.
 *
 *	This file is part of ALExxia.
 *	
 *	ALExxia is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *	
 *	ALExxia is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with ALExxia.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     alexxia
 * @author      Maurizio Carboni <maury91@gmail.com>
 * @copyright   2013 Maurizio Carboni
 * @license     http://www.gnu.org/licenses/  GNU General Public License
**/
//Include the language file
include(LANG::path().'logout.php');
if (USER::logged()) {
	//Destroy session data
	unset($_SESSION[COOKIE::val('ale_auth')]);
	//Destroy cookies
	COOKIE::set('ale_user');
	COOKIE::set('ale_auth');
	echo $__logged_out.'<script type="text/javascript">setTimeout(function() { location.href = __http_base},2000);</script>';
} else
	echo $__no_logged;
?>