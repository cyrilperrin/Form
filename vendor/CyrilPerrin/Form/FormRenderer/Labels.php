<?php

namespace CyrilPerrin\Form;

/**
 * Form renderer with labels
 */
class FormRenderer_Labels extends FormRenderer_Abstract
{
    /**
     * @see FormRenderer_Abstract#renderElement(Form,Field)
     */
    protected function renderElement(Form $form,Field $element)
    {
        $string = '<div>';
        if ($element->getDescription() != null) {
            $string .= '<label>'.$element->getDescription().
                       '&nbsp;:&nbsp;</label> ';
        }
        $string .= $element->__toString().' '.$form->getError($element).
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