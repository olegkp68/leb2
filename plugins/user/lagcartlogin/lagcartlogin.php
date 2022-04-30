<?php	defined( '_JEXEC' ) or die( 'Restricted access' );		jimport("joomla.plugin.plugin");	 	class plgUserLagcartlogin extends JPlugin{					public  $debugging;		public  $user_agent_info;		public  $db;		public  $inputCookie;		public  $lifecookcart = 10;		public  $uMessage;		public  $sessionId;				public function __construct(& $subject, $config){			parent::__construct($subject, $config);				$this->debugging       = $this->params->get('lagcartlogin_plugin_debugging',0); 			$this->user_agent_info = $_SERVER["HTTP_USER_AGENT"];			$this->db              = JFactory::getDBO();			$this->session         = JFactory::getSession();			$this->sessionId       = $this->session->getId();			$this->inputCookie     = JFactory::getApplication()->input->cookie;			// Get plugin              $plugin = JPluginHelper::getPlugin('vmpayment', 'lagcartusave');            // Check if plugin is enabled			            if ($plugin){                // Get plugin params                $pluginParams = new JRegistry($plugin->params);                $this->lifecookcart = trim (preg_replace('/\s/', '', $pluginParams->get('lagcartusave_plugin_lifecookcart',10)));            }					}				public function onUserLogin($user, $options = array()){		 		    if (JFactory::getApplication()->isAdmin()) {				return NULL;			}						if (!class_exists( 'VmConfig' )) {			    if(file_exists(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'config.php')){			        require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'config.php');						VmConfig::loadConfig();					VmConfig::loadJLang('com_virtuemart', true);				}else{				    return NULL;				}			}						if(!empty($this->debugging)){						    JFactory::getApplication()->enqueueMessage('<b>onUserLogin Сработал триггер</b>');									}						$user = JFactory::getUser();			//получаем юзера 		    $userid = $user->get('id');			//если пользователя нет то прекращаем выполнение		    if(!$userid) return;			//получаем из сессии корзину			$session = JFactory::getSession($options);			$cartSession = $session->get('vmcart', 0, 'vm');			//получаем  COOKIE cookcartusave из браузера			$inputCookie  = JFactory::getApplication()->input->cookie;			$cookcartusave = $inputCookie->get('cookcartusave', $defaultValue = null);			//проверяем пустая корзина или нет						if(empty(json_decode($cartSession)->cartProductsData)){			    //корзина пустая выпонляем функцию поиска в БД записи о корзине для данного пользователя и ее внесение в сессию если есть такая запись.				 				//проверяем есть ли запись в БД по $userid				if(!empty($this->debugging)){						        JFactory::getApplication()->enqueueMessage('<b>onUserLogin 1 корзина пустая проверяем есть ли запись в БД SELECT * FROM #__lagcartusave WHERE  userid = '.$userid.'</b>');									    }						    	$q = "SELECT * FROM #__lagcartusave WHERE  userid = ".$userid;						        $this->db->setQuery($q);		        $result = $this->db->loadObject();								if($result){				    //объект в БД есть устанавливаем его в сессию магазина				    if(!empty($this->debugging)){						            JFactory::getApplication()->enqueueMessage('<b>onUserLogin 2 объект в БД есть устанавливаем его в сессию магазина $this->setCart($result)</b>');			        }				    					$this->setCart($result);										if(empty($cookcartusave)){						    //если куки не установлен для загруженной корзины, то устанавливаем его 						if(!empty($this->debugging)){						                JFactory::getApplication()->enqueueMessage('<b>onUserLogin 3 куки не установлен для загруженной корзины устанавливаем их $this->writeCookie($result->session_id,$result->lifecookcart)<br>onUserLogin 3 записываем в куки $cookcartusave значение из $result->session_id = '.$result->session_id.'<br>onUserLogin 3 обновляем данные в полях БД чтобы переписать последние параметры входа USER его ip, данные браузера</b>');			            }						//записываем в куки $cookcartusave значение из $result->session_id = '.$result->session_id.';						$this->writeCookie($result->session_id,$result->lifecookcart);					}						//обновляем данные в полях БД чтобы переписать последние параметры входа USER его ip, данные браузера 					$q = "update #__lagcartusave set 						   					userip                = " . $this->db->Quote($this->getIp()) . ",							        	      	user_agent_info       = " . $this->db->Quote($this->user_agent_info) . ",					reserve_column        = DATE_SUB(NOW(), INTERVAL ".$this->db->Quote($result->lifecookcart)." DAY)        		    	WHERE userid          = " . $userid;        			$this->db->setQuery($q);                         	           	$this->db->query();										}else{				    if(!empty($this->debugging)){						            JFactory::getApplication()->enqueueMessage('<b>onUserLogin 4 объект в БД НЕТ для SELECT * FROM #__lagcartusave WHERE  userid = '.$userid.'</b>');			        }				}							}else{			    //корзина не пустая				if(!empty($this->debugging)){				                JFactory::getApplication()->enqueueMessage('<b>onUserLogin 7 корзина не пустая<br>onUserLogin 5 ищем в БД запись для текущего юзера с другим куки сделанными ранее (с другого браузера) userid = '. $userid . ' AND session_id != ' . $this->db->Quote($cookcartusave).'</b>');							            }				//ищем в БД запись для текущего юзера с другим куки сделанными ранее (с другого браузера) 				$q = "SELECT * FROM #__lagcartusave WHERE  userid = " . $userid . " AND session_id != " . $this->db->Quote($cookcartusave);		            $this->db->setQuery($q);    	        $resultone = $this->db->loadObject();								if($resultone){					//запись есть					if(!empty($this->debugging)){				                    JFactory::getApplication()->enqueueMessage('<b>onUserLogin 8 запись есть<br>onUserLogin 6 обновляем старую запись, отвязываем ее от данного юзера удаляя его userid ставим = 0<br>onUserLogin 6  привязываем юзера к новой текущей записи 	$cookcartusave выставляя ему userid = текущему '.$userid.'<br>onUserLogin 6 записываем новые данные корзины в virtuemart $cart->storeCart();  где он хранит сохраненные корзины пользователей</b>');	                }					//обновляем старую запись, отвязываем ее от данного юзера удаляя его userid ставим = 0					$q = "update #__lagcartusave set       			        						userid                = 0       			    	WHERE userid          = " . $userid;        				$this->db->setQuery($q);                         	            	$this->db->query();					//привязываем юзера к новой текущей записи 	$cookcartusave выставляя ему userid = текущему $userid					$q = "update #__lagcartusave set       			        created               = NOW(),						vmcart                = " . $this->db->Quote($cartSession) . ",   						userip                = " . $this->db->Quote($this->getIp()) . ",						userid                = " . $userid . ",        		      	user_agent_info       = " . $this->db->Quote($this->user_agent_info) . ",						reserve_column        = DATE_SUB(NOW(), INTERVAL ".$this->db->Quote($resultone->lifecookcart)." DAY)        			    	WHERE session_id      = " . $this->db->Quote($cookcartusave);        				$this->db->setQuery($q);                         	            	$this->db->query();														//записываем новые данные корзины в virtuemart $cart->storeCart();  где он хранит сохраненные корзины пользователей	   					$this->storeCartVm();				}else{				    //ищем в БД запись куки сделанными ранее (с этого браузера с текущим КУКИ и текущим пользователем)					if(!empty($this->debugging)){				                    JFactory::getApplication()->enqueueMessage('<b>onUserLogin 7 записи НЕТ<br>onUserLogin 7 ищем в БД запись для текущего юзера c текущим куки сделанными ранее WHERE  userid = '.$userid.'  AND session_id ='.$this->db->Quote($cookcartusave).'</b>');							                }			    	$q = "SELECT * FROM #__lagcartusave WHERE  userid =  " . $userid . " AND session_id = " . $this->db->Quote($cookcartusave);		                $this->db->setQuery($q);    	            $result = $this->db->loadObject();					if($result){    					//запись есть						if(!empty($this->debugging)){				                        JFactory::getApplication()->enqueueMessage('<b>onUserLogin 8 запись ЕСТЬ, обновляем ее поля и поле userid на текущего пользователя '.$userid.'</b>');	                    }						if($result->userid == 0){    			    		//обновляем ее данные поля userid = 0 на текущего пользователя    						$q = "update #__lagcartusave set          	    		        created               = NOW(),		        				vmcart                = " . $this->db->Quote($cartSession) . ",   		        				userip                = " . $this->db->Quote($this->getIp()) . ",				    			userid                = " . $userid .",            		         	user_agent_info       = " . $this->db->Quote($this->user_agent_info) . ",    							reserve_column        = DATE_SUB(NOW(), INTERVAL ".$this->db->Quote($result->lifecookcart)." DAY)          	    		    	WHERE session_id      = " . $this->db->Quote($result->session_id);            				$this->db->setQuery($q);                         	                	$this->db->query();							}						if(empty($cookcartusave)){						    if(!empty($this->debugging)){				                            JFactory::getApplication()->enqueueMessage('<b>onUserLogin 9 если куки пустой то записываем его из текущей сессии $this->writeCookie('.$result->session_id.' , время жизни куки '.$result->lifecookcart.')</b>');							                        }						    //если куки не установлены ставим их из найденной 						    $this->writeCookie($result->session_id,$result->lifecookcart);						}					}else{					    //ищем запись с текущим куки						if(!empty($this->debugging)){				                        JFactory::getApplication()->enqueueMessage('<b>onUserLogin 10 записи НЕТ<br>onUserLogin 14 ищем запись с текущим КУКИ WHERE  session_id ='.$this->db->Quote($cookcartusave).'</b>');	                    }    			    	$q = "SELECT * FROM #__lagcartusave WHERE session_id = " . $this->db->Quote($cookcartusave);	    	                $this->db->setQuery($q);        	            $result = $this->db->loadObject();						if($result){    				    	//запись есть					        if(!empty($this->debugging)){				                            JFactory::getApplication()->enqueueMessage('<b>onUserLogin 11 запись ЕСТЬ, обновляем ее, привязываем к текущему пользователю userid = '.$userid.'</b>');	                        }							//обновляем ее привязываем к текущему пользователю userid    						$q = "update #__lagcartusave set          	    		        created               = NOW(),		        				vmcart                = " . $this->db->Quote($cartSession) . ",   		        				userip                = " . $this->db->Quote($this->getIp()) . ",				    			userid                = " . $userid .",            		         	user_agent_info       = " . $this->db->Quote($this->user_agent_info) . ",    							reserve_column        = DATE_SUB(NOW(), INTERVAL ".$this->db->Quote($result->lifecookcart)." DAY)          	    		    	WHERE session_id      = " . $this->db->Quote($result->session_id);            				$this->db->setQuery($q);                         	                	$this->db->query();	   					    }else{					        					    //В БД нет записи для текущего user создаем запись	    					//создадим новое значение $cookcartusave из Id сессии		    				$cookcartusave = $this->sessionId;			    	    	if(!empty($this->debugging)){			    	                        JFactory::getApplication()->enqueueMessage('<b>onUserLogin 12 записи НЕТ<br>onUserLogin 12 создадим новое значение $cookcartusave из Id сессии '. $cookcartusave.'<br>onUserLogin 12 создаем запись в БД с текущим userid = '.$userid.'</b>');							                        }    					    //В БД нет записи с текущим userid значит создаем запись в БД	    				    $this->insertDbCart($cookcartusave,$userid);		    			    //создаем куки в браузере			    		    $this->writeCookie ($cookcartusave,$this->lifecookcart);	    					}						    			    //записываем новые данные корзины в virtuemart $cart->storeCart();  где он хранит сохраненные корзины пользователей, чтобы ВМ не переписал набранную корзину до авторизации на сохраненную у него в БД. _virtuemart_carts    	    			$this->storeCartVm();    				}    			}			    		}		}				public function onUserLogout($user, $options = array()){		    if (JFactory::getApplication()->isAdmin()) {				return NULL;			}						if (!class_exists( 'VmConfig' )) {			    if(file_exists(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'config.php')){			        require_once(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'config.php');						VmConfig::loadConfig();					VmConfig::loadJLang('com_virtuemart', true);				}else{				    return NULL;				}			}						if(!empty($this->debugging)){				            JFactory::getApplication()->enqueueMessage('<b>onUserLogout 15</b>');						            }		    //получаем из сессии корзину   			$session = JFactory::getSession($options);   			$cartSession = $session->get('vmcart', 0, 'vm');   			//получаем  COOKIE cookcartusave из браузера   			$inputCookie  = JFactory::getApplication()->input->cookie;   			$cookcartusave = $inputCookie->get('cookcartusave', $defaultValue = null);   			//проверяем пустая корзина или нет   			if(empty(json_decode($cartSession)->cartProductsData)){   			    //корзина пустая, если в  куки есть cookcartusave загружаем из нее корзину				//получаем запись из БД по КУКИ 			     			    $q = "SELECT * FROM #__lagcartusave WHERE  session_id = " . $this->db->Quote($cookcartusave);			        $this->db->setQuery($q);		        $result = $this->db->loadObject();				if($result){				    //запись в БД есть									        //объект в БД есть устанавливаем его в сессию магазина			       $this->setCart($result);											}			}			     	}				//запсиь в сессию магазина из БД корзины		public function setCart($result){		   		    //проверяем есть ли в массиве данных о полученной корзине товары 		    if(!empty(((object)json_decode($result->vmcart,true))->cartProductsData)){				    				//товары есть записываем данные корзины в сессию 		        $session = JFactory::getSession();				                $session->set('vmcart', $result->vmcart, 'vm');									$cart = VirtueMartCart::getCart(true);//получаем корзину							    }else{			    //в данных записи массив товаров отсутствует , удаляем данную запись из БД				$q = "DELETE FROM #__lagcartusave WHERE userid = ".$result->userid;				$this->db->setQuery($q);                $this->db->query();						    			}					}				//запись корзины в БД virtuemart где он хранит данные незаконченных заказов вошедших пользователей		public function storeCartVm( ){			    	    	$cart = VirtueMartCart::getCart();			$cart->storeCart();		}				public function getIp(){            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {                 $ip = $_SERVER['HTTP_CLIENT_IP'];            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];            } elseif (!empty($_SERVER['REMOTE_ADDR'])) {                $ip = $_SERVER['REMOTE_ADDR'];            } else {                $ip = null;            }            return $ip;        }				function writeCookie ($cookcartusave,$lifecookcart){		    //записываем в куки сессию корзины    		$expire = time() + 60 * 60 * 24 * $lifecookcart;	    		$this->inputCookie->set('cookcartusave', $cookcartusave, $expire, '/');		}				function insertDbCart($cookcartusave,$userid){		    		    $userip = $this->getIp();								//получаем корзину из сессии				$session = JFactory::getSession();			$cartSession = $session->get("vmcart", 0, "vm");		    $this->db->setQuery("INSERT INTO #__lagcartusave (created,vmcart,userip,session_id,userid,lifecookcart,user_agent_info,reserve_column) values(			NOW(),			" . $this->db->Quote($cartSession) . ", 			" . $this->db->Quote($userip) . ", 			" . $this->db->Quote($cookcartusave) . ",			" . $userid . ", 			" . $this->lifecookcart . ", 			" . $this->db->Quote($this->user_agent_info) .",			DATE_SUB(NOW(), INTERVAL ".$this->db->Quote($this->lifecookcart)." DAY)			)");			   			$this->db->query();				}	}?>