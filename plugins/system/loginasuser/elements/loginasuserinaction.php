<?php
/* ======================================================
# Login as User for Joomla! - v3.3.2
# -------------------------------------------------------
# For Joomla! CMS
# Author: Web357 (Yiannis Christodoulou)
# Copyright (©) 2009-2019 Web357. All rights reserved.
# License: GNU/GPLv3, http://www.gnu.org/licenses/gpl-3.0.html
# Website: https:/www.web357.com/
# Demo: https://demo.web357.com/?item=loginasuser
# Support: support@web357.com
# Last modified: 21 Mar 2019, 01:46:38
========================================================= */

defined('_JEXEC') or die;

jimport('joomla.form.formfield');

class JFormFieldloginasuserinaction extends JFormField {
	
	protected $type = 'loginasuserinaction';

	protected function getInput()
	{
		return ' ';
	}
	
	protected function getLabel()
	{
		$more_description = '<p><a href="index.php?option=com_loginasuser&plg=loginasuser" class="btn btn-default btn-warning"><strong>To Login as any User, visit Component\'s page and click on <em>Login as Username</em> &raquo;</strong></a></p>';
		return $more_description;		
	}
}