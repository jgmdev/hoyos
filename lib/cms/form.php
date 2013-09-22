<?php

/**
 * @author Jefferson González
 * @license MIT
 */

namespace Cms;

class Form extends Signal
{
    /**
     *
     * @var string
     */
    public $id;
    
    /**
     * @var string
     */
    public $name;
    
    /**
     * @var string
     */
    public $action;
    
    /**
     * @var string
     */
    public $method;
    
    /**
     * @var \Cms\Form\Field[]
     */
    public $fields;
    
    /**
     * @var \Cms\Form\FieldsGroup[]
     */
    public $groups;
    
    /**
     * @var string
     */
    public $encoding;
    
    /**
     * Default constructor.
     * @param string $name
     * @param string $action
     * @param string $method
     * @param string $encoding
     * @return \Cms\Form
     */
    public function __construct($name, $action, $method="POST", $encoding=null)
    {
        $this->name = $name;
        $this->id = $name;
        $this->action = $action;
        $this->method = $method;
        $this->encoding = $method;
        
        return $this;
    }
    
    /**
     * Add a new field to the form.
     * @param \Cms\Form\Field $field
     * @return \Cms\Form
     */
    public function AddField(\Cms\Form\Field $field)
    {
        if($field->type == Enumerations\FormFieldType::FILE)
            $this->encoding = 'multipart/form-data';
        
        $this->fields[] = $field;
        
        return $this;
    }
    
    /**
     * Add a group of fields to the form.
     * @param \Cms\Form\FieldsGroup $group
     */
    public function AddGroup(\Cms\Form\FieldsGroup $group)
    {
        $this->groups[] = $group;
    }
    
    /**
     * Generate the form html.
     * @return string
     */
    public function GetHtml()
    {
        $html = '';
        
        if(count($this->fields) > 0)
        {
            $html .= '<form id="'.$this->id.'" ';
            $html .= 'name="'.$this->name.'" ';
            $html .= 'action="'.Uri::GetUrl($this->action).'" ';
            $html .= 'method="'.$this->method.'" ';
            
            if($this->encoding)
                $html .= 'encoding="'.$this->encoding.'" ';
            
            $html .= '>' . "\n";
            
            foreach($this->fields as $field)
            {
                $html .= $field->GetLabelHtml();
                $html .= $field->GetHtml();
            }
            
            $html .= '</form>' . "\n";
        }
        
        return $html;
    }
    
    /**
     * Print the form html generated by GetHTML().
     */
    public function Render()
    {
        print $this->GetHtml();
    }
}
?>
