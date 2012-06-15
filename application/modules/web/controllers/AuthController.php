<?php

class Web_AuthController extends Zend_Controller_Action
{
    private $logger;
    
    public function init() 
    {
        $this->view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
        $this->view->addHelperPath("Zend/Dojo/View/Helper", "Zend_Dojo_View_Helper");
        $this->session = Zend_Registry::get('Zend_Session');
        $this->_helper->layout->setLayout('login');
        
        $this->_db = Zend_Registry::get('Zend_Web_Db');
        $this->logger = Zend_Registry::get('Zend_Log');
    }
    
    public function loginAction()
    {
        if ($this->session->expired) {
            echo "<font color='red'><b>Your session exipred. Please attempt login again.</b></font><br>";
        }
        
        $form = new My_Forms_Login();
        $this->view->form = $form;
        
        $user = new Model_Users();
        
        if ($this->_request->isPost())
        {
            if ($this->view->form->isValid($_POST))
            {
                echo "<br>Valid form";
                $user->login($_POST['login']['username'], $_POST['login']['password']);
                
                if ($user->isLoggedIn()) {
                    echo "<br>Login was successfull";
                    $this->_helper->redirector('main', 'dashboard');
                } else {
                    echo "<br>Login failed!!";
                }
            }
            else
            {
                $this->logger->err("Form is invalid");
            }
        }
        else
        {
            $this->logger->err("Form not posted.");
        }
        
        if ($user->isLoggedIn()) {
            $firstname = $user->getLoggedInUserField('firstname');
            $lastname = $user->getLoggedInUserField('lastname');
            
            echo "<br>User $firstname $lastname is logged in.";
        } else {
            //echo "<br>There are no users logged in.";
        }
    }
    
    public function logoutAction()
    {
        Model_Users::logout();
        
        $this->_helper->redirector('login', 'auth');
    }

    public function forgotpasswordAction()
    {
		$form = new My_Forms_ForgotPassword();
		$this->view->forgotpassword = $form;
        
        $userMessage = Zend_Registry::get('User_Config')->mail->smtp_host;
        //echo $userMessage;

		if ($this->_request->isPost())
		{
			if ($this->view->forgotpassword->isValid($_POST))
			{
				try {
					$config = array(
						'auth' => 'login',
						'username' => 'user@domain.com',
						'password' => 'password',
						//'ssl' => 'tls',
						'port' => 587
					);

					$mailTransport = new Zend_Mail_Transport_Smtp('mail.domain.com', $config);
					Zend_Mail::setDefaultTransport($mailTransport);

					//Prepare email
					$mail = new Zend_Mail();
					$mail->addTo('user@domain.com');
					$mail->setSubject('Foo ABr');
					$mail->setBodyHtml('Hi There <a href="#">Click here</a>');
					$mail->setFrom('support@domain.com', 'User Name');

					//Send it!
					$sent = true;  
					try {
						$mail->send();
					} catch (Exception $e){
						$sent = false;
					}

					//Do stuff (display error message, log it, redirect user, etc)
					if($sent){
						echo 'Mail was sent successfully';
					} else {
						echo 'Mail failed to send';
					}

				} catch (Zend_Exception $e){
					//Do something with exception
				}

			}
			else
			{
				echo "Form invalid";
			}
		}
		else
		{
			echo "<br>Form not posted.";
		}
	}
}
