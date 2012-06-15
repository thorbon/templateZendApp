<?php

class My_Forms_ForgotPassword extends ZendX_JQuery_Form
{

    public function init() 
    {
        $this->setAction('forgotpassword');
        $this->setAttrib('id', 'mainForm');
        $this->setAttrib('class', 'flora');
         
        $this->setDecorators(array(
            'FormElements',
            array('TabContainer', array(
                'id'          => 'tabContainer',
                'style'       => 'width: 350px;',
            )),
            'Form',
        ));
        
        $subForm1 = new ZendX_JQuery_Form();
        $subForm1->setDecorators(array(
            'FormElements',
            array('HtmlTag',
                  array('tag' => 'dl')),
            array('TabPane',
                  array('jQueryParams' => array('containerId' => 'mainForm',
                                                'title' => 'Reset Your Password')))
        ));
                       
        $email = $subForm1->createElement('text', 'email', array(
            'label'       => 'email',
            'filters'     => array('StringTrim', 'StripTags'),
            'class'       => 'textbox',
            'required'    => true,
        ));
        $email->addErrorMessage('Please enter a valid email address');
		$email->addValidator('EmailAddress', TRUE);
        $subForm1->addElement($email);

         
        // Submit Button
        $elem = new Zend_Form_Element_Submit("submit", array('value' => 'Submit'));
        $subForm1->addElement($elem);
        
        $this->addSubForm($subForm1, 'subform1');

    }
}

