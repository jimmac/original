<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2004 Olivier Meunier and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****


function __($str)
{
	return (!empty($GLOBALS['__l10n'][$str])) ? $GLOBALS['__l10n'][$str] : $str;
}

//why is this a class damnit!
//probably just to structure the functions
class l10n
{
	function init()
	{
		$GLOBALS['__l10n'] = array();
		$GLOBALS['__l10n_files'] = array();
	}
	
	function set($file)
	{
		if (!file_exists($file)) {
			//trigger_error('l10n file not found',E_USER_NOTICE);
			return false;
		}
		
		$f = file($file);
		$GLOBALS['__l10n_files'][] = $file;
		
		for ($i=0; $i<count($f); $i++) {
			if (substr($f[$i],0,1) == ';' && !empty($f[$i+1])) {
				$GLOBALS['__l10n'][trim(substr($f[$i],1))] = trim($f[$i+1]);
				$i++;
			}
		}
	}
}

?>
