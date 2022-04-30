/*
* This part handles Javascript functions of configurator view by One Page Checkout and it's extensions including tax handlers 
*
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
*/


function wasChanged(num)
{
  d = document.getElementById('waschanged'+num); 
  if (d!=null)
   {
     d.value = d.value.split('NO').join('YES'); 
	 
   }
}
function loadGoogle()
{
	var d = document.getElementById('load_google'); 
	if (d != null) {
		d.value = 1; 
		
	}
	
	if (typeof adminForm != 'undefined')
	{
		var e = document.getElementsByName('task'); 
		if (e.length > 0)
		for (var i=0; i<e.length; i++)
		{
			e[i].value = 'edit'; 
		}
	}
	
	
	if (typeof Joomla != 'undefined')
		if (typeof Joomla.submitbutton != 'undefined')
		{
			Joomla.submitbutton('save'); 
		}
		return false; 
	
}
function op_new_line(line, where)
{
  line_iter++; 
  table = document.getElementById(where); 
  if (table !=null)
   {
     e = document.createElement('tr');
	 e.setAttribute('id', 'rowid_'+line_iter);
     e.id = 'rowid_'+line_iter; 
	 e.innerHTML = line.split('{num}').join(line_iter); 
     
     table.appendChild(e); 
   }
   
   dz = document.getElementById('zip_start'+line_iter); 
   if (dz != null)
   dz.focus(); 
   
   dy = document.getElementById('last_iter');
   if (dy != null)
   dy.value = line_iter; 
   
   return false; 
}
