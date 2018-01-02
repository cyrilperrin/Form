<?php

namespace CyrilPerrin\Form\FormRenderer;

use CyrilPerrin\Form\Field;
use CyrilPerrin\Form\Form;
use CyrilPerrin\Form\FormRenderer;

/**
 * Form renderer with labels
 */
class Labels extends FormRenderer
{
    /**
     * @see FormRenderer#renderField(Form,Field)
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
     * @see FormRenderer#getListStart()
     */
    protected function getListStart()
    {
        return '';
    }

    /**
     * @see FormRenderer#getListEnd()
     */
    protected function getListEnd()
    {
        return '';
    }
}