<?php

class My_Forms_UserMenu extends ZendX_JQuery_Form
{

    public function init() 
    {
        $this->setAction('addrunner');
        $this->setAttrib('id', 'mainForm');
        $this->setAttrib('class', 'flora');
         
        $this->setDecorators(array(
            'FormElements',
            array('TabContainer', array(
                'id'          => 'tabContainer',
                'style'       => 'width: 650px;',
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
                                                'title' => 'Add Runner')))
        ));
                       
        $firstname = $subForm1->createElement('text', 'firstname', array(
            'label'       => 'Enter Runners Firstname:',
            'filters'     => array('StringTrim'),
            'class'       => 'textbox',
            'required'    => true,
        ));
        $firstname->addErrorMessage('Please enter a valid first name.');
        $subForm1->addElement($firstname);
        
        $lastname = $subForm1->createElement('text', 'lastname', array(
            'label'       => 'Enter Runners Lastname:',
            'filters'     => array('StringTrim'),
            'class'       => 'textbox',
            'required'    => true,
        ));
        $lastname->addErrorMessage('Please enter a valid last name.');
        $subForm1->addElement($lastname);
        
        $get = new Model_Get();
        $pdlist = $get->allPollingDivisions();
        $pdlist['data'][0] = '- Select a PD -';
        natsort($pdlist['data']);
         
        // Submit Button
        $elem1 = new Zend_Form_Element_Submit("Add Runner", array('value' => 'Submit'));
        $elem2 = new Zend_Form_Element_Reset("Clear Form", array('value' => 'Reset', "name"=>"Clear Form"));
        $subForm1->addElement($elem1);
        $subForm1->addElement($elem2);
        
        $this->addSubForm($subForm1, 'subform1');
        //$form->addSubForm($subForm2, 'subform2');
    }
}