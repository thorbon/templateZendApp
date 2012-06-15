<?php

/**
* My_Forms_AddWorker extends Zend_Dojo_Form
*
* My_Forms_AddWorker class is used to display the form elements
* required to add a new worker or edit an existing worker
*
* Example usage:
* $form = new My_Forms_AddWorker();
* $form = new My_Forms_AddWorker($values);
*
* @package  Zend_Dojo_Form
* @author   Ricardo Hamilton <ricardo.p.hamilton@gmail.com>
* @version  $Revision: 1.0 $
* @access   public
* @see      http://framework.zend.com/manual/en/zend.dojo.form.html
*/
class My_Forms_AddWorker extends Zend_Dojo_Form
{
    protected $_values;
    protected $_action;
    protected $_title;
    
    public function __construct($values = null) 
    {
        parent::__construct($values);
        
        $this->_values = null;
        $this->_action = 'addworker';
        $this->_title = 'Add New Worker';
        
        if(!empty($values)) {
            $this->_values = $values;
            $this->_title = 'Update Worker Information';
            $this->_action = 'editworker';
        }
        
        $this->setAction($this->_action);
        $this->setAttrib('id', 'mainForm');
        $this->setAttrib('class', 'flora');
        $this->setMethod('POST');
         
        $this->setDecorators(array(
            'FormElements',
            array('TabContainer', array(
                'id' => 'tabContainer',
                'style' => 'width: 600px; height: 500px;',
                'dijitParams' => array(
                    'tabPosition' => 'top'
                ),
            )),
            'DijitForm',
        ));
        
        $subForm1 = new Zend_Dojo_Form_SubForm();
        $subForm1->setAttribs(array(
            'name'   => 'textboxtab',
            'legend' => $this->_title,
            'dijitParams' => array(
                'title' => $this->_title,
            ),
        ));
        
        /**
        * If values dont exist, this means the form should
        * be displayed in edit mode and as such, the key
        * used for editing is to be displayed
        */
        if (!empty($this->_values)) {
            $subForm1->addElement(
                                'ValidationTextBox',
                                'id',
                                array(
                                    'value' =>  isset($this->_values['IOAgentID']) ? $this->_values['IOAgentID'] : null,
                                    'label' => 'Agent ID:',
                                    'class' => 'textBox',
                                    'required'  => true,
                                    'readonly'  => true,
                                    'trim'       => true,
                                    'propercase' => false,
                                )
                            );
        }
        
        $subForm1->addElement(
                            'ValidationTextBox',
                            'firstname',
                            array(
                                'value' =>  isset($this->_values['IOAgentFirstName']) ? $this->_values['IOAgentFirstName'] : null,
                                'label' => 'Enter Firstname:',
                                'class' => 'textBox',
                                'required'  => true,
                                'trim'       => true,
                                'propercase' => true,
                                'regExp' => '[a-zA-Z|-]+',
                                'invalidMessage' => 'Characters entered are invalid.',
                            )
                        );
                       
        $subForm1->addElement(
                            'ValidationTextBox',
                            'lastname',
                            array(
                                'value' =>  isset($this->_values['IOAgentLastName']) ? $this->_values['IOAgentLastName'] : null,
                                'label' => 'Enter Lastname:',
                                'class' => 'textBox',
                                'required'  => true,
                                'trim'       => true,
                                'propercase' => true,
                                'regExp' => '[a-zA-Z|-]+',
                                'invalidMessage' => 'Characters entered are invalid.',
                            )
                        );
        
        $subForm1->addElement(
                        'ValidationTextBox',
                        'homephone',
                        array(
                            'value' =>  isset($this->_values['IOAgentHomePhone']) ? $this->_values['IOAgentHomePhone'] : null,
                            'label' => 'Enter Home Phone Number (e.g. 8765551111):',
                            'class' => 'textBox',
                            'required'  => false,
                            'trim'       => true,
                            'regExp' => '^876[0-9][0-9][0-9][0-9][0-9][0-9][0-9]',
                            'invalidMessage' => 'Please enter a valid phone number (e.g. 8765551111)',
                        )
                    );
                    
        $subForm1->addElement(
                        'ValidationTextBox',
                        'mobilephone',
                        array(
                            'value' =>  isset($this->_values['IOAgentMobilePhone']) ? $this->_values['IOAgentMobilePhone'] : null,
                            'label' => 'Enter Mobile Phone Number (e.g. 8765551111):',
                            'class' => 'textBox',
                            'required'  => true,
                            'trim'       => true,
                            'regExp' => '^876[0-9][0-9][0-9][0-9][0-9][0-9][0-9]',
                            'invalidMessage' => 'Please enter a valid phone number (e.g. 8765551111)',
                        )
                    );
        
        $pdlist['data'][''] = '';
        natsort($pdlist['data']);
        
        $subForm1->addElement(
                        'FilteringSelect',
                        'pollingDivisionId',
                        array(
                            'label' => 'Select a Polling Division from the list:',
                            'class' => 'textBox',
                            'value' =>  isset($this->_values['PollingDivisionID']) ? $this->_values['PollingDivisionID'] : '',
                            'autocomplete' => true,
                            'required'    => true,
                            'multiOptions' => $pdlist['data'],
                            //'regExp' => '^876[0-9][0-9][0-9][0-9][0-9][0-9][0-9]',
                            'invalidMessage' => 'Please select a valid Polling Division',
                        )
                    );
        
        $agentType = array(''=>'', '1'=>'indoor', '2'=>'outdoor');
        $subForm1->addElement(
                        'FilteringSelect',
                        'type',
                        array(
                            'label' => 'Select an Agent Type from the list:',
                            'class' => 'textBox',
                            'value' =>  isset($this->_values['type']) ? $this->_values['type'] : '',
                            'autocomplete' => true,
                            'required'    => true,
                            'multiOptions' => $agentType,
                            //'regExp' => '^876[0-9][0-9][0-9][0-9][0-9][0-9][0-9]',
                            'invalidMessage' => 'Please select a valid Agent Type',
                        )
                    );
        
        $subForm1->addElement(
                        'Textarea',
                        'address',
                        array(
                            'value' =>  isset($this->_values['address']) ? $this->_values['address'] : null,
                            'label'    => 'Enter Address:',
                            'class' => 'formTextArea',
                            'required' => true,
                            'trim'     => true,
                            'style'    => 'width: 50%; height: 300px !important',
                        )
                    );
        
        
        $submitText = empty($this->_values) ? 'Add Worker' : 'Save';
        // Submit Button
        $elem1 = new Zend_Form_Element_Submit($submitText, array('value' => 'Submit'));
        $elem2 = new Zend_Form_Element_Reset("Clear Form", array('value' => 'Reset', "name"=>"Clear Form"));
        $subForm1->addElement($elem1);
        
        if (empty($this->_values)) {
            $subForm1->addElement($elem2);
        }
        
        $this->addSubForm($subForm1, 'worker');
        //$form->addSubForm($subForm2, 'subform2');
    }
}