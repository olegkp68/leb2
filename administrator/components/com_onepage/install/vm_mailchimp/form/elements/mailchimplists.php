<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
 
jimport('joomla.form.formfield');
 
// The class name must always be the same as the filename (in camel case)
class JFormFieldMailchimplists extends JFormField {
 
        //The field class must know its own type through the variable $type.
        protected $type = 'mailchimplists';
		public $value; 
		/*
		public function getParams() {
			$db = JFactory::getDBO(); 
			$q = 'select `params` from #__extensions where `element` = \'vm_mailchimp\' and `type` = \'plugin\' and `folder` = \'system\' limit 1'; 
			$db->setQuery($q); 
			$r = $db->loadResult(); 
			if (!empty($r)) {
				$json = json_decode($r, false); 
				
			}
		}
		*/
        public function getInput() {
			$dispatcher = JDispatcher::getInstance();
			$lists = array(); 
			$dispatcher->trigger('plgMailChimpGetLists', array(&$lists));
			$html = ''; 
			if (empty($this->value)) $this->value = array(); 
			
			$this->value = (array)$this->value; 
			$this->name = str_replace('[]', '', $this->name); 
			foreach ($lists as $id => $list) {
				$html .= 'Checkbox label for '.$id.': '.$list."<br>\n"; 
				$html .= '<input placeholder="'.htmlentities($list).'" type="text" name="'.$this->name.'['.htmlentities($id).']" '; 
				
				if ((is_array($this->value)) && (isset($this->value[$id]))) {
					$html .= ' value="'.htmlentities($this->value[$id]).'" '; 
				}
			
				$html .= ' >'."<br/>\n"; 
				
			}
			
                // code that returns HTML that will be shown as the form field
				return $html;
        }
}