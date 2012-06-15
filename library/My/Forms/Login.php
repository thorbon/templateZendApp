<?php

class My_Forms_Login extends Zend_Dojo_Form
{

    public function init() 
    {
        $this->setAction('login');
        $this->setAttrib('id', 'mainForm');
        $this->setAttrib('class', 'flora');
        $this->setMethod('POST');
         
        $this->setDecorators(array(
            'FormElements',
            array('TabContainer', array(
                'id' => 'tabContainer',
                'style' => 'width: 600px; height: 200px;',
                'dijitParams' => array(
                    'tabPosition' => 'top'
                ),
            )),
            'DijitForm',
        ));
        
        $login = new Zend_Dojo_Form_SubForm();
        $login->setAttribs(array(
            'name'   => 'textboxtab',
            'legend' => 'Login',
            'style'  => 'padding-left: 25px',
            'dijitParams' => array(
                'title' => 'Login',
            ),
        ));
                       
        $login->addElement(
                'ValidationTextBox',
                'username',
                array(
                    'class'      => 'loginTextBox',
                    'label'      => 'username',
                    'required'  => true,
                    'trim'       => true,
                    'propercase' => false,
                    //'style'      => 'width: 30%',
                    'regExp'     => '[\w]+',
                    'invalidMessage' => 'Please enter a valid username.',
                    'addErrorMessage' => 'Please enter a valid username.'
                )
            );
        
        $login->addElement(
                'PasswordTextBox',
                'password',
                array(
                    'class'      => 'loginTextBox',
                    'label'      => 'password',
                    'required'  => true,
                    'trim'       => true,
                    'propercase' => false,
                    //'style'      => 'width: 30%',
                    'regExp'     => '[\w]+',
                    'invalidMessage' => 'Please enter a valid password.'
                )
            );
        //$password->addError('Please enter a valid password.');
        //$subForm1->addElement($password);
         
        // Submit Button
        $elem = new Zend_Form_Element_Submit("login", array('value' => 'Submit', 'class' => 'loginButton'));
        $login->addElement($elem);
        
        $this->addSubForm($login, 'login');
        //$form->addSubForm($subForm2, 'subform2');
    }
}