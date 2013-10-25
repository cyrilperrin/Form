<?php

namespace CyrilPerrin\Form;

/**
 * Form renderer with labels
 */
class FormRenderer_Labels extends FormRenderer_Abstract
{
    /**
     * @see FormRenderer_Abstract#renderField(Form,Field)
     */
    protected function renderField(Form $form,Field $field)
    {
        $string = '<div>';
        if ($field->getDescription() != null) {
            $string .= '<label>'.$field->getDescription().
                       '&nbsp;:&nbsp;</label> ';
        }
        $string .= $field->__toString().' '.$form->getError($field).
                  '</div>'."\n";
        return $string;
    }

    /**
     * @see FormRenderer_Abstract#getListStart()
     */
    protected function getListStart()
    {
        return '';
    }

    /**
     * @see FormRenderer_Abstract#getListEnd()
     */
    protected function getListEnd()
    {
        return '';
    }
}